<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Table;
use App\Models\Product;
use App\Models\User;
use App\Models\CommandeAction;
use App\Models\CommandeProduit;
use App\Models\Caisse;
use Carbon\Carbon; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommandeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Commande::visibleTo($user)
            ->with(['user', 'table.user', 'produits.produit']);

        if ($request->has('statut') && $request->statut) {
            $query->where('statut', $request->statut);
        } else {
            $query->nonSoldees();
        }

        if ($request->has('date_debut') && $request->date_debut) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }
        
        if ($request->has('date_fin') && $request->date_fin) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        if ($request->has('hotesse_id') && $request->hotesse_id && ($user->isAdmin() || $user->isGerant())) {
            $query->where('hotesse_id', $request->hotesse_id);
        }

        if ($request->has('user_id') && $request->user_id && ($user->isAdmin() || $user->isGerant())) {
            $query->where('user_id', $request->user_id);
        }

        $commandes = $query->orderBy('created_at', 'desc')->paginate(20);

        $hotesses = $this->getHotessesForFilters($user);
        $caissiers = $this->getCaissiersForFilters($user);

        $statsQuery = clone $query;
        $totalCommandes = $statsQuery->count();
        $chiffreAffaires = $statsQuery->sum('montant');
        
        $stats = [
            'total_commandes' => $totalCommandes,
            'chiffre_affaires' => $chiffreAffaires,
            'commande_moyenne' => $totalCommandes > 0 ? $chiffreAffaires / $totalCommandes : 0,
        ];

        return view('commandes.index', compact('commandes', 'hotesses', 'caissiers', 'stats'));
    }

    public function commandesSoldees(Request $request)
    {
        $user = Auth::user();
        
        $query = Commande::visibleTo($user)
            ->with(['user', 'table.user', 'produits.produit'])
            ->where('statut', 'soldée');

        if ($request->filled('date_debut')) {
            $query->whereDate('updated_at', '>=', $request->date_debut);
        }
        
        if ($request->filled('date_fin')) {
            $query->whereDate('updated_at', '<=', $request->date_fin);
        }

        if ($request->filled('hotesse_id') && ($user->isAdmin() || $user->isGerant())) {
            $query->where('hotesse_id', $request->hotesse_id);
        }

        if ($request->filled('user_id') && ($user->isAdmin() || $user->isGerant())) {
            $query->where('user_id', $request->user_id);
        }

        $commandes = $query->orderBy('updated_at', 'desc')->paginate(20);

        $hotesses = $this->getHotessesForFilters($user);
        $caissiers = $this->getCaissiersForFilters($user);

        $statsQuery = clone $query;
        $totalCommandes = $statsQuery->count();
        $chiffreAffaires = $statsQuery->sum('montant');
        
        $stats = [
            'total_commandes' => $totalCommandes,
            'chiffre_affaires' => $chiffreAffaires,
            'commande_moyenne' => $totalCommandes > 0 ? $chiffreAffaires / $totalCommandes : 0,
        ];

        return view('commandes.soldees', compact('commandes', 'hotesses', 'caissiers', 'stats'));
    }

    public function create()
    {
        $user = Auth::user();
        
        if (!$user->isCaissier()) {
            abort(403, 'Seuls les caissiers peuvent créer des commandes.');
        }

        $caisse_ouverte = Caisse::where('user_id', $user->id)
                          ->where('statut', 'ouverte')
                          ->exists();
        
        if (!$caisse_ouverte) {
            return redirect()->route('dashboard')
                ->with('error', 'Veuillez ouvrir votre caisse avant de créer une commande.');
        }

        $tables = $this->getTablesForCommande($user);
        $produits = $this->getProduitsForCommande($user);

        if ($tables->isEmpty()) {
            return redirect()->route('commandes.index')
                       ->with('error', 'Aucune table affectée disponible. Veuillez affecter des tables aux hôtesses d\'abord.');
        }

        if ($produits->isEmpty()) {
            return redirect()->route('commandes.index')
                       ->with('error', 'Aucun produit disponible. Veuillez ajouter des produits d\'abord.');
        }

        return view('commandes.create', compact('tables', 'produits'));
    }

    /**
     * Génération de numéro de commande garanti unique
     */
    private function generateUniqueNumeroCommande()
    {
        // Utilisation de uniqid() qui est GARANTI UNIQUE
        $uniquePart = uniqid('', true); // Génère quelque chose comme: 6789abc123def
        $cleanUnique = substr(str_replace('.', '', $uniquePart), -8); // Prend les 8 derniers caractères
        
        return 'CMD' . date('YmdHis') . $cleanUnique;
    }

    private function getTablesForCommande($user)
    {
        $query = Table::with(['user'])
                ->whereNotNull('user_id')
                ->where('statut', 'libre');

        if ($user->isSuperAdmin()) {
            return $query->orderBy('numero')->get();
        }

        if ($user->isAdmin()) {
            return $query->where('admin_id', $user->id)
                    ->orderBy('numero')
                    ->get();
        }

        if ($user->admin_id) {
            return $query->where('admin_id', $user->admin_id)
                    ->orderBy('numero')
                    ->get();
        }

        return collect();
    }

    private function getProduitsForCommande($user)
    {
        $query = Product::where('quantite', '>', 0);

        if ($user->isSuperAdmin()) {
            return $query->orderBy('designation')->get();
        }

        if ($user->isAdmin()) {
            return $query->where('user_id', $user->id)
                    ->orderBy('designation')
                    ->get();
        }

        return $query->where('user_id', $user->admin_id)
                ->orderBy('designation')
                ->get();
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        if ($user->isCaissier()) {
            $caisse_ouverte = Caisse::where('user_id', $user->id)
                                  ->where('statut', 'ouverte')
                                  ->exists();
            
            if (!$caisse_ouverte) {
                return redirect()->route('dashboard')
                    ->with('error', 'Veuillez ouvrir votre caisse avant de créer une commande.');
            }
        }

        DB::beginTransaction();

        try {
            $request->validate([
                'table_id' => 'required|exists:tables,id',
                'produits' => 'required|array|min:1',
                'produits.*.produit_id' => 'required|exists:products,id',
                'produits.*.quantite' => 'required|integer|min:1',
                'description' => 'nullable|string|max:1000',
            ]);

            $table = Table::with('user')->find($request->table_id);

            if (!$table->user_id) {
                throw new \Exception('La table sélectionnée n\'est pas affectée à une hôtesse.');
            }

            $montantTotal = 0;
            foreach ($request->produits as $produitData) {
                $produit = Product::find($produitData['produit_id']);
                $montantTotal += $produit->prix * $produitData['quantite'];
            }

            // CORRECTION : Utiliser la nouvelle méthode de génération unique
            $numero_commande = $this->generateUniqueNumeroCommande();

            $commande = Commande::create([
                'user_id' => $user->id,
                'hotesse_id' => $table->user_id,
                'table_id' => $request->table_id,
                'montant' => $montantTotal,
                'statut' => 'en cours',
                'notes' => $request->notes,
                'description' => $request->description,
                'admin_id' => $user->admin_id,
                'numero_commande' => $numero_commande
            ]);

            foreach ($request->produits as $produitData) {
                $produit = Product::find($produitData['produit_id']);
                
                DB::table('commande_produits')->insert([
                    'commande_id' => $commande->id,
                    'produit_id' => $produit->id,
                    'quantite' => $produitData['quantite'],
                    'prix_unitaire' => $produit->prix,
                    'prix_total' => $produit->prix * $produitData['quantite'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $produit->decrement('quantite', $produitData['quantite']);
            }

            DB::commit();

            return redirect()->route('commandes.index')
                           ->with('success', 'Commande créée avec succès! Numéro: ' . $commande->numero_commande);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Erreur lors de la création de la commande: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'table_id' => $request->table_id,
                'numero_commande_tentative' => $numero_commande ?? 'non_généré'
            ]);
            
            return back()->with('error', 'Erreur: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $user = Auth::user();
        $commande = Commande::with(['user', 'table.user', 'produits.produit', 'actions.user'])
            ->findOrFail($id);

        if (!$commande->peutEtreVuePar($user)) {
            abort(403, 'Accès non autorisé à cette commande.');
        }

        return view('commandes.show', compact('commande'));
    }

    public function edit($id)
    {
        $user = Auth::user();
        $commande = Commande::with(['user', 'table.user', 'produits.produit'])->findOrFail($id);

        // CORRECTION: Utiliser l'attribut calculé pour la vérification
        if (!$commande->peut_etre_modifiee || !$commande->peutEtreModifieePar($user)) {
            abort(403, 'Non autorisé à modifier cette commande.');
        }

        $tables = $this->getTablesForCommande($user);
        $produits = $this->getProduitsForCommande($user);

        return view('commandes.edit', compact('commande', 'tables', 'produits'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $commande = Commande::with(['produits'])->findOrFail($id);

        // CORRECTION: Utiliser l'attribut calculé pour la vérification
        if (!$commande->peut_etre_modifiee || !$commande->peutEtreModifieePar($user)) {
            abort(403, 'Non autorisé à modifier cette commande.');
        }

        $request->validate([
            'table_id' => 'required|exists:tables,id',
            'produits' => 'required|array|min:1',
            'produits.*.produit_id' => 'required|exists:products,id',
            'produits.*.quantite' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            CommandeAction::create([
                'commande_id' => $commande->id,
                'user_id' => $user->id,
                'action' => 'modifier',
                'details' => json_encode([
                    'ancienne_table' => $commande->table_id,
                    'anciens_produits' => $commande->produits->toArray(),
                    'ancien_montant' => $commande->montant,
                    'ancienne_description' => $commande->description
                ]),
                'date_action' => now()
            ]);

            $table = Table::with('user')->find($request->table_id);
            
            if (!$table->user_id) {
                throw new \Exception('La table doit être affectée à une hôtesse.');
            }

            foreach ($commande->produits as $ancienProduit) {
                $produit = Product::find($ancienProduit->produit_id);
                if ($produit) {
                    $produit->increment('quantite', $ancienProduit->quantite);
                }
            }

            $commande->produits()->delete();

            $montantTotal = 0;

            foreach ($request->produits as $produitData) {
                $produit = Product::find($produitData['produit_id']);
                
                if (!$produit) {
                    throw new \Exception("Produit introuvable: ID {$produitData['produit_id']}");
                }

                if ($produit->quantite < $produitData['quantite']) {
                    throw new \Exception("Stock insuffisant pour {$produit->designation}. Stock disponible: {$produit->quantite}");
                }

                $prixTotal = $produit->prix * $produitData['quantite'];
                $montantTotal += $prixTotal;

                CommandeProduit::create([
                    'commande_id' => $commande->id,
                    'produit_id' => $produit->id,
                    'quantite' => $produitData['quantite'],
                    'prix_unitaire' => $produit->prix,
                    'prix_total' => $prixTotal
                ]);

                $produit->decrement('quantite', $produitData['quantite']);
            }

            $commande->update([
                'table_id' => $request->table_id,
                'hotesse_id' => $table->user_id,
                'montant' => $montantTotal,
                'notes' => $request->notes,
                'description' => $request->description,
            ]);

            DB::commit();

            return redirect()->route('commandes.index')
                           ->with('success', 'Commande modifiée avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la modification: ' . $e->getMessage())
                        ->withInput();
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $commande = Commande::with(['produits'])->findOrFail($id);

        // CORRECTION: Utiliser l'attribut calculé pour la vérification
        if (!$commande->peut_etre_modifiee || !$commande->peutEtreModifieePar($user)) {
            abort(403, 'Non autorisé à supprimer cette commande.');
        }

        DB::beginTransaction();

        try {
            foreach ($commande->produits as $produitCommande) {
                $produit = Product::find($produitCommande->produit_id);
                if ($produit) {
                    $produit->increment('quantite', $produitCommande->quantite);
                }
            }

            CommandeAction::create([
                'commande_id' => $commande->id,
                'user_id' => $user->id,
                'action' => 'supprimer',
                'details' => json_encode($commande->toArray()),
                'date_action' => now()
            ]);

            $commande->delete();

            DB::commit();

            return redirect()->route('commandes.index')
                           ->with('success', 'Commande supprimée avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    public function solderForm($id)
    {
        $user = Auth::user();
        $commande = Commande::with(['user', 'table.user', 'produits.produit'])
            ->findOrFail($id);

        if (!$user->isAdmin() && !$user->isGerant() && $commande->user_id !== $user->id) {
            abort(403, 'Non autorisé à solder cette commande.');
        }

        if ($commande->statut === 'soldée') {
            return redirect()->route('commandes.index')
                ->with('error', 'Cette commande est déjà soldée.');
        }

        return view('commandes.solder', compact('commande'));
    }

    public function solder(Request $request, $id)
    {
        $user = Auth::user();
        $commande = Commande::findOrFail($id);

        if (!$user->isAdmin() && !$user->isGerant() && $commande->user_id !== $user->id) {
            abort(403, 'Non autorisé à solder cette commande.');
        }

        if ($commande->statut === 'soldée') {
            return back()->with('error', 'Cette commande est déjà soldée.');
        }

        $request->validate([
            'montant_remis' => 'required|numeric|min:' . $commande->montant,
            'methode_paiement' => 'required|in:espece,carte,cheque,virement',
            'description' => 'nullable|string|max:1000'
        ], [
            'montant_remis.min' => 'Le montant remis doit être supérieur ou égal au montant de la commande (' . number_format($commande->montant, 0, ',', ' ') . ' FCFA).'
        ]);

        $monnaie_rendue = $request->montant_remis - $commande->montant;

        DB::beginTransaction();

        try {
            CommandeAction::create([
                'commande_id' => $commande->id,
                'user_id' => $user->id,
                'action' => 'solder',
                'details' => json_encode([
                    'montant_commande' => $commande->montant,
                    'montant_remis' => $request->montant_remis,
                    'monnaie_rendue' => $monnaie_rendue,
                    'methode_paiement' => $request->methode_paiement,
                    'description' => $request->description
                ]),
                'date_action' => now()
            ]);

            $commande->update([
                'statut' => 'soldée',
                'montant_remis' => $request->montant_remis,
                'monnaie_rendue' => $monnaie_rendue,
                'methode_paiement' => $request->methode_paiement,
                'description' => $request->description,
                'date_soldage' => now()
            ]);

            DB::commit();

            return redirect()->route('commandes.soldees')
                ->with('success', 'Commande soldée avec succès! Montant remis: ' . number_format($request->montant_remis, 0, ',', ' ') . ' FCFA - Monnaie rendue: ' . number_format($monnaie_rendue, 0, ',', ' ') . ' FCFA');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors du soldage: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function historiqueActions($id)
    {
        $user = Auth::user();
        $commande = Commande::findOrFail($id);

        if (!$commande->peutEtreVuePar($user)) {
            abort(403, 'Accès non autorisé.');
        }

        $actions = CommandeAction::with('user')
            ->where('commande_id', $id)
            ->orderBy('date_action', 'desc')
            ->get();

        return view('commandes.historique-actions', compact('commande', 'actions'));
    }

    public function ajouterProduit(Request $request, $id)
    {
        $user = Auth::user();
        $commande = Commande::findOrFail($id);

        // CORRECTION: Utiliser l'attribut calculé pour la vérification
        if (!$commande->peut_etre_modifiee || !$commande->peutEtreModifieePar($user)) {
            abort(403, 'Non autorisé à modifier cette commande.');
        }

        $request->validate([
            'produit_id' => 'required|exists:products,id',
            'quantite' => 'required|integer|min:1'
        ]);

        DB::beginTransaction();

        try {
            $produit = Product::find($request->produit_id);
            
            if (!$produit) {
                throw new \Exception("Produit introuvable: ID {$request->produit_id}");
            }

            if ($produit->quantite < $request->quantite) {
                throw new \Exception("Stock insuffisant pour {$produit->designation}. Stock disponible: {$produit->quantite}");
            }

            $prixTotal = $produit->prix * $request->quantite;

            CommandeProduit::create([
                'commande_id' => $commande->id,
                'produit_id' => $produit->id,
                'quantite' => $request->quantite,
                'prix_unitaire' => $produit->prix,
                'prix_total' => $prixTotal
            ]);

            $produit->decrement('quantite', $request->quantite);

            $commande->mettreAJourMontantTotal();

            DB::commit();

            return back()->with('success', 'Produit ajouté à la commande avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'ajout du produit: ' . $e->getMessage());
        }
    }

    public function supprimerProduit($id, $produitId)
    {
        $user = Auth::user();
        $commande = Commande::findOrFail($id);

        // CORRECTION: Utiliser l'attribut calculé pour la vérification
        if (!$commande->peut_etre_modifiee || !$commande->peutEtreModifieePar($user)) {
            abort(403, 'Non autorisé à modifier cette commande.');
        }

        $produitCommande = CommandeProduit::where('commande_id', $id)
            ->where('id', $produitId)
            ->firstOrFail();

        DB::beginTransaction();

        try {
            $produit = Product::find($produitCommande->produit_id);
            if ($produit) {
                $produit->increment('quantite', $produitCommande->quantite);
            }

            $produitCommande->delete();

            $commande->mettreAJourMontantTotal();

            DB::commit();

            return back()->with('success', 'Produit retiré de la commande avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la suppression du produit: ' . $e->getMessage());
        }
    }

    public function showReceipt($id)
    {
        try {
            $commande = Commande::with(['user', 'table.user'])
                ->where('statut', 'soldée')
                ->findOrFail($id);

            $produitsData = DB::select("
                SELECT 
                    p.designation,
                    cp.quantite,
                    cp.prix_unitaire,
                    cp.prix_total
                FROM commande_produits cp
                INNER JOIN products p ON cp.produit_id = p.id
                WHERE cp.commande_id = ?
            ", [$id]);

            $produitsData = array_map(function($item) {
                return [
                    'designation' => $item->designation,
                    'quantite' => $item->quantite,
                    'prix_unitaire' => $item->prix_unitaire,
                    'total' => $item->prix_total
                ];
            }, $produitsData);

            return view('commandes.receipt', compact('commande', 'produitsData'));

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors du chargement du reçu: ' . $e->getMessage());
        }
    }

    public function downloadVentes(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isGerant()) {
            abort(403, 'Accès non autorisé.');
        }

        $request->validate([
            'type_export' => 'required|in:commandes,ventes',
            'statut_export' => 'required|in:toutes,soldees,en_cours',
            'date_debut_export' => 'nullable|date',
            'date_fin_export' => 'nullable|date|after_or_equal:date_debut_export'
        ]);

        $query = Commande::visibleTo($user)
            ->with(['user', 'table.user', 'produits.produit']);

        if ($request->filled('date_debut_export')) {
            $query->whereDate('created_at', '>=', $request->date_debut_export);
        }
        
        if ($request->filled('date_fin_export')) {
            $query->whereDate('created_at', '<=', $request->date_fin_export);
        }

        if ($request->statut_export === 'soldees') {
            $query->where('statut', 'soldée');
        } elseif ($request->statut_export === 'en_cours') {
            $query->where('statut', 'en cours');
        }

        $commandes = $query->orderBy('created_at', 'desc')->get();

        if ($request->type_export === 'commandes') {
            return $this->generateCommandesExport($commandes, $request);
        } else {
            return $this->generateVentesExport($commandes, $request);
        }
    }

    private function generateCommandesExport($commandes, Request $request)
    {
        $fileName = 'commandes_' . Carbon::now()->format('Y-m-d_H-i') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function() use ($commandes) {
            $file = fopen('php://output', 'w');
            
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            
            fputcsv($file, [
                'N° Commande',
                'Date Création',
                'Table',
                'Hôtesse',
                'Caissier',
                'Statut',
                'Montant Total (FCFA)',
                'Méthode Paiement',
                'Date Soldage',
                'Montant Remis (FCFA)',
                'Monnaie Rendue (FCFA)',
                'Nombre Produits',
                'Produits Détail',
                'Notes',
                'Description'
            ], ';');

            foreach ($commandes as $commande) {
                $produitsDetail = [];
                foreach ($commande->produits as $produit) {
                    $produitsDetail[] = $produit->produit->designation . ' (x' . $produit->quantite . ')';
                }
                $produitsString = implode(' | ', $produitsDetail);

                fputcsv($file, [
                    $commande->numero_commande ?? 'CMD-' . $commande->id,
                    $commande->created_at->format('d/m/Y H:i'),
                    $commande->table ? 'Table ' . $commande->table->numero . ' - ' . $commande->table->nom : 'N/A',
                    $commande->hotesse ? $commande->hotesse->prenom . ' ' . $commande->hotesse->name : 'N/A',
                    $commande->user ? $commande->user->prenom . ' ' . $commande->user->name : 'N/A',
                    ucfirst($commande->statut),
                    number_format($commande->montant, 0, ',', ' '),
                    $commande->methode_paiement ?? 'N/A',
                    $commande->date_soldage ? $commande->date_soldage->format('d/m/Y H:i') : 'N/A',
                    $commande->montant_remis ? number_format($commande->montant_remis, 0, ',', ' ') : 'N/A',
                    $commande->monnaie_rendue ? number_format($commande->monnaie_rendue, 0, ',', ' ') : 'N/A',
                    $commande->produits->count(),
                    $produitsString,
                    $commande->notes ?? '',
                    $commande->description ?? ''
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function generateVentesExport($commandes, Request $request)
    {
        $fileName = 'ventes_' . Carbon::now()->format('Y-m-d_H-i') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function() use ($commandes) {
            $file = fopen('php://output', 'w');
            
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            
            fputcsv($file, [
                'Date',
                'N° Commande',
                'Table',
                'Hôtesse',
                'Caissier',
                'Montant (FCFA)',
                'Statut',
                'Méthode Paiement'
            ], ';');

            foreach ($commandes as $commande) {
                fputcsv($file, [
                    $commande->created_at->format('d/m/Y'),
                    $commande->numero_commande ?? 'CMD-' . $commande->id,
                    $commande->table ? 'Table ' . $commande->table->numero : 'N/A',
                    $commande->hotesse ? $commande->hotesse->prenom . ' ' . $commande->hotesse->name : 'N/A',
                    $commande->user ? $commande->user->prenom . ' ' . $commande->user->name : 'N/A',
                    number_format($commande->montant, 0, ',', ' '),
                    ucfirst($commande->statut),
                    $commande->methode_paiement ?? 'N/A'
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function showExportForm()
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isGerant()) {
            abort(403, 'Accès non autorisé.');
        }

        return view('commandes.export');
    }

    private function getHotessesForFilters(User $user)
    {
        if ($user->isSuperAdmin()) {
            return User::where('fonction', 'hotesse')->get();
        }

        if ($user->isAdmin()) {
            return User::where('fonction', 'hotesse')
                      ->where('admin_id', $user->id)
                      ->get();
        }

        if ($user->isGerant()) {
            return User::where('fonction', 'hotesse')
                      ->where('admin_id', $user->admin_id)
                      ->get();
        }

        return collect([]);
    }

    private function getCaissiersForFilters(User $user)
    {
        if ($user->isSuperAdmin()) {
            return User::where('fonction', 'caissier')->get();
        }

        if ($user->isAdmin()) {
            return User::where('fonction', 'caissier')
                      ->where('admin_id', $user->id)
                      ->get();
        }

        if ($user->isGerant()) {
            return User::where('fonction', 'caissier')
                      ->where('admin_id', $user->admin_id)
                      ->get();
        }

        return collect([]);
    }
}