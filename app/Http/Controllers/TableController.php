<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\User;
use App\Models\Commande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TableController extends Controller
{
    public function index()
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isGerant() && !Auth::user()->isManager()) {
            abort(403, 'Accès non autorisé.');
        }

        $currentUser = Auth::user();
        
        // Utiliser le scope visibleTo pour l'isolation par admin
        $tables = Table::visibleTo($currentUser)
                      ->with('user')
                      ->orderBy('numero')
                      ->get();

        // Utiliser la méthode getAvailableHotesses() du modèle User
        $serveuses = $currentUser->getAvailableHotesses();

        $totalTables = $tables->count();
        $tablesAffectees = $tables->where('user_id', '!=', null)->count();
        $tablesLibres = $tables->where('user_id', null)->count();

        // Créer le tableau stats pour la compatibilité avec la vue
        $stats = [
            'total_tables' => $totalTables,
            'tables_affectees' => $tablesAffectees,
            'tables_libres' => $tablesLibres
        ];

        return view('tables.index', compact('tables', 'totalTables', 'tablesAffectees', 'tablesLibres', 'stats', 'serveuses'));
    }

    public function create()
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isGerant() && !Auth::user()->isManager()) {
            abort(403, 'Accès non autorisé.');
        }

        return view('tables.create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isGerant() && !Auth::user()->isManager()) {
            abort(403, 'Accès non autorisé.');
        }

        $user = Auth::user();
        
        // Validation d'unicité par scope admin
        $validated = $request->validate([
            'numero' => [
                'required', 
                'integer', 
                'min:1',
                function ($attribute, $value, $fail) use ($user) {
                    // Vérifier l'unicité dans le scope admin
                    $query = Table::visibleTo($user)->where('numero', $value);
                    if ($query->exists()) {
                        $fail('Ce numéro de table existe déjà dans votre organisation.');
                    }
                }
            ],
            'nom' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $table = Table::create([
                'numero' => $validated['numero'],
                'nom' => $validated['nom'],
                // Les managers utilisent l'admin_id de leur parent
                'admin_id' => $user->isAdmin() ? $user->id : $user->admin_id,
            ]);

            DB::commit();

            return redirect()->route('tables.index')
                           ->with('success', 'Table créée avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la création: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Table $table)
{
    if (!Auth::user()->isAdmin() && !Auth::user()->isGerant() && !Auth::user()->isManager()) {
        abort(403, 'Accès non autorisé.');
    }

    // Vérifier les permissions avec canManageTable()
    if (!Auth::user()->canManageTable($table)) {
        abort(403, 'Accès non autorisé à cette table.');
    }

    // CORRECTION : Utiliser $hotesses au lieu de $serveuses
    $hotesses = Auth::user()->getAvailableHotesses();

    // Charger les commandes en cours pour cette table
    $commandesEnCours = Commande::where('table_id', $table->id)
                               ->where('statut', 'en cours')
                               ->with(['produits', 'user'])
                               ->get();

    return view('tables.show', compact('table', 'hotesses', 'commandesEnCours'));
}
    public function edit(Table $table)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isGerant() && !Auth::user()->isManager()) {
            abort(403, 'Accès non autorisé.');
        }

        // Vérifier les permissions avec canManageTable()
        if (!Auth::user()->canManageTable($table)) {
            abort(403, 'Accès non autorisé à cette table.');
        }

        // Utiliser la méthode getAvailableHotesses() du modèle User
        $hotesses = Auth::user()->getAvailableHotesses();

        return view('tables.edit', compact('table', 'hotesses'));
    }

    public function update(Request $request, Table $table)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isGerant() && !Auth::user()->isManager()) {
            abort(403, 'Accès non autorisé.');
        }

        // Vérifier les permissions avec canManageTable()
        if (!Auth::user()->canManageTable($table)) {
            return back()->with('error', 'Vous n\'êtes pas autorisé à modifier cette table.')->withInput();
        }

        $user = Auth::user();
        
        // Validation d'unicité par scope admin (en ignorant la table actuelle)
        $validated = $request->validate([
            'numero' => [
                'required', 
                'integer', 
                'min:1',
                function ($attribute, $value, $fail) use ($user, $table) {
                    // Vérifier l'unicité dans le scope admin (en ignorant la table actuelle)
                    $query = Table::visibleTo($user)
                                 ->where('numero', $value)
                                 ->where('id', '!=', $table->id);
                    if ($query->exists()) {
                        $fail('Ce numéro de table existe déjà dans votre organisation.');
                    }
                }
            ],
            'nom' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id',
        ]);

        DB::beginTransaction();

        try {
            // Gestion de l'affectation
            $user_id = null;
            if (!empty($validated['user_id'])) {
                $userAffectation = User::find($validated['user_id']);
                
                // Vérifier que l'utilisateur existe et est une hôtesse
                if (!$userAffectation) {
                    return back()->with('error', 'L\'utilisateur sélectionné n\'existe pas.')->withInput();
                }
                
                if (!$userAffectation->isHotesse()) {
                    return back()->with('error', 'L\'utilisateur sélectionné n\'est pas une hôtesse.')->withInput();
                }
                
                // Vérifier les permissions hiérarchiques avec canManageUser()
                if (!Auth::user()->canManageUser($userAffectation)) {
                    return back()->with('error', 'Vous ne pouvez pas affecter cette table à cette hôtesse.')->withInput();
                }
                
                $user_id = $validated['user_id'];
            }

            // Mettre à jour la table
            $table->update([
                'numero' => $validated['numero'],
                'nom' => $validated['nom'],
                'user_id' => $user_id,
            ]);

            DB::commit();

            return redirect()->route('tables.index')
                           ->with('success', 'Table modifiée avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Erreur modification table ID ' . $table->id . ': ' . $e->getMessage());
            
            return back()->with('error', 'Erreur lors de la modification: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Table $table)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isGerant() && !Auth::user()->isManager()) {
            abort(403, 'Accès non autorisé.');
        }

        // Vérifier les permissions avec canManageTable()
        if (!Auth::user()->canManageTable($table)) {
            return back()->with('error', 'Vous n\'êtes pas autorisé à supprimer cette table.');
        }

        DB::beginTransaction();

        try {
            $table->delete();

            DB::commit();

            return redirect()->route('tables.index')
                           ->with('success', 'Table supprimée avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Méthode pour l'affectation rapide via le modal
     */
    public function affecter(Request $request)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isGerant() && !Auth::user()->isManager()) {
            return response()->json(['error' => 'Accès non autorisé.'], 403);
        }

        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'user_id' => 'required|exists:users,id',
        ]);

        DB::beginTransaction();

        try {
            $table = Table::find($validated['table_id']);
            
            // Vérifier les permissions avec canManageTable()
            if (!Auth::user()->canManageTable($table)) {
                return response()->json(['error' => 'Vous n\'êtes pas autorisé à modifier cette table.'], 403);
            }

            $user = User::find($validated['user_id']);
            
            // Vérifier que c'est une hôtesse
            if (!$user->isHotesse()) {
                return response()->json(['error' => 'L\'utilisateur sélectionné n\'est pas une hôtesse.'], 400);
            }

            // Vérifier les permissions hiérarchiques avec canManageUser()
            if (!Auth::user()->canManageUser($user)) {
                return response()->json(['error' => 'Vous ne pouvez pas affecter cette table à cette hôtesse.'], 403);
            }

            $table->update(['user_id' => $validated['user_id']]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Table affectée avec succès!',
                'hotesse' => $user->prenom . ' ' . $user->nom
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erreur lors de l\'affectation: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Méthode pour libérer une table
     */
    public function liberer(Table $table)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isGerant() && !Auth::user()->isManager()) {
            return back()->with('error', 'Accès non autorisé.');
        }

        // Vérifier les permissions avec canManageTable()
        if (!Auth::user()->canManageTable($table)) {
            return back()->with('error', 'Vous n\'êtes pas autorisé à modifier cette table.');
        }

        DB::beginTransaction();

        try {
            $table->update(['user_id' => null]);

            DB::commit();

            return redirect()->route('tables.index')
                           ->with('success', 'Table libérée avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la libération: ' . $e->getMessage());
        }
    }

    /**
     * Méthode pour l'affectation rapide depuis le dashboard
     */
    public function affecterRapide(Request $request)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isGerant() && !Auth::user()->isManager()) {
            return response()->json(['error' => 'Accès non autorisé.'], 403);
        }

        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'user_id' => 'required|exists:users,id',
        ]);

        DB::beginTransaction();

        try {
            $table = Table::find($validated['table_id']);
            
            // Vérifier les permissions avec canManageTable()
            if (!Auth::user()->canManageTable($table)) {
                return response()->json(['error' => 'Vous n\'êtes pas autorisé à modifier cette table.'], 403);
            }

            $user = User::find($validated['user_id']);
            
            // Vérifier que c'est une hôtesse
            if (!$user->isHotesse()) {
                return response()->json(['error' => 'L\'utilisateur sélectionné n\'est pas une hôtesse.'], 400);
            }

            // Vérifier les permissions hiérarchiques avec canManageUser()
            if (!Auth::user()->canManageUser($user)) {
                return response()->json(['error' => 'Vous ne pouvez pas affecter cette table à cette hôtesse.'], 403);
            }

            $table->update(['user_id' => $validated['user_id']]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Table affectée avec succès à ' . $user->prenom . ' ' . $user->nom,
                'hotesse_nom' => $user->prenom . ' ' . $user->nom
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erreur lors de l\'affectation: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Méthode pour debug des permissions (à supprimer en production)
     */
    public function debugPermissions(Table $table)
    {
        $user = Auth::user();
        
        $debugInfo = [
            'user_id' => $user->id,
            'user_fonction' => $user->fonction,
            'user_admin_id' => $user->admin_id,
            'table_id' => $table->id,
            'table_admin_id' => $table->admin_id,
            'canManageTable' => $user->canManageTable($table) ? 'true' : 'false',
            'isAdmin' => $user->isAdmin() ? 'true' : 'false',
            'isGerant' => $user->isGerant() ? 'true' : 'false',
            'isManager' => $user->isManager() ? 'true' : 'false',
        ];

        \Log::info('Debug Permissions Table:', $debugInfo);

        return response()->json($debugInfo);
    }
}