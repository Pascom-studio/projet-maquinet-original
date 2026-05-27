<?php
// app/Http/Controllers/Admin/ThemeController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserThemeSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThemeController extends Controller
{
    public function edit()
    {
        // Vérification admin
        $user = auth()->user();
        if (!$user || (!$user->isAdmin() && !$user->isSuperAdmin())) {
            abort(403, 'Accès réservé aux administrateurs');
        }

        $colors = UserThemeSetting::getColorsForUser(auth()->id());
        return view('admin.theme.edit', compact('colors'));
    }

    public function update(Request $request)
    {
        // Vérification admin
        $user = auth()->user();
        if (!$user || (!$user->isAdmin() && !$user->isSuperAdmin())) {
            abort(403, 'Accès réservé aux administrateurs');
        }

        $validated = $request->validate([
            'navbar_bg' => 'required|string|min:6|max:7',
            'footer_bg' => 'required|string|min:6|max:7',
            'primary_text' => 'required|string|min:6|max:7',
            'hover_color' => 'required|string|min:6|max:7',
        ]);

        // Nettoyer les valeurs (supprimer le # si présent)
        $cleanedData = [
            'navbar_bg' => str_replace('#', '', $validated['navbar_bg']),
            'footer_bg' => str_replace('#', '', $validated['footer_bg']),
            'primary_text' => str_replace('#', '', $validated['primary_text']),
            'hover_color' => str_replace('#', '', $validated['hover_color']),
        ];

        // Vérification que les données sont valides
        foreach ($cleanedData as $key => $value) {
            if (strlen($value) !== 6 || !ctype_xdigit($value)) {
                return back()->with('error', "Couleur $key invalide: $value");
            }
        }

        DB::beginTransaction();

        try {
            // Récupérer tous les utilisateurs de l'admin (y compris l'admin lui-même)
            $usersToUpdate = User::where(function($query) use ($user) {
                if ($user->isSuperAdmin()) {
                    // SuperAdmin peut choisir d'appliquer à tous ou seulement à certains
                    return $query->whereIn('id', $request->user_ids ?? [User::pluck('id')->toArray()]);
                } else {
                    // Admin régulier : appliquer à lui-même + tous ses utilisateurs
                    return $query->where('id', $user->id)
                                ->orWhere('admin_id', $user->id);
                }
            })->get();

            // Mettre à jour le thème pour chaque utilisateur
            foreach ($usersToUpdate as $userToUpdate) {
                UserThemeSetting::updateOrCreate(
                    ['user_id' => $userToUpdate->id],
                    $cleanedData
                );
            }

            DB::commit();

            $message = $user->isSuperAdmin() 
                ? 'Thème mis à jour pour les utilisateurs sélectionnés!'
                : 'Thème mis à jour pour vous et tous vos utilisateurs!';

            return redirect()->route('admin.theme.edit')
                ->with('success', $message)
                ->with('theme_updated', true);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la sauvegarde: ' . $e->getMessage());
        }
    }
    
    public function reset(Request $request)
    {
        // Vérification admin
        $user = auth()->user();
        if (!$user || (!$user->isAdmin() && !$user->isSuperAdmin())) {
            abort(403, 'Accès réservé aux administrateurs');
        }

        DB::beginTransaction();

        try {
            // Récupérer tous les utilisateurs de l'admin (y compris l'admin lui-même)
            $usersToReset = User::where(function($query) use ($user) {
                if ($user->isSuperAdmin()) {
                    // SuperAdmin peut choisir de réinitialiser pour tous ou seulement certains
                    return $query->whereIn('id', $request->user_ids ?? [User::pluck('id')->toArray()]);
                } else {
                    // Admin régulier : réinitialiser pour lui-même + tous ses utilisateurs
                    return $query->where('id', $user->id)
                                ->orWhere('admin_id', $user->id);
                }
            })->get();

            // Supprimer les thèmes personnalisés pour chaque utilisateur
            UserThemeSetting::whereIn('user_id', $usersToReset->pluck('id'))->delete();

            DB::commit();

            $message = $user->isSuperAdmin() 
                ? 'Thème réinitialisé pour les utilisateurs sélectionnés!'
                : 'Thème réinitialisé pour vous et tous vos utilisateurs!';

            return redirect()->route('admin.theme.edit')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la réinitialisation: ' . $e->getMessage());
        }
    }

    /**
     * Méthode pour le SuperAdmin pour sélectionner les utilisateurs
     */
    public function editSuperAdmin()
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Accès réservé au SuperAdmin');
        }

        $colors = UserThemeSetting::getColorsForUser(auth()->id());
        $allUsers = User::all();
        $userGroups = User::with('admin')->get()->groupBy('admin_id');

        return view('admin.theme.super-admin-edit', compact('colors', 'allUsers', 'userGroups'));
    }
}