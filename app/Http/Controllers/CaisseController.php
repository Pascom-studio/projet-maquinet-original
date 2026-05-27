<?php

namespace App\Http\Controllers;

use App\Models\Caisse;
use App\Models\User;
use App\Models\TransactionCaisse;
use App\Models\Commande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CaisseController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $caisses = Caisse::with('user')->where('user_id', Auth::id())->get();
        $caisse_actuelle = Caisse::where('user_id', Auth::id())
                                ->where('statut', 'ouverte')
                                ->first();
        
        // Calculer le total des commandes soldées pour la caisse actuelle
        if ($caisse_actuelle) {
            $total_commandes_soldees = Commande::where('user_id', $user->id)
                ->where('statut', 'soldée')
                ->whereBetween('updated_at', [$caisse_actuelle->date_ouverture, now()])
                ->sum('montant');
            
            $caisse_actuelle->total_commandes_soldees = $total_commandes_soldees;
        }

        // Récupérer uniquement les utilisateurs de la même hiérarchie
        $caissiers = $this->getUsersSameHierarchy();
        
        return view('caisse.index', compact('caisses', 'caisse_actuelle', 'caissiers'));
    }

    /**
     * Récupère les utilisateurs de la même hiérarchie (même admin parent)
     */
    private function getUsersSameHierarchy()
    {
        $user = Auth::user();
        
        if ($user->isSuperAdmin()) {
            // SuperAdmin voit tous les utilisateurs
            return User::whereIn('fonction', ['admin', 'gerant', 'caissier'])
                      ->where('id', '!=', $user->id)
                      ->get()
                      ->map(function($user) {
                          return $this->addCaisseInfo($user);
                      });
        }

        if ($user->isAdmin()) {
            // Admin voit les gérants et caissiers de sa hiérarchie
            return User::where(function($query) use ($user) {
                    $query->where('admin_id', $user->id)
                          ->whereIn('fonction', ['gerant', 'caissier']);
                })
                ->where('id', '!=', $user->id)
                ->get()
                ->map(function($user) {
                    return $this->addCaisseInfo($user);
                });
        }

        if ($user->isGerant()) {
            // Gérant voit les caissiers de son admin parent
            return User::where('admin_id', $user->admin_id)
                      ->where('fonction', 'caissier')
                      ->where('id', '!=', $user->id)
                      ->get()
                      ->map(function($user) {
                          return $this->addCaisseInfo($user);
                      });
        }

        // Caissier ne voit personne
        return collect([]);
    }

    /**
     * Ajoute les informations de caisse à un utilisateur
     */
    private function addCaisseInfo($user)
    {
        $caisse_ouverte = Caisse::where('user_id', $user->id)
                               ->where('statut', 'ouverte')
                               ->first();
        
        $solde_caisse = 0;
        if ($caisse_ouverte) {
            $solde_caisse = $caisse_ouverte->solde_actuel;
        }
        
        $user->solde_caisse = $solde_caisse;
        $user->caisse_ouverte = !is_null($caisse_ouverte);
        
        return $user;
    }

    /**
     * Vérifie si l'utilisateur peut gérer la caisse d'un autre utilisateur
     */
    private function canManageUserCaisse($targetUserId)
    {
        $user = Auth::user();
        
        if ($user->isSuperAdmin()) {
            return true;
        }

        $targetUser = User::find($targetUserId);
        if (!$targetUser) {
            return false;
        }

        if ($user->isAdmin()) {
            // Admin peut gérer les gérants et caissiers de sa hiérarchie
            return $targetUser->admin_id === $user->id && 
                   in_array($targetUser->fonction, ['gerant', 'caissier']);
        }

        if ($user->isGerant()) {
            // Gérant peut gérer les caissiers de son admin
            return $targetUser->admin_id === $user->admin_id && 
                   $targetUser->fonction === 'caissier';
        }

        return false;
    }

    public function ouvrir(Request $request)
    {
        $user = Auth::user();
        
        // Vérifier si une caisse est déjà ouverte
        $caisse_existante = Caisse::where('user_id', $user->id)
                                ->where('statut', 'ouverte')
                                ->first();

        if ($caisse_existante) {
            return back()->with('error', 'Vous avez déjà une caisse ouverte.');
        }

        // CORRECTION : Logique différente selon le rôle
        $solde_ouverture = 0;
        
        if ($user->isCaissier() || $user->isGerant()) {
            // Pour le caissier et le gérant : solde d'ouverture = solde de fermeture précédent
            $derniere_caisse = Caisse::where('user_id', $user->id)
                                   ->where('statut', 'fermee')
                                   ->orderBy('date_fermeture', 'desc')
                                   ->first();
            
            if ($derniere_caisse) {
                $solde_ouverture = $derniere_caisse->solde_fermeture ?? 0;
            } else {
                // Première ouverture de caisse
                $solde_ouverture = 0;
            }
            
        } else {
            // CORRECTION POUR L'ADMIN : solde d'ouverture = solde de fermeture précédent + solde saisi manuellement
            $request->validate([
                'solde_ouverture' => 'required|numeric|min:0'
            ]);
            
            // Récupérer le solde de fermeture précédent
            $derniere_caisse = Caisse::where('user_id', $user->id)
                                   ->where('statut', 'fermee')
                                   ->orderBy('date_fermeture', 'desc')
                                   ->first();
            
            $solde_fermeture_precedent = $derniere_caisse->solde_fermeture ?? 0;
            $solde_saisi_manuel = $request->solde_ouverture;
            
            // CORRECTION : Le solde d'ouverture = solde de fermeture précédent + solde saisi manuellement
            $solde_ouverture = $solde_fermeture_precedent + $solde_saisi_manuel;
        }

        $caisse = Caisse::create([
            'user_id' => $user->id,
            'statut' => 'ouverte',
            'solde_ouverture' => $solde_ouverture,
            'solde_actuel' => $solde_ouverture,
            'date_ouverture' => now(),
        ]);

        return redirect()->route('caisse.index')
            ->with('success', 'Caisse ouverte avec succès. Solde d\'ouverture: ' . number_format($solde_ouverture, 0, ',', ' ') . ' FCFA');
    }

    public function fermer(Request $request)
    {
        $user = Auth::user();
        $caisse = Caisse::where('user_id', $user->id)
                        ->where('statut', 'ouverte')
                        ->firstOrFail();

        // Calculer le total des commandes soldées avant fermeture
        $total_commandes_soldees = Commande::where('user_id', $user->id)
            ->where('statut', 'soldée')
            ->whereBetween('updated_at', [$caisse->date_ouverture, now()])
            ->sum('montant');

        DB::beginTransaction();

        try {
            // Calcul du solde final basé sur solde_actuel
            $solde_final = $caisse->solde_actuel;

            $caisse->update([
                'statut' => 'fermee',
                'solde_fermeture' => $solde_final,
                'date_fermeture' => now(),
                'total_commandes_soldees' => $total_commandes_soldees
            ]);

            DB::commit();

            return redirect()->route('caisse.index')
                ->with('success', 'Caisse fermée avec succès. Solde de fermeture: ' . number_format($solde_final, 0, ',', ' ') . ' FCFA');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la fermeture de la caisse: ' . $e->getMessage());
        }
    }

    public function retrait(Request $request)
    {
        $user = Auth::user();
        
        // Admin ET Gérant peuvent effectuer des retraits
        if (!$user->isAdmin() && !$user->isGerant()) {
            abort(403, 'Accès non autorisé. Seuls les administrateurs et gérants peuvent effectuer des retraits.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'montant' => 'required|numeric|min:0.01'
        ]);

        // Vérifier les permissions selon la hiérarchie
        if (!$this->canManageUserCaisse($request->user_id)) {
            abort(403, 'Accès non autorisé à cette caisse.');
        }

        $targetUser = User::find($request->user_id);
        $caisse_cible = Caisse::where('user_id', $request->user_id)
                        ->where('statut', 'ouverte')
                        ->first();

        if (!$caisse_cible) {
            return back()->with('error', 'La caisse de l\'utilisateur sélectionné n\'est pas ouverte.');
        }

        // Utiliser solde_actuel directement
        $solde_actuel_cible = $caisse_cible->solde_actuel;

        if ($solde_actuel_cible < $request->montant) {
            return back()->with('error', 'Solde insuffisant pour effectuer ce retrait. Solde disponible: ' . number_format($solde_actuel_cible, 0, ',', ' ') . ' FCFA');
        }

        // Vérifier que l'utilisateur a une caisse ouverte
        $caisse_utilisateur = Caisse::where('user_id', $user->id)
                                  ->where('statut', 'ouverte')
                                  ->first();

        if (!$caisse_utilisateur) {
            return back()->with('error', 'Vous devez avoir une caisse ouverte pour effectuer un retrait.');
        }

        DB::beginTransaction();

        try {
            // 1. Retirer de la caisse cible
            $caisse_cible->decrement('solde_actuel', $request->montant);
            $caisse_cible->increment('total_retraits', $request->montant);

            // 2. Ajouter à la caisse de l'utilisateur (admin ou gérant)
            $caisse_utilisateur->increment('solde_actuel', $request->montant);
            $caisse_utilisateur->increment('total_approvisionnements', $request->montant);

            // 3. Créer la transaction de retrait pour la caisse cible
            TransactionCaisse::create([
                'caisse_id' => $caisse_cible->id,
                'user_id' => $user->id,
                'type' => 'retrait',
                'montant' => $request->montant,
                'description' => 'Retrait effectué par ' . 
                               ($user->isAdmin() ? 'l\'administrateur ' : 'le gérant ') . 
                               $user->prenom . ' ' . $user->name
            ]);

            // 4. Créer une transaction d'approvisionnement pour la caisse de l'utilisateur
            TransactionCaisse::create([
                'caisse_id' => $caisse_utilisateur->id,
                'user_id' => $user->id,
                'type' => 'approvisionnement',
                'montant' => $request->montant,
                'description' => 'Retrait de la caisse de ' . $targetUser->prenom . ' ' . $targetUser->name
            ]);

            DB::commit();

            return back()->with('success', 
                'Retrait de ' . number_format($request->montant, 0, ',', ' ') . 
                ' FCFA effectué avec succès. Le montant a été ajouté à votre caisse.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors du retrait: ' . $e->getMessage());
        }
    }

    public function approvisionnement(Request $request)
    {
        $user = Auth::user();
        
        // Admin ET Gérant peuvent effectuer des approvisionnements
        if (!$user->isAdmin() && !$user->isGerant()) {
            abort(403, 'Accès non autorisé.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'montant' => 'required|numeric|min:0.01'
        ]);

        // Vérifier les permissions selon la hiérarchie
        if (!$this->canManageUserCaisse($request->user_id)) {
            abort(403, 'Accès non autorisé à cette caisse.');
        }

        $targetUser = User::find($request->user_id);
        $caisse_cible = Caisse::where('user_id', $request->user_id)
                        ->where('statut', 'ouverte')
                        ->first();

        if (!$caisse_cible) {
            return back()->with('error', 'La caisse de l\'utilisateur sélectionné n\'est pas ouverte.');
        }

        // Vérifier que l'utilisateur a une caisse ouverte
        $caisse_utilisateur = Caisse::where('user_id', $user->id)
                                  ->where('statut', 'ouverte')
                                  ->first();

        if (!$caisse_utilisateur) {
            return back()->with('error', 'Vous devez avoir une caisse ouverte pour effectuer un approvisionnement.');
        }

        // Utiliser solde_actuel directement
        $solde_actuel_utilisateur = $caisse_utilisateur->solde_actuel;

        // Vérifier le solde de l'utilisateur
        if ($solde_actuel_utilisateur < $request->montant) {
            return back()->with('error', 
                'Solde insuffisant dans votre caisse pour effectuer cet approvisionnement. ' .
                'Solde disponible: ' . number_format($solde_actuel_utilisateur, 0, ',', ' ') . ' FCFA');
        }

        DB::beginTransaction();

        try {
            // 1. Retirer de la caisse de l'utilisateur (admin ou gérant)
            $caisse_utilisateur->decrement('solde_actuel', $request->montant);
            $caisse_utilisateur->increment('total_retraits', $request->montant);

            // 2. Ajouter à la caisse cible
            $caisse_cible->increment('solde_actuel', $request->montant);
            $caisse_cible->increment('total_approvisionnements', $request->montant);

            // 3. Créer la transaction de retrait pour la caisse de l'utilisateur
            TransactionCaisse::create([
                'caisse_id' => $caisse_utilisateur->id,
                'user_id' => $user->id,
                'type' => 'retrait',
                'montant' => $request->montant,
                'description' => 'Approvisionnement de la caisse de ' . $targetUser->prenom . ' ' . $targetUser->name
            ]);

            // 4. Créer la transaction d'approvisionnement pour la caisse cible
            TransactionCaisse::create([
                'caisse_id' => $caisse_cible->id,
                'user_id' => $user->id,
                'type' => 'approvisionnement',
                'montant' => $request->montant,
                'description' => 'Approvisionnement effectué par ' . 
                               ($user->isAdmin() ? 'l\'administrateur ' : 'le gérant ') . 
                               $user->prenom . ' ' . $user->name
            ]);

            DB::commit();

            return back()->with('success', 
                'Approvisionnement de ' . number_format($request->montant, 0, ',', ' ') . 
                ' FCFA effectué avec succès. Le montant a été déduit de votre caisse.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'approvisionnement: ' . $e->getMessage());
        }
    }

    public function depense(Request $request)
    {
        $user = Auth::user();
        
        // AUTORISER les caissiers, gérants ET admins
        if (!$user->isAdmin() && !$user->isGerant() && !$user->isCaissier()) {
            abort(403, 'Accès non autorisé. Seuls les administrateurs, gérants et caissiers peuvent effectuer des dépenses.');
        }

        $request->validate([
            'montant' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500'
        ]);

        $caisse = Caisse::where('user_id', $user->id)
                       ->where('statut', 'ouverte')
                       ->firstOrFail();

        // Utiliser solde_actuel directement
        $solde_actuel = $caisse->solde_actuel;

        if ($solde_actuel < $request->montant) {
            return back()->with('error', 
                'Solde insuffisant. Disponible: ' . number_format($solde_actuel, 0, ',', ' ') . ' FCFA');
        }

        DB::beginTransaction();

        try {
            $caisse->decrement('solde_actuel', $request->montant);
            $caisse->increment('total_depenses', $request->montant);

            TransactionCaisse::create([
                'caisse_id' => $caisse->id,
                'user_id' => $user->id,
                'type' => 'depense',
                'montant' => $request->montant,
                'description' => 'Dépense: ' . $request->description
            ]);

            // Calculer le nouveau solde
            $nouveau_solde = $solde_actuel - $request->montant;

            DB::commit();

            return redirect()->route('caisse.index')->with('success', 
                'Dépense de ' . number_format($request->montant, 0, ',', ' ') . 
                ' FCFA enregistrée. Nouveau solde: ' . number_format($nouveau_solde, 0, ',', ' ') . ' FCFA');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'enregistrement de la dépense: ' . $e->getMessage());
        }
    }

    /**
     * Méthode pour l'historique des transactions
     */
    public function historique(Request $request)
    {
        $user = Auth::user();
        
        $query = TransactionCaisse::with(['caisse.user', 'user'])
                    ->orderBy('created_at', 'desc');

        // Filtrer selon les permissions
        if ($user->isAdmin()) {
            $query->whereHas('caisse.user', function($q) use ($user) {
                $q->where('admin_id', $user->id)
                  ->orWhere('id', $user->id);
            });
        } elseif ($user->isGerant()) {
            $query->whereHas('caisse.user', function($q) use ($user) {
                $q->where('admin_id', $user->admin_id);
            });
        } else {
            $query->whereHas('caisse', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Filtrage par date
        if ($request->has('date_debut') && $request->date_debut) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }
        
        if ($request->has('date_fin') && $request->date_fin) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        // Filtrage par type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $transactions = $query->paginate(20);

        return view('caisse.historique', compact('transactions'));
    }  
}