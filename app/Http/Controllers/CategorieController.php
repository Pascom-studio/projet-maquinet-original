<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategorieController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $categories = Categorie::visibleTo($user)
                              ->withCount('products')
                              ->orderBy('created_at', 'desc')
                              ->get();
        
        // CORRECTION : Utiliser 'categorie.index' au lieu de 'categories.index'
        return view('categorie.index', compact('categories'));
    }

    public function create()
    {
        // Vérifier que seul admin/gerant peut créer des catégories
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isGerant()) {
            abort(403, 'Seuls les administrateurs et gérants peuvent créer des catégories.');
        }

        // CORRECTION : Utiliser 'categorie.create' au lieu de 'categories.create'
        return view('categorie.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        // CORRECTION : Vérifier admin/gerant pour les catégories, pas les caissiers
        if (!$user->isAdmin() && !$user->isGerant()) {
            abort(403, 'Seuls les administrateurs et gérants peuvent créer des catégories.');
        }

        // Validation de base
        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        // CORRECTION : Validation d'unicité par scope admin
        if (!Categorie::isNameUniqueForUser($request->nom, $user)) {
            return redirect()->back()
                ->with('error', 'Une catégorie avec ce nom existe déjà dans votre organisation.')
                ->withInput();
        }

        try {
            // CORRECTION : Créer une catégorie avec user_id
            Categorie::create([
                'nom' => $request->nom,
                'description' => $request->description,
                'user_id' => $user->id,
            ]);

            return redirect()->route('categories.index')
                ->with('success', 'Catégorie créée avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la création de la catégorie: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Categorie $category)
    {
        if (!$category->canBeManagedBy(Auth::user())) {
            abort(403, 'Accès non autorisé à cette catégorie.');
        }

        // Charger les produits visibles par l'utilisateur
        $user = Auth::user();
        $products = $category->products()
                            ->visibleTo($user)
                            ->get();
        
        // CORRECTION : Utiliser 'categorie.show' au lieu de 'categories.show'
        return view('categorie.show', compact('category', 'products'));
    }

    public function edit(Categorie $category)
    {
        if (!$category->canBeManagedBy(Auth::user())) {
            abort(403, 'Accès non autorisé à cette catégorie.');
        }

        // CORRECTION : Utiliser 'categorie.edit' au lieu de 'categories.edit'
        return view('categorie.edit', compact('category'));
    }

    public function update(Request $request, Categorie $category)
    {
        $user = Auth::user();
        
        if (!$category->canBeManagedBy($user)) {
            abort(403, 'Accès non autorisé à cette catégorie.');
        }

        // Validation de base
        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        // CORRECTION : Validation d'unicité par scope admin (en ignorant la catégorie actuelle)
        if (!Categorie::isNameUniqueForUser($request->nom, $user, $category->id)) {
            return redirect()->back()
                ->with('error', 'Une catégorie avec ce nom existe déjà dans votre organisation.')
                ->withInput();
        }

        try {
            $category->update([
                'nom' => $request->nom,
                'description' => $request->description,
            ]);

            return redirect()->route('categories.index')
                ->with('success', 'Catégorie mise à jour avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour de la catégorie: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Categorie $category)
    {
        if (!$category->canBeManagedBy(Auth::user())) {
            abort(403, 'Accès non autorisé à cette catégorie.');
        }

        try {
            // Vérifier si la catégorie contient des produits
            if ($category->products()->exists()) {
                return redirect()->route('categories.index')
                    ->with('error', 'Impossible de supprimer cette catégorie car elle contient des produits.');
            }

            $category->delete();

            return redirect()->route('categories.index')
                ->with('success', 'Catégorie supprimée avec succès.');

        } catch (\Exception $e) {
            return redirect()->route('categories.index')
                ->with('error', 'Erreur lors de la suppression de la catégorie: ' . $e->getMessage());
        }
    }
}