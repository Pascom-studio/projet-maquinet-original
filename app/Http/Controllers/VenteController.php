<?php

namespace App\Http\Controllers;

use App\Models\Vente;
use App\Models\LigneVente;
use App\Models\Product;
use App\Models\Caisse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VenteController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Construire la requête de base
        $query = Vente::visibleTo($user)->with(['user', 'lignesVente.product']);

        // CORRECTION : Filtrer pour n'afficher que les ventes de la caisse actuelle POUR TOUS LES UTILISATEURS
        if ($user->isCaissier()) {
            $caisse_actuelle = Caisse::where('user_id', $user->id)
                                   ->where('statut', 'ouverte')
                                   ->first();
            
            if ($caisse_actuelle) {
                // Si une caisse est ouverte, afficher seulement les ventes créées après l'ouverture de cette caisse
                $query->where('created_at', '>=', $caisse_actuelle->date_ouverture);
            } else {
                // Si aucune caisse n'est ouverte, afficher seulement les ventes du jour
                $query->whereDate('created_at', Carbon::today());
            }
        } elseif ($user->isGerant() || $user->isAdmin()) {
            // CORRECTION : Pour gérant et admin, afficher seulement les ventes du jour actuel
            $query->whereDate('created_at', Carbon::today());
        }

        // Filtrage par date (si spécifié manuellement)
        if ($request->has('date_debut') && $request->date_debut) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }
        
        if ($request->has('date_fin') && $request->date_fin) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        // Filtrage par utilisateur (seulement pour Admin/Gérant)
        if ($request->has('user_id') && $request->user_id && ($user->isGerant() || $user->isAdmin())) {
            // Vérifier que l'utilisateur filtré fait partie de la hiérarchie
            $filteredUser = User::find($request->user_id);
            if ($filteredUser && $user->canManageUser($filteredUser)) {
                $query->where('user_id', $request->user_id);
            }
        }

        $ventes = $query->orderBy('created_at', 'desc')->get();
        
        // Récupérer les utilisateurs visibles pour les filtres
        if ($user->isSuperAdmin()) {
            $users = User::all();
        } elseif ($user->isAdmin()) {
            $users = User::where('id', $user->id)
                        ->orWhere('admin_id', $user->id)
                        ->get();
        } elseif ($user->isGerant()) {
            $users = User::where('admin_id', $user->admin_id)->get();
        } else {
            $users = collect([$user]);
        }

        // Statistiques pour le dashboard
        $stats = [
            'total_ventes' => $ventes->count(),
            'chiffre_affaires' => $ventes->sum('montant_total'),
            'vente_moyenne' => $ventes->count() > 0 ? $ventes->sum('montant_total') / $ventes->count() : 0,
        ];

        return view('vente.index', compact('ventes', 'users', 'stats'));
    }

    public function create()
    {
        $user = Auth::user();
        
        // CORRECTION : Vérifier que la caisse est ouverte POUR TOUS LES UTILISATEURS
        $caisse_ouverte = Caisse::where('user_id', $user->id)
                               ->where('statut', 'ouverte')
                               ->exists();

        if (!$caisse_ouverte) {
            return redirect()->route('ventes.index')
                           ->with('error', 'Veuillez ouvrir votre caisse avant de faire une vente.');
        }

        // Récupérer les produits visibles par l'utilisateur
        $products = Product::visibleTo($user)->where('quantite', '>', 0)->get();
        
        return view('vente.create', compact('products'));
    }

    /**
     * Génération de numéro de vente garanti unique
     */
    private function generateUniqueNumeroVente()
    {
        // Utilisation de uniqid() qui est GARANTI UNIQUE
        $uniquePart = uniqid('', true); // Génère quelque chose comme: 6789abc123def
        $cleanUnique = substr(str_replace('.', '', $uniquePart), -8); // Prend les 8 derniers caractères
        
        return 'V' . date('YmdHis') . $cleanUnique;
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        // CORRECTION : Vérifier que la caisse est ouverte POUR TOUS LES UTILISATEURS
        $caisse = Caisse::where('user_id', $user->id)
                       ->where('statut', 'ouverte')
                       ->first();

        if (!$caisse) {
            return back()->with('error', 'Veuillez ouvrir votre caisse avant de faire une vente.')
                        ->withInput();
        }

        // Validation des données
        $request->validate([
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantite' => 'required|integer|min:1',
            'cash_client' => 'required|numeric|min:0',
            'monnaie' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        // Calculer le montant total de la vente
        $montant_total_calcule = 0;
        $products_data = [];

        foreach ($request->products as $productData) {
            $product = Product::with('user')->find($productData['product_id']);
            
            if ($product) {
                // Vérification des permissions
                if (!$product->canBeSoldBy($user)) {
                    return back()->with('error', "Accès non autorisé au produit: {$product->designation}")
                                ->withInput();
                }

                $quantite = $productData['quantite'];
                
                // Vérifier le stock
                if ($product->quantite < $quantite) {
                    return back()->with('error', "Stock insuffisant pour {$product->designation}! Stock disponible: {$product->quantite}")
                                ->withInput();
                }

                $montant_ligne = $product->prix * $quantite;
                $montant_total_calcule += $montant_ligne;

                $products_data[] = [
                    'product' => $product,
                    'quantite' => $quantite,
                    'montant_ligne' => $montant_ligne
                ];
            }
        }

        // Vérifier que le cash client est suffisant
        if ($request->cash_client < $montant_total_calcule) {
            return back()->with('error', 'Le montant remis par le client est insuffisant.')
                        ->withInput();
        }

        // Vérifier que la monnaie correspond
        $monnaie_calculee = $request->cash_client - $montant_total_calcule;
        if ($request->monnaie != $monnaie_calculee) {
            return back()->with('error', 'Erreur dans le calcul de la monnaie. Veuillez vérifier les montants.')
                        ->withInput();
        }

        DB::beginTransaction();

        try {
            // CORRECTION : Utiliser la nouvelle méthode de génération unique
            $numero_vente = $this->generateUniqueNumeroVente();

            // Créer la vente
            $vente = Vente::create([
                'user_id' => $user->id,
                'caisse_id' => $caisse->id ?? null,
                'numero_vente' => $numero_vente,
                'montant_total' => $montant_total_calcule,
                'cash_client' => $request->cash_client,
                'monnaie_rendue' => $request->monnaie,
                'notes' => $request->notes
            ]);

            // Traiter chaque produit
            foreach ($products_data as $product_data) {
                $product = $product_data['product'];
                $quantite = $product_data['quantite'];

                // Créer la ligne de vente
                LigneVente::create([
                    'vente_id' => $vente->id,
                    'product_id' => $product->id,
                    'quantite' => $quantite,
                    'prix_unitaire' => $product->prix,
                    'montant_total' => $product_data['montant_ligne']
                ]);

                // Mettre à jour le stock
                $product->decrement('quantite', $quantite);
            }

            // Mettre à jour la caisse
            if ($caisse) {
                $caisse->increment('solde_actuel', $montant_total_calcule);
                $caisse->increment('total_ventes', $montant_total_calcule);
                $caisse->increment('nombre_ventes', 1);
            }

            DB::commit();

            return redirect()->route('ventes.index')
                           ->with('success', 'Vente enregistrée avec succès! Montant total: ' . number_format($montant_total_calcule, 0, ',', ' ') . ' FCFA - Monnaie rendue: ' . number_format($request->monnaie, 0, ',', ' ') . ' FCFA');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Erreur lors de l\'enregistrement de la vente: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'products' => $request->products,
                'cash_client' => $request->cash_client,
                'numero_vente_tentative' => $numero_vente ?? 'non_généré'
            ]);
            
            return back()->with('error', 'Erreur lors de l\'enregistrement: ' . $e->getMessage())
                        ->withInput();
        }
    }

    public function show($id)
    {
        $user = Auth::user();
        
        // Charger la vente avec TOUTES les relations nécessaires
        $vente = Vente::with(['user', 'lignesVente.product', 'caisse'])->findOrFail($id);
        
        // LOGIQUE SIMPLE ET ÉPROUVÉE
        if ($user->isAdmin() || $user->isGerant()) {
            // Admin et Gérant peuvent voir toutes les ventes
            return view('vente.show', compact('vente'));
        }
        
        // Caissier ne peut voir que ses propres ventes
        if ($vente->user_id === $user->id) {
            return view('vente.show', compact('vente'));
        }
        
        abort(403, 'ACCÈS NON AUTORISÉ À CETTE VENTE.');
    }

    public function edit($id)
    {
        $user = Auth::user();
        $vente = Vente::with(['user', 'lignesVente.product', 'caisse'])->findOrFail($id);
        
        // Vérifications d'accès
        if ($vente->user_id !== $user->id) {
            abort(403, 'Non autorisé à modifier cette vente.');
        }
        
        if (!$vente->peutEtreModifiee()) {
            abort(403, "Cette vente ne peut plus être modifiée (délai de 24h dépassé).");
        }
        
        $products = Product::visibleTo($user)->get();
        return view('vente.edit', compact('vente', 'products'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $vente = Vente::findOrFail($id);
        
        // CORRECTION : Seul le créateur de la vente peut la modifier
        if ($vente->user_id !== $user->id) {
            abort(403, 'Non autorisé à modifier cette vente. Vous ne pouvez modifier que vos propres ventes.');
        }
        
        // DEBUG
        \Log::info('=== DEBUG UPDATE VENTE ===');
        \Log::info('Utilisateur:', [
            'id' => $user->id,
            'fonction' => $user->fonction,
            'isAdmin' => $user->isAdmin(),
            'isGerant' => $user->isGerant(), 
            'isCaissier' => $user->isCaissier()
        ]);
        \Log::info('Vente:', [
            'id' => $vente->id,
            'user_id' => $vente->user_id,
            'peutEtreModifiee' => $vente->peutEtreModifiee()
        ]);
        
        // Vérifier si la vente peut encore être modifiée (24h)
        if (!$vente->peutEtreModifiee()) {
            $hours = $vente->created_at->diffInHours(now());
            abort(403, "Cette vente ne peut plus être modifiée. Créée il y a {$hours} heures (délai de 24h).");
        }

        $request->validate([
            'lignes' => 'required|array|min:1',
            'lignes.*.ligne_id' => 'required|exists:ligne_ventes,id',
            'lignes.*.quantite' => 'required|integer|min:1',
            'cash_client' => 'required|numeric|min:0',
            'monnaie' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        // Vérifier que le cash client est suffisant pour le nouveau montant
        $nouveau_montant_total = 0;
        $lignes_data = [];

        foreach ($request->lignes as $ligneData) {
            $ligne = LigneVente::find($ligneData['ligne_id']);
            if ($ligne && $ligne->vente_id === $vente->id) {
                $product = $ligne->product;
                if (!$product->canBeSoldBy($user)) {
                    return back()->with('error', "Accès non autorisé au produit: {$product->designation}")
                                ->withInput();
                }

                $nouveau_montant_ligne = $ligne->prix_unitaire * $ligneData['quantite'];
                $nouveau_montant_total += $nouveau_montant_ligne;

                $lignes_data[] = [
                    'ligne' => $ligne,
                    'nouvelle_quantite' => $ligneData['quantite'],
                    'ancienne_quantite' => $ligne->quantite,
                    'produit' => $product,
                    'nouveau_montant' => $nouveau_montant_ligne
                ];
            }
        }

        if ($request->cash_client < $nouveau_montant_total) {
            return back()->with('error', 'Le montant remis par le client est insuffisant pour le nouveau total.')
                        ->withInput();
        }

        // Vérifier que la monnaie correspond
        $monnaie_calculee = $request->cash_client - $nouveau_montant_total;
        if ($request->monnaie != $monnaie_calculee) {
            return back()->with('error', 'Erreur dans le calcul de la monnaie. Veuillez vérifier les montants.')
                        ->withInput();
        }

        DB::beginTransaction();

        try {
            $ancien_montant_total = $vente->montant_total;

            // Mettre à jour chaque ligne de vente
            foreach ($lignes_data as $ligne_data) {
                $ligne = $ligne_data['ligne'];
                $produit = $ligne_data['produit'];
                $ancienne_quantite = $ligne_data['ancienne_quantite'];
                $nouvelle_quantite = $ligne_data['nouvelle_quantite'];

                // Calculer la différence de quantité
                $difference_quantite = $nouvelle_quantite - $ancienne_quantite;

                // Vérifier le stock si on augmente la quantité
                if ($difference_quantite > 0 && $produit->quantite < $difference_quantite) {
                    throw new \Exception("Stock insuffisant pour {$produit->designation}. Stock disponible: {$produit->quantite}");
                }

                // Mettre à jour le stock
                if ($difference_quantite != 0) {
                    $produit->decrement('quantite', $difference_quantite);
                }

                // Mettre à jour la ligne
                $ligne->update([
                    'quantite' => $nouvelle_quantite,
                    'montant_total' => $ligne_data['nouveau_montant']
                ]);
            }

            // Mettre à jour la vente
            $vente->update([
                'montant_total' => $nouveau_montant_total,
                'cash_client' => $request->cash_client,
                'monnaie_rendue' => $request->monnaie,
                'notes' => $request->notes
            ]);

            // Mettre à jour la caisse si nécessaire
            if ($vente->caisse) {
                $difference = $nouveau_montant_total - $ancien_montant_total;
                
                if ($difference != 0) {
                    $vente->caisse->increment('solde_actuel', $difference);
                    $vente->caisse->increment('total_ventes', $difference);
                }
            }

            DB::commit();

            return redirect()->route('ventes.show', $vente)
                           ->with('success', 'Vente modifiée avec succès! Nouveau montant: ' . number_format($nouveau_montant_total, 0, ',', ' ') . ' FCFA - Monnaie rendue: ' . number_format($request->monnaie, 0, ',', ' ') . ' FCFA');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur lors de la modification de la vente: ' . $e->getMessage(), [
                'vente_id' => $vente->id,
                'user_id' => $user->id
            ]);
            
            return back()->with('error', 'Erreur lors de la modification: ' . $e->getMessage())
                        ->withInput();
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $vente = Vente::findOrFail($id);
        
        // CORRECTION : Seul le créateur de la vente peut la supprimer
        if ($vente->user_id !== $user->id) {
            abort(403, 'Non autorisé à supprimer cette vente. Vous ne pouvez supprimer que vos propres ventes.');
        }
        
        if (!$vente->peutEtreModifiee()) {
            abort(403, 'Non autorisé à supprimer cette vente. Seul le créateur de la vente peut la supprimer pendant 24h.');
        }

        DB::beginTransaction();

        try {
            $montant_total = $vente->montant_total;

            // Restaurer le stock
            foreach ($vente->lignesVente as $ligne) {
                $ligne->product->increment('quantite', $ligne->quantite);
            }

            // Mettre à jour la caisse
            if ($vente->caisse) {
                $vente->caisse->decrement('solde_actuel', $montant_total);
                $vente->caisse->decrement('total_ventes', $montant_total);
                $vente->caisse->decrement('nombre_ventes', 1);
            }

            // Supprimer la vente (les lignes seront supprimées automatiquement par CASCADE)
            $vente->delete();

            DB::commit();

            return redirect()->route('ventes.index')
                           ->with('success', 'Vente supprimée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur lors de la suppression de la vente: ' . $e->getMessage(), [
                'vente_id' => $vente->id,
                'user_id' => $user->id
            ]);
            
            return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    public function printReceipt($id)
    {
        $user = Auth::user();
        $vente = Vente::findOrFail($id);
        
        // LOGIQUE SIMPLE ET ÉPROUVÉE
        if ($user->isAdmin() || $user->isGerant() || $vente->user_id === $user->id) {
            try {
                // Charger les relations nécessaires
                $vente->load(['user', 'lignesVente.product']);
                
                // Vérifications de sécurité
                if (!$vente->exists) {
                    abort(404, 'Vente non trouvée');
                }

                // Assurer que created_at n'est pas null
                if (!$vente->created_at) {
                    $vente->created_at = now();
                }

                return view('vente.receipt', compact('vente'));

            } catch (\Exception $e) {
                // Log l'erreur pour le débogage
                \Log::error('Erreur génération reçu: ' . $e->getMessage(), [
                    'vente_id' => $vente->id ?? 'null',
                    'user_id' => Auth::id()
                ]);
                
                return redirect()->route('ventes.index')
                               ->with('error', 'Erreur lors de la génération du reçu: ' . $e->getMessage());
            }
        }
        
        abort(403, 'Accès non autorisé à ce reçu');
    }

    /**
     * Méthode pour voir l'historique complet des ventes (sans filtre de caisse)
     */
    public function historique(Request $request)
    {
        $user = Auth::user();
        
        // Vérification des permissions
        if (!$user->isAdmin() && !$user->isGerant() && !$user->isCaissier()) {
            abort(403, 'Accès non autorisé à l\'historique des ventes.');
        }
        
        // Construire la requête de base
        $query = Vente::with(['user', 'lignesVente.product', 'caisse']);

        // Appliquer les filtres de visibilité selon le rôle
        if ($user->isAdmin()) {
            // Admin voit ses ventes + celles de ses sub-users
            $query->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('user', function($subQ) use ($user) {
                      $subQ->where('admin_id', $user->id);
                  });
            });
        } elseif ($user->isGerant()) {
            // Gérant voit les ventes de son établissement
            $query->whereHas('user', function($q) use ($user) {
                $q->where('admin_id', $user->admin_id);
            });
        } else {
            // Caissier ne voit que ses propres ventes
            $query->where('user_id', $user->id);
        }

        // Filtrage par date
        if ($request->has('date_debut') && $request->date_debut) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        } else {
            // Par défaut, afficher les 30 derniers jours
            $query->where('created_at', '>=', Carbon::now()->subDays(30));
        }
        
        if ($request->has('date_fin') && $request->date_fin) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        // Filtrage par utilisateur (seulement pour Admin/Gérant)
        if ($request->has('user_id') && $request->user_id && ($user->isAdmin() || $user->isGerant())) {
            $query->where('user_id', $request->user_id);
        }

        // Filtrage par caisse (optionnel)
        if ($request->has('caisse_id') && $request->caisse_id) {
            $query->where('caisse_id', $request->caisse_id);
        }

        $ventes = $query->orderBy('created_at', 'desc')->paginate(20);

        // Récupérer les données pour les filtres
        $users = $this->getUsersForFilters($user);
        $caisses = $this->getCaissesForFilters($user);

        // Statistiques
        $stats = [
            'total_ventes' => $ventes->total(),
            'chiffre_affaires' => $ventes->sum('montant_total'),
            'vente_moyenne' => $ventes->count() > 0 ? $ventes->sum('montant_total') / $ventes->count() : 0,
        ];

        return view('vente.historique', compact('ventes', 'users', 'caisses', 'stats'));
    }

    /**
     * Télécharger les ventes en Excel/CSV
     */
    public function downloadVentes(Request $request)
    {
        $user = Auth::user();
        
        // Vérifier les permissions
        if (!$user->isAdmin() && !$user->isGerant()) {
            abort(403, 'Accès non autorisé.');
        }

        $request->validate([
            'type_export' => 'required|in:ventes_detaillees,ventes_simplifiees',
            'date_debut_export' => 'nullable|date',
            'date_fin_export' => 'nullable|date|after_or_equal:date_debut_export',
            'user_id_export' => 'nullable|exists:users,id'
        ]);

        // Construire la requête de base
        $query = Vente::with(['user', 'lignesVente.product', 'caisse']);

        // Appliquer les filtres de visibilité selon le rôle
        if ($user->isAdmin()) {
            $query->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('user', function($subQ) use ($user) {
                      $subQ->where('admin_id', $user->id);
                  });
            });
        } elseif ($user->isGerant()) {
            $query->whereHas('user', function($q) use ($user) {
                $q->where('admin_id', $user->admin_id);
            });
        }

        // Appliquer les filtres
        if ($request->filled('date_debut_export')) {
            $query->whereDate('created_at', '>=', $request->date_debut_export);
        }
        
        if ($request->filled('date_fin_export')) {
            $query->whereDate('created_at', '<=', $request->date_fin_export);
        }

        // Filtrage par utilisateur
        if ($request->filled('user_id_export')) {
            $query->where('user_id', $request->user_id_export);
        }

        $ventes = $query->orderBy('created_at', 'desc')->get();

        // Générer le fichier selon le type
        if ($request->type_export === 'ventes_detaillees') {
            return $this->generateVentesDetailleesExport($ventes, $request);
        } else {
            return $this->generateVentesSimplifieesExport($ventes, $request);
        }
    }

    /**
     * Génère l'export des ventes détaillées
     */
    private function generateVentesDetailleesExport($ventes, Request $request)
    {
        $fileName = 'ventes_detaillees_' . Carbon::now()->format('Y-m-d_H-i') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function() use ($ventes) {
            $file = fopen('php://output', 'w');
            
            // En-tête UTF-8 BOM pour Excel
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            
            // En-têtes du CSV
            fputcsv($file, [
                'N° Vente',
                'Date Création',
                'Utilisateur',
                'Rôle',
                'Caisse',
                'Montant Total (FCFA)',
                'Cash Client (FCFA)',
                'Monnaie Rendue (FCFA)',
                'Nombre Produits',
                'Produits Détail',
                'Notes'
            ], ';');

            foreach ($ventes as $vente) {
                // Détail des produits
                $produitsDetail = [];
                foreach ($vente->lignesVente as $ligne) {
                    $produitsDetail[] = $ligne->product->designation . ' (x' . $ligne->quantite . ' @ ' . number_format($ligne->prix_unitaire, 0, ',', ' ') . ' FCFA) = ' . number_format($ligne->montant_total, 0, ',', ' ') . ' FCFA';
                }
                $produitsString = implode(' | ', $produitsDetail);

                fputcsv($file, [
                    $vente->numero_vente ?? 'VENTE-' . $vente->id,
                    $vente->created_at->format('d/m/Y H:i'),
                    $vente->user ? $vente->user->prenom . ' ' . $vente->user->name : 'N/A',
                    $vente->user ? ucfirst($vente->user->fonction) : 'N/A',
                    $vente->caisse ? 'Caisse ' . $vente->caisse->id . ' - ' . $vente->caisse->user->prenom : 'N/A',
                    number_format($vente->montant_total, 0, ',', ' '),
                    number_format($vente->cash_client, 0, ',', ' '),
                    number_format($vente->monnaie_rendue, 0, ',', ' '),
                    $vente->lignesVente->count(),
                    $produitsString,
                    $vente->notes ?? ''
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Génère l'export des ventes simplifiées (format pour analyse)
     */
    private function generateVentesSimplifieesExport($ventes, Request $request)
    {
        $fileName = 'ventes_simplifiees_' . Carbon::now()->format('Y-m-d_H-i') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function() use ($ventes) {
            $file = fopen('php://output', 'w');
            
            // En-tête UTF-8 BOM pour Excel
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            
            // En-têtes du CSV
            fputcsv($file, [
                'Date',
                'N° Vente',
                'Utilisateur',
                'Rôle',
                'Montant (FCFA)',
                'Cash Client (FCFA)',
                'Monnaie Rendue (FCFA)',
                'Nombre Produits'
            ], ';');

            foreach ($ventes as $vente) {
                fputcsv($file, [
                    $vente->created_at->format('d/m/Y'),
                    $vente->numero_vente ?? 'VENTE-' . $vente->id,
                    $vente->user ? $vente->user->prenom . ' ' . $vente->user->name : 'N/A',
                    $vente->user ? ucfirst($vente->user->fonction) : 'N/A',
                    number_format($vente->montant_total, 0, ',', ' '),
                    number_format($vente->cash_client, 0, ',', ' '),
                    number_format($vente->monnaie_rendue, 0, ',', ' '),
                    $vente->lignesVente->count()
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Affiche le formulaire d'export
     */
    public function showExportForm()
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isGerant()) {
            abort(403, 'Accès non autorisé.');
        }

        // Récupérer les utilisateurs pour les filtres
        $users = $this->getUsersForFilters($user);

        return view('vente.export', compact('users'));
    }

    /**
     * Récupère les utilisateurs pour les filtres selon les permissions
     */
    private function getUsersForFilters(User $user)
    {
        if ($user->isSuperAdmin()) {
            return User::whereIn('fonction', ['admin', 'gerant', 'caissier'])->get();
        }

        if ($user->isAdmin()) {
            // Admin voit ses sub-users + lui-même
            return User::where(function($query) use ($user) {
                $query->where('id', $user->id)
                      ->orWhere('admin_id', $user->id);
            })->get();
        }

        if ($user->isGerant()) {
            // Gérant voit les caissiers de son admin + lui-même + son admin
            return User::where(function($query) use ($user) {
                $query->where('id', $user->id)
                      ->orWhere('id', $user->admin_id)
                      ->orWhere(function($q) use ($user) {
                          $q->where('admin_id', $user->admin_id)
                            ->where('fonction', 'caissier');
                      });
            })->get();
        }

        // Caissier ne voit que lui-même
        return collect([$user]);
    }

    /**
     * Récupère les caisses pour les filtres selon les permissions
     */
    private function getCaissesForFilters(User $user)
    {
        $query = Caisse::with('user');

        if ($user->isSuperAdmin()) {
            return $query->get();
        }

        if ($user->isAdmin()) {
            return $query->whereHas('user', function($q) use ($user) {
                $q->where('id', $user->id)
                  ->orWhere('admin_id', $user->id);
            })->get();
        }

        if ($user->isGerant()) {
            return $query->whereHas('user', function($q) use ($user) {
                $q->where('admin_id', $user->admin_id);
            })->get();
        }

        // Caissier ne voit que ses propres caisses
        return $query->where('user_id', $user->id)->get();
    }
}