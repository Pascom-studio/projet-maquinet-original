<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\TransactionCaisse;
use App\Models\User;
use App\Models\Caisse; 
use App\Models\Commande;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isGerant()) {
            abort(403, 'Accès non autorisé.');
        }

        $user = Auth::user();
        $query = Audit::with('user');

        // FILTRE IMPORTANT : Appliquer le scope selon la hiérarchie utilisateur
        $query->where(function($q) use ($user) {
            if ($user->isSuperAdmin()) {
                // SuperAdmin voit tout
                return;
            }

            if ($user->isAdmin()) {
                // Admin voit ses audits + ceux de ses sub-users
                $q->where('user_id', $user->id)
                  ->orWhereHas('user', function($subQuery) use ($user) {
                      $subQuery->where('admin_id', $user->id);
                  });
            }

            if ($user->isGerant()) {
                // Gérant voit les audits de son admin parent
                $q->whereHas('user', function($subQuery) use ($user) {
                    $subQuery->where('admin_id', $user->admin_id);
                });
            }
        });

        // Filtrage par date
        if ($request->has('date_debut') && $request->date_debut) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }
        
        if ($request->has('date_fin') && $request->date_fin) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        // Filtrage par action
        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }

        // Filtrage par table
        if ($request->has('table_name') && $request->table_name) {
            $query->where('table_name', $request->table_name);
        }

        $audits = $query->orderBy('created_at', 'desc')->paginate(50);

        // Pour les filtres, limiter aussi aux tables et actions visibles
        $tables = Audit::whereIn('id', $query->pluck('id'))->distinct()->pluck('table_name');
        $actions = Audit::whereIn('id', $query->pluck('id'))->distinct()->pluck('action');

        return view('audit.index', compact('audits', 'tables', 'actions'));
    }

    /**
     * AUDIT FINANCIER - CORRIGÉ POUR SÉPARER PAR ADMIN
     */
    public function financier(Request $request)
    {
        $user = Auth::user();
        
        // Seul l'admin a accès à l'audit financier
        if (!$user->isAdmin()) {
            abort(403, 'Accès réservé aux administrateurs.');
        }

        // CORRECTION : Récupérer les transactions UNIQUEMENT pour l'admin connecté et ses utilisateurs
        $query = TransactionCaisse::with(['caisse.user', 'user'])
                    ->where(function($q) use ($user) {
                        // L'admin voit ses propres transactions
                        $q->where('user_id', $user->id)
                          // ET les transactions de ses utilisateurs
                          ->orWhereHas('user', function($subQuery) use ($user) {
                              $subQuery->where('admin_id', $user->id);
                          });
                    })
                    ->orderBy('created_at', 'desc');

        // Filtrage par date
        if ($request->has('date_debut') && $request->date_debut) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }
        
        if ($request->has('date_fin') && $request->date_fin) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        // Filtrage par type de transaction
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filtrage par utilisateur (uniquement les utilisateurs de cet admin)
        if ($request->has('user_id') && $request->user_id) {
            // Vérifier que l'utilisateur sélectionné appartient bien à cet admin
            $selectedUser = User::where('id', $request->user_id)
                              ->where(function($q) use ($user) {
                                  $q->where('id', $user->id)
                                    ->orWhere('admin_id', $user->id);
                              })
                              ->first();
            
            if ($selectedUser) {
                $query->where('user_id', $request->user_id);
            }
        }

        $transactions = $query->paginate(50);

        // Statistiques financières - CORRIGÉ POUR ADMIN SEULEMENT
        $stats = $this->getFinancialStats($request);

        // CORRECTION : Données pour les filtres - uniquement les utilisateurs de cet admin
        $types = ['depense', 'approvisionnement', 'retrait'];
        $users = User::where('admin_id', $user->id)
                    ->orWhere('id', $user->id)
                    ->get(['id', 'prenom', 'name', 'fonction']);

        return view('audit.financier', compact('transactions', 'stats', 'types', 'users'));
    }

    /**
     * Calcule les statistiques financières - VERSION CORRIGÉE
     */
    private function getFinancialStats(Request $request)
    {
        $user = Auth::user();
        $baseQuery = TransactionCaisse::query();

        // CORRECTION : Appliquer la même restriction que la requête principale
        $baseQuery->where(function($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhereHas('user', function($subQuery) use ($user) {
                  $subQuery->where('admin_id', $user->id);
              });
        });

        // Appliquer les mêmes filtres que la requête principale
        if ($request->has('date_debut') && $request->date_debut) {
            $baseQuery->whereDate('created_at', '>=', $request->date_debut);
        }
        
        if ($request->has('date_fin') && $request->date_fin) {
            $baseQuery->whereDate('created_at', '<=', $request->date_fin);
        }

        if ($request->has('user_id') && $request->user_id) {
            // Vérifier que l'utilisateur sélectionné appartient bien à cet admin
            $selectedUser = User::where('id', $request->user_id)
                              ->where(function($q) use ($user) {
                                  $q->where('id', $user->id)
                                    ->orWhere('admin_id', $user->id);
                              })
                              ->first();
            
            if ($selectedUser) {
                $baseQuery->where('user_id', $request->user_id);
            }
        }

        // Calculs des totaux - CORRECTION : Uniquement pour cet admin et ses utilisateurs
        $total_depenses = (clone $baseQuery)->where('type', 'depense')->sum('montant');
        $total_approvisionnements = (clone $baseQuery)->where('type', 'approvisionnement')->sum('montant');
        $total_retraits = (clone $baseQuery)->where('type', 'retrait')->sum('montant');

        // CORRECTION : Récupérer UNIQUEMENT la caisse OUVERTE de l'admin connecté
        $caisseAdmin = Caisse::where('user_id', $user->id)
                            ->where('statut', 'ouverte')
                            ->first();
        
        // Si pas de caisse ouverte, prendre la dernière caisse
        if (!$caisseAdmin) {
            $caisseAdmin = Caisse::where('user_id', $user->id)
                                ->orderBy('created_at', 'desc')
                                ->first();
        }

        $solde_admin = $caisseAdmin ? $caisseAdmin->solde_actuel : 0;
        $solde_ouverture = $caisseAdmin ? $caisseAdmin->solde_ouverture : 0;
        $solde_fermeture = $caisseAdmin ? $caisseAdmin->solde_fermeture : 0;

        // CORRECTION : Calculer correctement le solde net
        // Solde Net = Solde Admin + Total des caissiers + Total des gérants
        $total_caissiers = $this->getTotalCaissiers($user);
        $total_gerants = $this->getTotalGerants($user);
        $solde_net = $solde_admin + $total_caissiers + $total_gerants;

        return [
            'total_depenses' => $total_depenses,
            'total_approvisionnements' => $total_approvisionnements,
            'total_retraits' => $total_retraits,
            'total_transactions' => (clone $baseQuery)->count(),
            'solde_net' => $solde_net,
            'solde_admin' => $solde_admin,
            'total_caissiers' => $total_caissiers,
            'total_gerants' => $total_gerants,
            'nombre_caissiers' => $this->getNombreCaissiers($user),
            'nombre_gerants' => $this->getNombreGerants($user),
            'solde_ouverture' => $solde_ouverture,
            'solde_fermeture' => $solde_fermeture
        ];
    }

    /**
     * Calcule le total des soldes de tous les caissiers de l'admin
     */
    private function getTotalCaissiers(User $admin)
    {
        try {
            // Récupérer tous les caissiers de cet admin avec leurs caisses
            $caissiers = User::where('admin_id', $admin->id)
                            ->where('fonction', 'caissier')
                            ->with('caisse')
                            ->get();

            $total = 0;
            foreach ($caissiers as $caissier) {
                if ($caissier->caisse) {
                    $total += $caissier->caisse->solde_actuel;
                }
            }

            return $total;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Calcule le total des soldes de tous les gérants de l'admin
     */
    private function getTotalGerants(User $admin)
    {
        try {
            // Récupérer tous les gérants de cet admin avec leurs caisses
            $gerants = User::where('admin_id', $admin->id)
                          ->where('fonction', 'gerant')
                          ->with('caisse')
                          ->get();

            $total = 0;
            foreach ($gerants as $gerant) {
                if ($gerant->caisse) {
                    $total += $gerant->caisse->solde_actuel;
                }
            }

            return $total;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Retourne le nombre de caissiers de l'admin
     */
    private function getNombreCaissiers(User $admin)
    {
        return User::where('admin_id', $admin->id)
                  ->where('fonction', 'caissier')
                  ->count();
    }

    /**
     * Retourne le nombre de gérants de l'admin
     */
    private function getNombreGerants(User $admin)
    {
        return User::where('admin_id', $admin->id)
                  ->where('fonction', 'gerant')
                  ->count();
    }

    public function show($id)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isGerant()) {
            abort(403, 'Accès non autorisé.');
        }

        $user = Auth::user();
        $audit = Audit::with('user')->findOrFail($id);

        // Vérifier les permissions pour cet audit spécifique
        if (!$this->canViewAudit($user, $audit)) {
            abort(403, 'Accès non autorisé à cet audit.');
        }

        return view('audit.show', compact('audit'));
    }

    /**
     * Vérifie si l'utilisateur peut voir cet audit
     */
    private function canViewAudit(User $user, Audit $audit)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            // Admin peut voir ses audits + ceux de ses sub-users
            return $audit->user_id === $user->id || 
                   ($audit->user && $audit->user->admin_id === $user->id);
        }

        if ($user->isGerant()) {
            // Gérant peut voir les audits de son admin parent
            return $audit->user && $audit->user->admin_id === $user->admin_id;
        }

        return false;
    }

    /**
     * MÉTHODE POUR FERMER LA CAISSE ADMIN - LOGIQUE CORRIGÉE
     */
    public function fermerCaisse(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            abort(403, 'Accès réservé aux administrateurs.');
        }

        DB::beginTransaction();

        try {
            // CORRECTION : Récupérer UNIQUEMENT la caisse de l'admin connecté
            $caisseAdmin = Caisse::where('user_id', $user->id)->first();
            
            if (!$caisseAdmin) {
                return back()->with('error', 'Caisse admin non trouvée.');
            }

            // CORRECTION : Calculer les transactions UNIQUEMENT pour cet admin
            $total_retraits = TransactionCaisse::where('user_id', $user->id)
                ->where('type', 'retrait')
                ->where('est_traite', false)
                ->sum('montant');

            $total_depenses = TransactionCaisse::where('user_id', $user->id)
                ->where('type', 'depense')
                ->where('est_traite', false)
                ->sum('montant');

            // CORRECTION : SOLDE DE FERMETURE = solde_actuel
            $solde_fermeture = $caisseAdmin->solde_actuel;

            // Fermer la caisse avec le solde de fermeture
            $caisseAdmin->update([
                'solde_fermeture' => $solde_fermeture,
                'solde_ouverture' => $solde_fermeture,
                'solde_actuel' => $solde_fermeture,
                'total_entrees' => 0,
                'total_sorties' => 0,
                'date_fermeture' => now(),
                'statut' => 'fermee'
            ]);

            // CORRECTION : Marquer UNIQUEMENT les transactions de cet admin comme "traitées"
            TransactionCaisse::where('user_id', $user->id)
                ->where('est_traite', false)
                ->update(['est_traite' => true]);

            DB::commit();

            return redirect()->route('audit.financier')
                           ->with('success', 'Caisse fermée avec succès! Solde de fermeture: ' . $solde_fermeture);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la fermeture: ' . $e->getMessage());
        }
    }

    /**
     * MÉTHODE POUR OUVRIR LA CAISSE AVEC LE SOLDE DE DÉMARRAGE - LOGIQUE CORRIGÉE
     */
    public function ouvrirCaisse(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            abort(403, 'Accès réservé aux administrateurs.');
        }

        DB::beginTransaction();

        try {
            // CORRECTION : Récupérer UNIQUEMENT la caisse de l'admin connecté
            $caisseAdmin = Caisse::where('user_id', $user->id)->first();
            
            if (!$caisseAdmin) {
                // Créer une nouvelle caisse si elle n'existe pas
                $caisseAdmin = Caisse::create([
                    'user_id' => $user->id,
                    'solde_ouverture' => $request->solde_demarrage ?? 0,
                    'solde_actuel' => $request->solde_demarrage ?? 0,
                    'solde_fermeture' => 0,
                    'total_entrees' => 0,
                    'total_sorties' => 0,
                    'date_ouverture' => now(),
                    'statut' => 'ouverte'
                ]);
            } else {
                // Ouvrir la caisse avec le solde d'ouverture
                $caisseAdmin->update([
                    'solde_actuel' => $caisseAdmin->solde_ouverture,
                    'total_entrees' => 0,
                    'total_sorties' => 0,
                    'date_ouverture' => now(),
                    'statut' => 'ouverte'
                ]);
            }

            DB::commit();

            return redirect()->route('audit.financier')
                           ->with('success', 'Caisse ouverte avec succès! Solde de démarrage: ' . $caisseAdmin->solde_ouverture);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'ouverture: ' . $e->getMessage());
        }
    }

    /**
     * PERFORMANCE DES HÔTESSES - ACCÈS CORRIGÉ POUR MANAGERS
     */
    public function performanceHotesses(Request $request)
    {
        $user = Auth::user();
        
        // CORRECTION : Autoriser l'accès aux managers, gérants ET aux admins
        if (!$user->isAdmin() && !$user->isManager() && !$user->isGerant()) {
            abort(403, 'Accès non autorisé. Votre rôle (' . $user->fonction . ') ne permet pas d\'accéder à cette page.');
        }

        // Récupérer les hôtesses selon les permissions
        if ($user->isAdmin()) {
            $hotesses = User::where('fonction', 'hotesse')
                          ->where('admin_id', $user->id)
                          ->get();
        } else {
            // CORRECTION : Manager/Gérant voit les hôtesses de son établissement
            $hotesses = User::where('fonction', 'hotesse')
                          ->where('admin_id', $user->admin_id)
                          ->get();
        }

        $hotessePerformance = null;
        $topPerformances = $this->getTopPerformances($user);

        // Si une hôtesse est sélectionnée pour la recherche
        if ($request->has('hotesse_id') && $request->hotesse_id) {
            $hotessePerformance = $this->getHotessePerformance($request->hotesse_id, $request);
        }

        return view('audit.performance-hotesses', compact(
            'hotesses', 
            'hotessePerformance', 
            'topPerformances'
        ));
    }

    /**
     * Calcule la performance d'une hôtesse spécifique - VERSION CORRIGÉE
     */
    private function getHotessePerformance($hotesseId, Request $request)
    {
        $hotesse = User::findOrFail($hotesseId);

        // Vérifier que l'hôtesse appartient bien à l'admin/manager connecté
        $currentUser = Auth::user();
        
        if ($currentUser->isAdmin() && $hotesse->admin_id !== $currentUser->id) {
            abort(403, 'Accès non autorisé à cette hôtesse.');
        }
        
        // CORRECTION : Vérification pour le manager/gérant
        if (($currentUser->isManager() || $currentUser->isGerant()) && $hotesse->admin_id !== $currentUser->admin_id) {
            abort(403, 'Accès non autorisé à cette hôtesse.');
        }

        // CORRECTION : Utiliser hotesse_id au lieu de user_id
        $query = Commande::where('hotesse_id', $hotesseId);

        // Filtrage par date
        if ($request->has('date_debut') && $request->date_debut) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }
        
        if ($request->has('date_fin') && $request->date_fin) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $commandes = $query->with(['produits.produit', 'table'])->get();

        // CORRECTION : Utiliser montant au lieu de montant_total
        $totalCommandes = $commandes->count();
        $totalVentes = $commandes->sum('montant');
        $commandesSoldees = $commandes->where('statut', 'soldée')->count();
        $commandesEnCours = $commandes->where('statut', 'en cours')->count();

        // Tables affectées
        $tablesAffectees = Table::where('user_id', $hotesseId)
                               ->where(function($q) use ($request) {
                                   if ($request->has('date_debut') && $request->date_debut) {
                                       $q->whereDate('created_at', '>=', $request->date_debut);
                                   }
                                   if ($request->has('date_fin') && $request->date_fin) {
                                       $q->whereDate('created_at', '<=', $request->date_fin);
                                   }
                               })
                               ->count();

        // Produits les plus vendus - CORRECTION
        $produitsVendus = [];
        foreach ($commandes as $commande) {
            foreach ($commande->produits as $produitCommande) {
                $produitId = $produitCommande->produit_id;
                if (!isset($produitsVendus[$produitId])) {
                    $produitsVendus[$produitId] = [
                        'produit' => $produitCommande->produit,
                        'quantite' => 0,
                        'montant' => 0
                    ];
                }
                $produitsVendus[$produitId]['quantite'] += $produitCommande->quantite;
                $produitsVendus[$produitId]['montant'] += $produitCommande->prix_total;
            }
        }

        // Trier par montant décroissant et prendre les top 5
        usort($produitsVendus, function($a, $b) {
            return $b['montant'] <=> $a['montant'];
        });
        $topProduits = array_slice($produitsVendus, 0, 5);

        return [
            'hotesse' => $hotesse,
            'total_commandes' => $totalCommandes,
            'total_ventes' => $totalVentes,
            'commandes_soldees' => $commandesSoldees,
            'commandes_en_cours' => $commandesEnCours,
            'taux_soldees' => $totalCommandes > 0 ? round(($commandesSoldees / $totalCommandes) * 100, 2) : 0,
            'tables_affectees' => $tablesAffectees,
            'moyenne_vente' => $totalCommandes > 0 ? round($totalVentes / $totalCommandes, 2) : 0,
            'top_produits' => $topProduits,
            'periode_debut' => $request->date_debut,
            'periode_fin' => $request->date_fin
        ];
    }

    /**
     * Récupère les 3 meilleures performances parmi les hôtesses - VERSION DÉFINITIVEMENT CORRIGÉE
     */
    private function getTopPerformances($user)
    {
        try {
            // Déterminer la période (30 derniers jours par défaut)
            $dateDebut = now()->subDays(30)->format('Y-m-d');
            $dateFin = now()->format('Y-m-d');

            // Récupérer les hôtesses selon les permissions
            $hotessesQuery = User::where('fonction', 'hotesse');
            
            if ($user->isAdmin()) {
                $hotessesQuery->where('admin_id', $user->id);
            } else {
                // Pour les managers/gérants, utiliser l'admin_id de l'utilisateur
                $hotessesQuery->where('admin_id', $user->admin_id);
            }

            $hotesses = $hotessesQuery->get(['id', 'prenom', 'name', 'email']);

            // Si aucune hôtesse trouvée, retourner un tableau vide
            if ($hotesses->isEmpty()) {
                return [];
            }

            $performances = [];

            // Récupérer toutes les commandes des hôtesses en une seule requête pour optimisation
            $hotesseIds = $hotesses->pluck('id')->toArray();
            
            // CORRECTION DÉFINITIVE : Utiliser hotesse_id et montant
            $commandes = Commande::whereIn('hotesse_id', $hotesseIds)
                               ->whereDate('created_at', '>=', $dateDebut)
                               ->whereDate('created_at', '<=', $dateFin)
                               ->get(['id', 'hotesse_id', 'montant', 'statut', 'created_at']);

            foreach ($hotesses as $hotesse) {
                // Filtrer les commandes pour cette hôtesse spécifique
                $commandesHotesse = $commandes->where('hotesse_id', $hotesse->id);
                
                // CORRECTION DÉFINITIVE : Utiliser montant
                $totalVentes = $commandesHotesse->sum('montant');
                $totalCommandes = $commandesHotesse->count();
                $commandesSoldees = $commandesHotesse->where('statut', 'soldée')->count();
                
                // Calcul des indicateurs de performance
                $tauxEfficacite = $totalCommandes > 0 ? round(($commandesSoldees / $totalCommandes) * 100, 2) : 0;
                $moyenneVente = $totalCommandes > 0 ? round($totalVentes / $totalCommandes, 2) : 0;

                // Calcul du score de performance (pondération : 70% ventes totales + 30% taux d'efficacité)
                $scorePerformance = ($totalVentes * 0.7) + ($tauxEfficacite * 100);

                $performances[] = [
                    'hotesse' => $hotesse,
                    'total_ventes' => $totalVentes,
                    'total_commandes' => $totalCommandes,
                    'commandes_soldees' => $commandesSoldees,
                    'taux_efficacite' => $tauxEfficacite,
                    'moyenne_vente' => $moyenneVente,
                    'score_performance' => $scorePerformance,
                    'periode_debut' => $dateDebut,
                    'periode_fin' => $dateFin
                ];
            }

            // Trier par score de performance (décroissant) et prendre les 3 premiers
            usort($performances, function($a, $b) {
                if ($b['score_performance'] !== $a['score_performance']) {
                    return $b['score_performance'] <=> $a['score_performance'];
                }
                return $b['total_ventes'] <=> $a['total_ventes'];
            });

            $topPerformances = array_slice($performances, 0, 3);

            // Ajouter des médailles et classement
            foreach ($topPerformances as $index => &$performance) {
                $performance['classement'] = $index + 1;
                $performance['medaille'] = $this->getMedaille($index + 1);
            }

            return $topPerformances;

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Retourne l'emoji médaille selon le classement
     */
    private function getMedaille($classement)
    {
        switch ($classement) {
            case 1: return '🥇';
            case 2: return '🥈';
            case 3: return '🥉';
            default: return '🏅';
        }
    }

    /**
     * MÉTHODE POUR METTRE À JOUR LE SOLDE ACTUEL EN TEMPS RÉEL - CORRIGÉE
     */
    public function updateSoldeActuel(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        try {
            // CORRECTION : Récupérer UNIQUEMENT la caisse de l'admin connecté
            $caisseAdmin = Caisse::where('user_id', $user->id)->first();
            
            if (!$caisseAdmin) {
                return response()->json(['error' => 'Caisse non trouvée'], 404);
            }

            // CORRECTION : Calculer UNIQUEMENT pour cet admin
            $total_retraits = TransactionCaisse::where('user_id', $user->id)
                ->where('type', 'retrait')
                ->where('est_traite', false)
                ->sum('montant');

            $total_depenses = TransactionCaisse::where('user_id', $user->id)
                ->where('type', 'depense')
                ->where('est_traite', false)
                ->sum('montant');

            // Solde actuel = solde d'ouverture + total retraits - total dépenses
            $solde_actuel = $caisseAdmin->solde_ouverture + $total_retraits - $total_depenses;

            // Mettre à jour le solde actuel
            $caisseAdmin->update([
                'solde_actuel' => $solde_actuel
            ]);

            return response()->json([
                'success' => true,
                'solde_actuel' => $solde_actuel,
                'solde_ouverture' => $caisseAdmin->solde_ouverture,
                'total_retraits' => $total_retraits,
                'total_depenses' => $total_depenses
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur de calcul: ' . $e->getMessage()], 500);
        }
    }

    /**
     * MÉTHODE POUR CALCULER LE SOLDE NET ACTUEL - CORRIGÉE AVEC NOUVELLE FORMULE
     */
    public function getSoldeNet(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        try {
            // CORRECTION : Récupérer UNIQUEMENT la caisse de l'admin connecté
            $caisseAdmin = Caisse::where('user_id', $user->id)->first();
            
            if (!$caisseAdmin) {
                return response()->json(['error' => 'Caisse non trouvée'], 404);
            }

            // NOUVELLE FORMULE : solde_net = solde_admin + total_caissiers + total_gerants
            $solde_admin = $caisseAdmin->solde_actuel;
            $total_caissiers = $this->getTotalCaissiers($user);
            $total_gerants = $this->getTotalGerants($user);
            $solde_net = $solde_admin + $total_caissiers + $total_gerants;

            return response()->json([
                'success' => true,
                'solde_net' => $solde_net,
                'solde_admin' => $solde_admin,
                'total_caissiers' => $total_caissiers,
                'total_gerants' => $total_gerants,
                'nombre_caissiers' => $this->getNombreCaissiers($user),
                'nombre_gerants' => $this->getNombreGerants($user)
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur de calcul: ' . $e->getMessage()], 500);
        }
    }
}