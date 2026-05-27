<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Table;
use App\Models\Commande;
use App\Models\Product;
use App\Models\Categorie;
use App\Models\Observation;
use App\Models\Commentaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
  /**
 * Dashboard principal - Redirige vers le bon dashboard selon le rôle
 */
public function dashboard()
{
    $user = Auth::user();
    
    // NOUVELLES STATISTIQUES POUR SUPER ADMIN
    if ($user->isSuperAdmin()) {
        $totalMobileCaissiers = User::where('fonction', 'mobile_caissier')->count();
        $totalGrandesCaisses = User::where('fonction', 'grande_caisse_mobile')->count();
    } else {
        $totalMobileCaissiers = 0;
        $totalGrandesCaisses = 0;
    }
    
    // Variables pour la vue dashboard
    $anneeEnCours = now()->year;
    $moisEnCours = now()->month;
    
    // Noms des mois (courts)
    $nomsMoisCourts = [
        1 => 'Jan', 2 => 'Fév', 3 => 'Mar', 4 => 'Avr', 
        5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Aoû', 
        9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc'
    ];
    
    // Redirection selon le type d'utilisateur
    if ($user->isHotesse()) {
        return $this->hotesseDashboard();
    }
    
    if ($user->isManager()) {
        return $this->managerDashboard();
    }

    if ($user->isMobileCaissier()) {
        return $this->mobileCaissierDashboard();
    }

    // NOUVEAU : Redirection pour les commerciaux
    if ($user->isCommercial()) {
        return redirect()->route('commercial.dashboard');
    }

    // NOUVEAU : Redirection pour la grande caisse mobile
    if ($user->isGrandeCaisseMobile()) {
        return redirect()->route('grande-caisse.dashboard');
    }
    
    // Logique existante pour admin/gerant/superadmin
    if ($user->isAdmin() || $user->isGerant() || $user->isSuperAdmin()) {
        // Récupérer les hôtesses
        $hotesses = User::where('fonction', 'hotesse')
                       ->where(function($query) use ($user) {
                           if ($user->isSuperAdmin()) {
                               return;
                           }
                           if ($user->isAdmin()) {
                               $query->where('admin_id', $user->id);
                           }
                           if ($user->isGerant()) {
                               $query->where('admin_id', $user->admin_id);
                           }
                       })
                       ->get();

        // Récupérer les caissiers
        $caissiers = User::where('fonction', 'caissier')
                        ->where(function($query) use ($user) {
                            if ($user->isSuperAdmin()) {
                                return;
                            }
                            if ($user->isAdmin()) {
                                $query->where('admin_id', $user->id);
                            }
                            if ($user->isGerant()) {
                                $query->where('admin_id', $user->admin_id);
                            }
                        })
                        ->get();

        // Récupérer les caissiers mobile money
        $mobileCaissiers = User::where('fonction', 'mobile_caissier')
                              ->where(function($query) use ($user) {
                                  if ($user->isSuperAdmin()) {
                                      return;
                                  }
                                  if ($user->isAdmin()) {
                                      $query->where('admin_id', $user->id);
                                  }
                                  if ($user->isGerant()) {
                                      $query->where('admin_id', $user->admin_id);
                                  }
                              })
                              ->get();

        // NOUVEAU : Récupérer les commerciaux
        $commerciaux = User::where('fonction', 'commercial')
                          ->where(function($query) use ($user) {
                              if ($user->isSuperAdmin()) {
                                  return;
                              }
                              if ($user->isAdmin()) {
                                  $query->where('admin_id', $user->id);
                              }
                          })
                          ->get();

        // NOUVEAU : Récupérer les grandes caisses mobile
        $grandesCaissesMobile = User::where('fonction', 'grande_caisse_mobile')
                                   ->where(function($query) use ($user) {
                                       if ($user->isSuperAdmin()) {
                                           return;
                                       }
                                       if ($user->isAdmin()) {
                                           $query->where('admin_id', $user->id);
                                       }
                                   })
                                   ->get();

        // CORRECTION : Récupérer les mobile caissiers par commercial
        $commerciauxAvecCaissiers = [];
        foreach ($commerciaux as $commercial) {
            $commerciauxAvecCaissiers[$commercial->id] = $commercial->mobileCaissiersCommercial()->get();
        }
        
        // CORRECTION : Récupérer les comptes regroupés par grande caisse
        $grandesCaissesAvecComptes = [];
        foreach ($grandesCaissesMobile as $grandeCaisse) {
            $grandesCaissesAvecComptes[$grandeCaisse->id] = $grandeCaisse->comptesMobileCaissiers()->get();
        }

        // Statistiques des tables
        $tables = Table::visibleTo($user)->get();
        $totalTables = $tables->count();
        $tablesAffectees = $tables->where('user_id', '!=', null)->count();
        $tablesLibres = $tables->where('user_id', null)->count();

        // Commandes en cours
        $commandesEnCours = Commande::where('statut', 'en cours')
                                   ->visibleTo($user)
                                   ->with(['table', 'user'])
                                   ->orderBy('created_at', 'desc')
                                   ->limit(10)
                                   ->get();

        return view('dashboard', compact(
            'hotesses', 
            'caissiers',
            'mobileCaissiers',
            'commerciaux',
            'grandesCaissesMobile',
            'commerciauxAvecCaissiers',
            'grandesCaissesAvecComptes',
            'totalTables', 
            'tablesAffectees', 
            'tablesLibres',
            'commandesEnCours',
            'totalMobileCaissiers',
            'totalGrandesCaisses',
            'anneeEnCours',
            'moisEnCours',
            'nomsMoisCourts'     
        ));
    }

    // Par défaut, retourner la vue dashboard standard
    return view('dashboard');
}

    /**
     * Dashboard spécifique pour les caissiers mobile money
     */
    private function mobileCaissierDashboard()
    {
        $user = Auth::user();
        
        // Rediriger vers le module mobile money
        return redirect()->route('mobile-money.index');
    }

    /**
     * Dashboard spécifique pour les Managers - CORRIGÉ AVEC PERFORMANCES
     */
    private function managerDashboard()
    {
        $user = Auth::user();
        
        // Récupérer les hôtesses du manager
        $hotesses = User::where('fonction', 'hotesse')
                       ->where('admin_id', $user->admin_id)
                       ->withCount(['tables', 'observationsRecues'])
                       ->get();

        // Statistiques des hôtesses
        $totalHotesses = $hotesses->count();
        $hotessesAvecTables = $hotesses->where('tables_count', '>', 0)->count();
        $hotessesSansTables = $hotesses->where('tables_count', 0)->count();

        // Commandes des hôtesses
        $commandesEnCours = Commande::whereHas('hotesse', function($query) use ($user) {
                            $query->where('admin_id', $user->admin_id);
                        })
                        ->where('statut', 'en cours')
                        ->with(['table', 'hotesse', 'produits'])
                        ->orderBy('created_at', 'desc')
                        ->get();

        $commandesSoldees = Commande::whereHas('hotesse', function($query) use ($user) {
                            $query->where('admin_id', $user->admin_id);
                        })
                        ->where('statut', 'soldée')
                        ->with(['table', 'hotesse', 'produits'])
                        ->orderBy('updated_at', 'desc')
                        ->paginate(10);

        // Tables de l'établissement
        $tables = Table::where('admin_id', $user->admin_id)
                      ->with(['user', 'commandesEnCours'])
                      ->get();

        $totalTables = $tables->count();
        $tablesAffectees = $tables->where('user_id', '!=', null)->count();
        $tablesLibres = $tables->where('user_id', null)->count();

        // Observations récentes
        $observationsRecentes = Observation::where('manager_id', $user->id)
                                         ->with('hotesse')
                                         ->orderBy('created_at', 'desc')
                                         ->take(5)
                                         ->get();

        // COMMENTAIRES RÉCENTS - CORRECTION
        $commentairesRecents = \App\Models\Commentaire::with(['auteur', 'observation.hotesse'])
                                    ->whereHas('observation', function($query) use ($user) {
                                        $query->where('manager_id', $user->id);
                                    })
                                    ->where('user_id', '!=', $user->id)
                                    ->orderBy('created_at', 'desc')
                                    ->take(5)
                                    ->get();

        // NOUVEAU : Top performances des hôtesses (30 derniers jours)
        $topPerformances = $this->getTopPerformancesForManager($user);

        return view('manager.dashboard', compact(
            'hotesses',
            'totalHotesses',
            'hotessesAvecTables',
            'hotessesSansTables',
            'commandesEnCours',
            'commandesSoldees',
            'tables',
            'totalTables',
            'tablesAffectees',
            'tablesLibres',
            'observationsRecentes',
            'commentairesRecents',
            'topPerformances' 
        ));
    }

    /**
     * Récupère les top performances pour le manager
     */
    private function getTopPerformancesForManager($user)
    {
        try {
            $dateDebut = now()->subDays(30)->format('Y-m-d');
            $dateFin = now()->format('Y-m-d');

            // Récupérer les hôtesses du manager
            $hotesses = User::where('fonction', 'hotesse')
                           ->where('admin_id', $user->admin_id)
                           ->get();

            $performances = [];

            foreach ($hotesses as $hotesse) {
                // CORRECTION : Utiliser hotesse_id au lieu de user_id
                $commandes = Commande::where('hotesse_id', $hotesse->id)
                                   ->whereDate('created_at', '>=', $dateDebut)
                                   ->whereDate('created_at', '<=', $dateFin)
                                   ->get();

                // CORRECTION : Utiliser montant au lieu de montant_total
                $totalVentes = $commandes->sum('montant');
                $totalCommandes = $commandes->count();
                $commandesSoldees = $commandes->where('statut', 'soldée')->count();
                
                $tauxEfficacite = $totalCommandes > 0 ? round(($commandesSoldees / $totalCommandes) * 100, 2) : 0;

                $performances[] = [
                    'hotesse' => $hotesse,
                    'total_ventes' => $totalVentes,
                    'total_commandes' => $totalCommandes,
                    'commandes_soldees' => $commandesSoldees,
                    'taux_efficacite' => $tauxEfficacite,
                    'moyenne_vente' => $totalCommandes > 0 ? round($totalVentes / $totalCommandes, 2) : 0
                ];
            }

            usort($performances, function($a, $b) {
                return $b['total_ventes'] <=> $a['total_ventes'];
            });

            return array_slice($performances, 0, 3);

        } catch (\Exception $e) {
            \Log::error('Erreur dans getTopPerformancesForManager: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Dashboard spécifique pour les hôtesses - CORRIGÉ avec observations
     */
    private function hotesseDashboard()
    {
        $user = Auth::user();
        
        // Tables affectées à cette hôtesse
        $tablesAffectees = Table::where('user_id', $user->id)
                               ->with(['commandes' => function($query) {
                                   $query->where('statut', 'en cours');
                               }])
                               ->orderBy('numero')
                               ->get();

        // Commandes en cours pour les tables de cette hôtesse
        $commandesEnCours = Commande::where('statut', 'en cours')
                                   ->whereHas('table', function($query) use ($user) {
                                       $query->where('user_id', $user->id);
                                   })
                                   ->with(['table', 'products'])
                                   ->orderBy('created_at', 'desc')
                                   ->get();

        // Commandes soldées pour les tables de cette hôtesse (avec pagination)
        $commandesSoldees = Commande::where('statut', 'soldée')
                                   ->whereHas('table', function($query) use ($user) {
                                       $query->where('user_id', $user->id);
                                   })
                                   ->with(['table', 'products'])
                                   ->orderBy('updated_at', 'desc')
                                   ->paginate(10);

        // Observations reçues - CORRECTION : Récupération des observations
        $observationsRecentes = Observation::where('hotesse_id', $user->id)
                                         ->with('manager')
                                         ->orderBy('date_observation', 'desc')
                                         ->limit(5)
                                         ->get();

        $observationsNonLues = Observation::where('hotesse_id', $user->id)
                                        ->where('est_lu', false)
                                        ->get();

        // CORRECTION : Récupération simplifiée des produits
        $products = Product::where(function($query) use ($user) {
                // Si l'hôtesse a un admin_id, prendre les produits de cet admin
                if ($user->admin_id) {
                    $query->where('user_id', $user->admin_id);
                } else {
                    // Sinon, prendre les produits de l'utilisateur lui-même
                    $query->where('user_id', $user->id);
                }
            })
            ->where('quantite', '>', 0)
            ->with('categorie')
            ->orderBy('designation')
            ->get();

        // CORRECTION : Récupération simplifiée des catégories
        $categories = Categorie::where(function($query) use ($user) {
                if ($user->admin_id) {
                    $query->where('user_id', $user->admin_id);
                } else {
                    $query->where('user_id', $user->id);
                }
            })
            ->whereHas('products')
            ->orderBy('nom')
            ->get();

        return view('hotesse.dashboard', compact(
            'tablesAffectees',
            'commandesEnCours',
            'commandesSoldees',
            'observationsRecentes',
            'observationsNonLues',
            'products',
            'categories'
        ));
    }

    /**
     * Afficher la liste des produits (lecture seule pour les hôtesses et managers)
     */
    public function productsIndex()
    {
        $user = Auth::user();
        
        // Si c'est une hôtesse, rediriger vers son dashboard ou afficher une vue en lecture seule
        if ($user->isHotesse()) {
            return $this->hotesseProducts();
        }
        
        // Si c'est un manager, afficher vue en lecture seule
        if ($user->isManager()) {
            return $this->managerProducts();
        }

        // Si c'est un mobile caissier, rediriger vers mobile money
        if ($user->isMobileCaissier()) {
            return redirect()->route('mobile-money.index');
        }

        // NOUVEAU : Redirection pour les commerciaux
        if ($user->isCommercial()) {
            return redirect()->route('commercial.dashboard');
        }

        // NOUVEAU : Redirection pour la grande caisse mobile
        if ($user->isGrandeCaisseMobile()) {
            return redirect()->route('grande-caisse.dashboard');
        }
        
        // Logique normale pour les autres rôles
        if (!$user->isAdmin() && !$user->isGerant() && !$user->isSuperAdmin()) {
            abort(403, 'Accès non autorisé.');
        }

        $products = Product::where(function($query) use ($user) {
                if ($user->isSuperAdmin()) {
                    return;
                }
                if ($user->isAdmin()) {
                    $query->where('user_id', $user->id);
                }
                if ($user->isGerant()) {
                    $query->where('user_id', $user->admin_id);
                }
            })
            ->with('categorie')
            ->orderBy('designation')
            ->get();

        $categories = Categorie::where(function($query) use ($user) {
                if ($user->isSuperAdmin()) {
                    return;
                }
                if ($user->isAdmin()) {
                    $query->where('user_id', $user->id);
                }
                if ($user->isGerant()) {
                    $query->where('user_id', $user->admin_id);
                }
            })
            ->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Vue produits en lecture seule pour les managers
     */
    private function managerProducts()
    {
        $user = Auth::user();
        
        $products = Product::where('user_id', $user->admin_id)
            ->where('quantite', '>', 0)
            ->with('categorie')
            ->orderBy('designation')
            ->get();

        $categories = Categorie::where('user_id', $user->admin_id)
            ->whereHas('products')
            ->orderBy('nom')
            ->get();

        return view('manager.products-index', compact('products', 'categories'));
    }

    /**
     * Vue produits en lecture seule pour les hôtesses
     */
    private function hotesseProducts()
    {
        $user = Auth::user();
        
        $products = Product::where(function($query) use ($user) {
                if ($user->admin_id) {
                    $query->where('user_id', $user->admin_id);
                } else {
                    $query->where('user_id', $user->id);
                }
            })
            ->where('quantite', '>', 0)
            ->with('categorie')
            ->orderBy('designation')
            ->get();

        $categories = Categorie::where(function($query) use ($user) {
                if ($user->admin_id) {
                    $query->where('user_id', $user->admin_id);
                } else {
                    $query->where('user_id', $user->id);
                }
            })
            ->whereHas('products')
            ->orderBy('nom')
            ->get();

        return view('hotesse.products-index', compact('products', 'categories'));
    }

    /**
     * Afficher un produit spécifique (lecture seule pour les hôtesses et managers)
     */
    public function showProduct(Product $product)
    {
        $user = Auth::user();
        
        // Vérifier la visibilité du produit
        if (!$this->canViewProduct($product, $user)) {
            abort(403, 'Accès non autorisé.');
        }

        // Pour les hôtesses et managers, vue en lecture seule
        if ($user->isHotesse() || $user->isManager()) {
            $view = $user->isHotesse() ? 'hotesse.products-show' : 'manager.products-show';
            return view($view, compact('product'));
        }

        return view('products.show', compact('product'));
    }

    /**
     * Vérifie si l'utilisateur peut voir le produit
     */
    private function canViewProduct(Product $product, $user)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $product->user_id === $user->id;
        }

        if ($user->isGerant() || $user->isHotesse() || $user->isManager() || $user->isMobileCaissier()) {
            return $product->user_id === $user->admin_id;
        }

        return $product->user_id === $user->id;
    }

    /**
     * NOUVELLE FONCTION : Recherche d'utilisateurs par email ou nom/prenom
     */
    public function search(Request $request)
    {
        $currentUser = Auth::user();
        
        // Vérifier les permissions
        if (!$currentUser->isSuperAdmin() && !$currentUser->isAdmin() && !$currentUser->isManager()) {
            abort(403, 'Accès non autorisé.');
        }

        $searchTerm = $request->input('search', '');
        
        if (empty($searchTerm)) {
            return redirect()->route('users.index');
        }

        // Construire la requête de recherche
        $query = User::query();
        
        // Appliquer les permissions de visibilité
        if ($currentUser->isSuperAdmin()) {
            // Super Admin voit tous les utilisateurs
        } elseif ($currentUser->isAdmin()) {
            // Admin voit ses utilisateurs dépendants
            $query->where(function($q) use ($currentUser) {
                $q->where('id', $currentUser->id)
                  ->orWhere('admin_id', $currentUser->id)
                  ->orWhereHas('admin', function($subQ) use ($currentUser) {
                      $subQ->where('admin_id', $currentUser->id);
                  });
            });
        } elseif ($currentUser->isManager()) {
            // Manager ne voit que les hôtesses de son admin
            $query->where('fonction', 'hotesse')
                  ->where('admin_id', $currentUser->admin_id);
        }

        // Rechercher par email OU nom/prénom
        $query->where(function($q) use ($searchTerm) {
            $q->where('email', 'LIKE', "%{$searchTerm}%")
              ->orWhereRaw("CONCAT(prenom, ' ', name) LIKE ?", ["%{$searchTerm}%"])
              ->orWhere('prenom', 'LIKE', "%{$searchTerm}%")
              ->orWhere('name', 'LIKE', "%{$searchTerm}%");
        });

        $users = $query->with('admin')
                      ->orderBy('fonction')
                      ->orderBy('prenom')
                      ->paginate(20);

        return view('users.index', compact('users', 'searchTerm'));
    }

    public function index()
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin() && !Auth::user()->isManager()) {
            abort(403, 'Accès non autorisé.');
        }

        $currentUser = Auth::user();
        
        if ($currentUser->isSuperAdmin()) {
            $users = User::with('admin')
                        ->orderBy('fonction')
                        ->orderBy('prenom')
                        ->paginate(20);
        } elseif ($currentUser->isAdmin()) {
            // CORRECTION : Inclure tous les utilisateurs de la lignée admin
            $users = User::with('admin')
                        ->where(function($query) use ($currentUser) {
                            $query->where('id', $currentUser->id)
                                  ->orWhere('admin_id', $currentUser->id)
                                  ->orWhereHas('admin', function($q) use ($currentUser) {
                                      $q->where('admin_id', $currentUser->id);
                                  });
                        })
                        ->orderBy('fonction')
                        ->orderBy('prenom')
                        ->paginate(20);
        } elseif ($currentUser->isManager()) {
            // Manager ne voit que les hôtesses
            $users = User::with('admin')
                        ->where('fonction', 'hotesse')
                        ->where('admin_id', $currentUser->admin_id)
                        ->orderBy('prenom')
                        ->paginate(20);
        } else {
            $users = collect();
        }

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin() && !$currentUser->isAdmin() && !$currentUser->isManager()) {
            abort(403, 'Accès non autorisé.');
        }

        $fonctions = $this->getAvailableFonctions();
        $admins = $this->getAvailableAdmins();

        // NOUVEAU : Récupérer les commerciaux pour l'assignation des mobile caissiers
        $commerciaux = User::where('fonction', 'commercial')->get();
        
        // NOUVEAU : Récupérer les grandes caisses mobile pour le regroupement
        $grandesCaissesMobile = User::where('fonction', 'grande_caisse_mobile')->get();

        return view('users.create', compact('fonctions', 'admins', 'commerciaux', 'grandesCaissesMobile'));
    }

    /**
     * Méthode de debug pour vérifier la visibilité d'un utilisateur spécifique
     */
    public function debugUserVisibility($userId)
    {
        $currentUser = Auth::user();
        $user = User::find($userId);
        
        echo "<h1>DEBUG VISIBILITÉ UTILISATEUR</h1>";
        echo "<p>Utilisateur connecté: {$currentUser->prenom} {$currentUser->name} (ID: {$currentUser->id}, Fonction: {$currentUser->fonction})</p>";
        echo "<p>Utilisateur cible: {$user->prenom} {$user->name} (ID: {$user->id}, Fonction: {$user->fonction}, Admin ID: {$user->admin_id})</p>";
        
        // Test de la requête index
        $visibleUsers = User::with('admin')
                        ->where(function($query) use ($currentUser) {
                            $query->where('id', $currentUser->id)
                                  ->orWhere('admin_id', $currentUser->id);
                        })
                        ->get();
        
        $isVisible = $visibleUsers->contains('id', $userId);
        
        echo "<p>Visible dans index(): " . ($isVisible ? 'OUI' : 'NON') . "</p>";
        
        if (!$isVisible) {
            echo "<h2>Utilisateurs visibles:</h2>";
            foreach ($visibleUsers as $visibleUser) {
                echo "<p>- {$visibleUser->prenom} {$visibleUser->name} (ID: {$visibleUser->id}, Fonction: {$visibleUser->fonction}, Admin ID: {$visibleUser->admin_id})</p>";
            }
        }
        
        die();
    }

    public function store(Request $request)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin() && !$currentUser->isAdmin() && !$currentUser->isManager()) {
            abort(403, 'Accès non autorisé.');
        }

        $validationRules = [
            'prenom' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'fonction' => 'required|in:hotesse,caissier,gerant,admin,manager,mobile_caissier,commercial,grande_caisse_mobile',
            'admin_id' => 'nullable|exists:users,id',
            'commercial_id' => 'nullable|exists:users,id', 
            'grande_caisse_id' => 'nullable|exists:users,id', 
            'telephone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        if (Schema::hasColumn('users', 'adresse')) {
            $validationRules['adresse'] = 'nullable|string|max:500';
        }

        $validated = $request->validate($validationRules);

        // CORRECTION STRICTE : Vérifications renforcées des autorisations
        if (!$this->canAssignFonction($validated['fonction'], $validated['admin_id'] ?? null)) {
            return back()->with('error', 'Vous n\'êtes pas autorisé à créer ce type de compte.')->withInput();
        }

        DB::beginTransaction();

        try {
            $userData = [
                'prenom' => $validated['prenom'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'fonction' => $validated['fonction'],
                'admin_id' => $this->determineAdminId($validated['admin_id'] ?? null),
                'password' => Hash::make($validated['password']),
            ];

            // NOUVEAU : Gestion du commercial_id
            if (isset($validated['commercial_id']) && $validated['fonction'] === 'mobile_caissier') {
                $userData['commercial_id'] = $validated['commercial_id'];
            }

            // NOUVEAU : Gestion de la grande_caisse_id
            if (isset($validated['grande_caisse_id']) && $validated['fonction'] === 'mobile_caissier') {
                $userData['grande_caisse_id'] = $validated['grande_caisse_id'];
            }

            if (isset($validated['telephone'])) {
                $userData['contact'] = $validated['telephone'];
            }

            if (isset($validated['adresse']) && Schema::hasColumn('users', 'adresse')) {
                $userData['adresse'] = $validated['adresse'];
            }

            $user = User::create($userData);

            DB::commit();

            return redirect()->route('users.index')
                           ->with('success', 'Utilisateur créé avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la création: ' . $e->getMessage())->withInput();
        }
    }

    public function show(User $user)
    {
        if (!$this->canManageUser($user)) {
            abort(403, 'Accès non autorisé.');
        }

        $user->load('admin', 'subUsers', 'commercial', 'grandeCaisseMobile');

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        if (!$this->canManageUser($user)) {
            abort(403, 'Accès non autorisé.');
        }

        $fonctions = $this->getAvailableFonctions();
        $admins = $this->getAvailableAdmins();

        // NOUVEAU : Récupérer les commerciaux pour l'assignation des mobile caissiers
        $commerciaux = User::where('fonction', 'commercial')->get();
        
        // NOUVEAU : Récupérer les grandes caisses mobile pour le regroupement
        $grandesCaissesMobile = User::where('fonction', 'grande_caisse_mobile')->get();

        return view('users.edit', compact('user', 'fonctions', 'admins', 'commerciaux', 'grandesCaissesMobile'));
    }

    public function update(Request $request, User $user)
    {
        if (!$this->canManageUser($user)) {
            abort(403, 'Accès non autorisé.');
        }

        $validated = $request->validate([
            'prenom' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'fonction' => 'required|in:caissier,gerant,admin,hotesse,manager,mobile_caissier,commercial,grande_caisse_mobile',
            'admin_id' => 'nullable|exists:users,id',
            'commercial_id' => 'nullable|exists:users,id',
            'grande_caisse_id' => 'nullable|exists:users,id',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:500',
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        if (!$this->canUpdateUser($user, $validated['fonction'], $validated['admin_id'] ?? null)) {
            return back()->with('error', 'Vous n\'êtes pas autorisé à effectuer cette modification.')->withInput();
        }

        DB::beginTransaction();

        try {
            $updateData = [
                'prenom' => $validated['prenom'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'fonction' => $validated['fonction'],
                'admin_id' => $this->determineAdminId($validated['admin_id'] ?? null),
                'adresse' => $validated['adresse'],
            ];

            // NOUVEAU : Gestion du commercial_id
            if (isset($validated['commercial_id']) && $validated['fonction'] === 'mobile_caissier') {
                $updateData['commercial_id'] = $validated['commercial_id'];
            } else {
                $updateData['commercial_id'] = null;
            }

            // NOUVEAU : Gestion de la grande_caisse_id
            if (isset($validated['grande_caisse_id']) && $validated['fonction'] === 'mobile_caissier') {
                $updateData['grande_caisse_id'] = $validated['grande_caisse_id'];
            } else {
                $updateData['grande_caisse_id'] = null;
            }

            if (isset($validated['telephone'])) {
                $updateData['contact'] = $validated['telephone'];
            }

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);

            DB::commit();

            return redirect()->route('users.index')
                           ->with('success', 'Utilisateur modifié avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la modification: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(User $user)
    {
        if (!$this->canManageUser($user)) {
            abort(403, 'Accès non autorisé.');
        }

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $hasSubUsers = false;
        try {
            if (method_exists($user, 'subUsers') && Schema::hasColumn('users', 'admin_id')) {
                $hasSubUsers = $user->subUsers()->exists();
            }
        } catch (\Exception $e) {
            $hasSubUsers = false;
        }

        if ($hasSubUsers) {
            return back()->with('error', 'Impossible de supprimer cet utilisateur car il a des utilisateurs dépendants.');
        }

        DB::beginTransaction();

        try {
            $user->delete();

            DB::commit();

            return redirect()->route('users.index')
                           ->with('success', 'Utilisateur supprimé avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    private function getAvailableFonctions()
    {
        $currentUser = Auth::user();

        if ($currentUser->isSuperAdmin()) {
            return [
                'caissier' => 'Caissier',
                'gerant' => 'Gérant', 
                'admin' => 'Admin',
                'super_admin' => 'Super Admin',
                'hotesse' => 'Hôtesse',
                'manager' => 'Manager',
                'mobile_caissier' => 'Caissier Mobile Money',
                'commercial' => 'Commercial', 
                'grande_caisse_mobile' => 'Grande Caisse Mobile' 
            ];
        }

        if ($currentUser->isAdmin()) {
            return [
                'caissier' => 'Caissier',
                'gerant' => 'Gérant',
                'hotesse' => 'Hôtesse',
                'manager' => 'Manager',
                'mobile_caissier' => 'Caissier Mobile Money',
            ];
        }

        if ($currentUser->isManager()) {
            return [
                'hotesse' => 'Hôtesse'
            ];
        }

        return [];
    }

    private function getAvailableAdmins()
    {
        $currentUser = Auth::user();

        if ($currentUser->isSuperAdmin()) {
            return User::whereIn('fonction', ['super_admin', 'admin'])
                      ->orderBy('prenom')
                      ->get()
                      ->mapWithKeys(function ($user) {
                          return [$user->id => $user->prenom . ' ' . $user->name . ' (' . $user->fonction . ')'];
                      });
        }

        if ($currentUser->isAdmin()) {
            return User::where('id', $currentUser->id)
                      ->get()
                      ->mapWithKeys(function ($user) {
                          return [$user->id => $user->prenom . ' ' . $user->name . ' (' . $user->fonction . ') - MA LIGNÉE'];
                      });
        }

        if ($currentUser->isManager()) {
            return User::where('id', $currentUser->admin_id)
                      ->get()
                      ->mapWithKeys(function ($user) {
                          return [$user->id => $user->prenom . ' ' . $user->name . ' (' . $user->fonction . ') - MON ADMIN'];
                      });
        }

        return collect();
    }

    private function canAssignFonction($fonction, $adminId = null)
    {
        $currentUser = Auth::user();

        if ($currentUser->isSuperAdmin()) {
            return true;
        }

        if ($currentUser->isAdmin()) {
            if (in_array($fonction, ['admin', 'commercial', 'grande_caisse_mobile', 'super_admin'])) {
                return false;
            }

            $allowedFonctions = ['caissier', 'gerant', 'hotesse', 'manager', 'mobile_caissier'];
            if (!in_array($fonction, $allowedFonctions)) {
                return false;
            }

            if ($adminId && $adminId !== $currentUser->id) {
                return false;
            }

            return true;
        }

        if ($currentUser->isManager()) {
            if ($fonction !== 'hotesse') {
                return false;
            }

            if ($adminId && $adminId !== $currentUser->admin_id) {
                return false;
            }

            return true;
        }

        return false;
    }

    private function determineAdminId($requestedAdminId = null)
    {
        $currentUser = Auth::user();

        if ($currentUser->isSuperAdmin()) {
            return $requestedAdminId;
        }

        if ($currentUser->isAdmin()) {
            return $currentUser->id;
        }

        if ($currentUser->isManager()) {
            return $currentUser->admin_id;
        }

        return $requestedAdminId;
    }

    private function canUpdateUser(User $user, $newFonction, $newAdminId = null)
    {
        $currentUser = Auth::user();

        if ($currentUser->isSuperAdmin()) {
            return true;
        }

        if ($currentUser->isAdmin()) {
            if (!$this->canManageUser($user)) {
                return false;
            }

            if (in_array($newFonction, ['admin', 'commercial', 'grande_caisse_mobile', 'super_admin'])) {
                return false;
            }

            if ($newAdminId && $newAdminId !== $currentUser->id) {
                return false;
            }

            if ($user->id === $currentUser->id && $newFonction !== 'admin') {
                return false;
            }

            $allowedFonctions = ['caissier', 'gerant', 'admin', 'hotesse', 'manager', 'mobile_caissier'];
            if (!in_array($newFonction, $allowedFonctions)) {
                return false;
            }

            return true;
        }

        if ($currentUser->isManager()) {
            if ($user->fonction !== 'hotesse') {
                return false;
            }

            if ($newFonction !== 'hotesse') {
                return false;
            }

            if ($newAdminId && $newAdminId !== $currentUser->admin_id) {
                return false;
            }

            return true;
        }

        return false;
    }
    
    /**
     * NOUVEAU : Affecter un mobile caissier à un commercial
     */
    public function affecterCommercial(Request $request)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Accès non autorisé.');
        }

        $validated = $request->validate([
            'commercial_id' => 'required|exists:users,id',
            'mobile_caissier_id' => 'required|exists:users,id',
        ]);

        DB::beginTransaction();

        try {
            $mobileCaissier = User::find($validated['mobile_caissier_id']);
            $mobileCaissier->commercial_id = $validated['commercial_id'];
            $mobileCaissier->save();

            DB::commit();

            return back()->with('success', 'Mobile caissier affecté au commercial avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'affectation: ' . $e->getMessage());
        }
    }

    /**
     * NOUVEAU : Affecter un mobile caissier à une grande caisse mobile
     */
    public function affecterGrandeCaisse(Request $request)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Accès non autorisé.');
        }

        $validated = $request->validate([
            'grande_caisse_id' => 'required|exists:users,id',
            'mobile_caissier_id' => 'required|exists:users,id',
        ]);

        DB::beginTransaction();

        try {
            $mobileCaissier = User::find($validated['mobile_caissier_id']);
            $mobileCaissier->grande_caisse_id = $validated['grande_caisse_id'];
            $mobileCaissier->save();

            DB::commit();

            return back()->with('success', 'Mobile caissier regroupé dans la grande caisse avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors du regroupement: ' . $e->getMessage());
        }
    }

    /**
     * NOUVEAU : Mettre à jour l'affectation (retirer)
     */
    public function updateAffectation(Request $request, User $user)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Accès non autorisé.');
        }

        DB::beginTransaction();

        try {
            if ($request->has('commercial_id') && $request->commercial_id === '') {
                $user->commercial_id = null;
            }

            if ($request->has('grande_caisse_id') && $request->grande_caisse_id === '') {
                $user->grande_caisse_id = null;
            }

            $user->save();

            DB::commit();

            return back()->with('success', 'Affectation mise à jour avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }
    
    /**
     * CORRECTION : Méthode publique pour vérifier si l'utilisateur peut gérer un autre utilisateur
     */
    public function canManageUser(User $user)
    {
        $currentUser = Auth::user();

        if ($currentUser->isSuperAdmin()) {
            return true;
        }

        if ($currentUser->isAdmin()) {
            return $user->id === $currentUser->id || $user->admin_id === $currentUser->id;
        }

        if ($currentUser->isGerant()) {
            return $user->admin_id === $currentUser->admin_id;
        }

        if ($currentUser->isManager()) {
            return $user->fonction === 'hotesse' && $user->admin_id === $currentUser->admin_id;
        }

        if ($currentUser->isCommercial()) {
            return $user->commercial_id === $currentUser->id && $user->fonction === 'mobile_caissier';
        }

        if ($currentUser->isGrandeCaisseMobile()) {
            return $user->grande_caisse_id === $currentUser->id && $user->fonction === 'mobile_caissier';
        }

        return false;
    }

    /**
     * CORRECTION : Méthode pour récupérer les hôtesses selon les permissions
     */
    public function getHotessesForAffectation()
    {
        $currentUser = Auth::user();
        
        if ($currentUser->isSuperAdmin()) {
            $hotesses = User::where('fonction', 'hotesse')
                           ->where('est_actif', true)
                           ->orderBy('prenom')
                           ->get();
        } elseif ($currentUser->isAdmin()) {
            $hotesses = User::where('fonction', 'hotesse')
                           ->where('admin_id', $currentUser->id)
                           ->where('est_actif', true)
                           ->orderBy('prenom')
                           ->get();
        } elseif ($currentUser->isGerant() || $currentUser->isManager()) {
            $hotesses = User::where('fonction', 'hotesse')
                           ->where('admin_id', $currentUser->admin_id)
                           ->where('est_actif', true)
                           ->orderBy('prenom')
                           ->get();
        } else {
            $hotesses = collect();
        }

        return $hotesses;
    }

    /**
     * Gestion des observations
     */
    public function createObservation($hotesseId)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->canCreateObservations()) {
            abort(403, 'Accès non autorisé.');
        }

        $hotesse = User::where('fonction', 'hotesse')
                      ->where('id', $hotesseId)
                      ->firstOrFail();

        if (!$this->canManageUser($hotesse)) {
            abort(403, 'Accès non autorisé à cette hôtesse.');
        }

        return view('observations.create', compact('hotesse'));
    }

    public function storeObservation(Request $request, $hotesseId)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->canCreateObservations()) {
            abort(403, 'Accès non autorisé.');
        }

        $hotesse = User::where('fonction', 'hotesse')
                      ->where('id', $hotesseId)
                      ->firstOrFail();

        if (!$this->canManageUser($hotesse)) {
            abort(403, 'Accès non autorisé à cette hôtesse.');
        }

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'contenu' => 'required|string',
            'type' => 'required|in:positif,negatif,suggestion',
            'priorite' => 'required|in:faible,moyenne,elevee'
        ]);

        DB::beginTransaction();

        try {
            $observation = Observation::create([
                'manager_id' => $currentUser->id,
                'hotesse_id' => $hotesseId,
                'titre' => $validated['titre'],
                'contenu' => $validated['contenu'],
                'type' => $validated['type'],
                'priorite' => $validated['priorite'],
                'date_observation' => now(),
            ]);

            DB::commit();

            return redirect()->route('manager.dashboard')
                           ->with('success', 'Observation envoyée avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'envoi de l\'observation: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * NOUVEAU : Activer/désactiver un mobile caissier
     */
    public function toggleActivation(User $user)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Accès non autorisé. Seul le Super Admin peut modifier le statut.');
        }

        if ($user->fonction !== 'mobile_caissier') {
            return back()->with('error', 'Cette action n\'est possible que pour les mobile caissiers.');
        }

        DB::beginTransaction();

        try {
            $user->est_actif = !$user->est_actif;
            $user->save();

            DB::commit();

            $message = $user->est_actif ? 'Mobile caissier activé avec succès!' : 'Mobile caissier désactivé avec succès!';
            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la modification du statut: ' . $e->getMessage());
        }
    }
    
    /**
     * NOUVEAU : Retirer l'affectation d'un commercial
     */
    public function retirerAffectationCommercial(User $user)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Accès non autorisé.');
        }

        DB::beginTransaction();

        try {
            $user->commercial_id = null;
            $user->save();

            DB::commit();

            return back()->with('success', 'Affectation commerciale retirée avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors du retrait de l\'affectation: ' . $e->getMessage());
        }
    }

    /**
     * NOUVEAU : Retirer l'affectation d'une grande caisse
     */
    public function retirerAffectationGrandeCaisse(User $user)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Accès non autorisé.');
        }

        DB::beginTransaction();

        try {
            $user->grande_caisse_id = null;
            $user->save();

            DB::commit();

            return back()->with('success', 'Affectation grande caisse retirée avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors du retrait de l\'affectation: ' . $e->getMessage());
        }
    }

    /**
     * NOUVELLE MÉTHODE OPTIMISÉE : Obtenir TOUS les paiements en une seule requête
     */
    public function getAllPaiementsForDashboard(Request $request)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé. Seul le Super Admin peut gérer les paiements.'
            ], 403);
        }

        try {
            $annee = $request->input('annee', now()->year);
            $commercialId = $request->input('commercial_id');
            $mois = $request->input('mois', now()->month);
            
            // Construire la requête de base
            $query = User::where('fonction', 'mobile_caissier');
            
            if ($commercialId) {
                $query->where('commercial_id', $commercialId);
            }
            
            // Récupérer tous les mobile caissiers avec leurs paiements OPTIMISÉS
            $mobileCaissiers = $query->with(['commercial', 'grandeCaisseMobile'])
                                    ->get()
                                    ->map(function($user) use ($annee) {
                                        // Utiliser la nouvelle méthode optimisée
                                        $paiementsOptimises = $user->getPaiementsOptimises($annee);
                                        
                                        return [
                                            'id' => $user->id,
                                            'nom' => $user->nomComplet,
                                            'email' => $user->email,
                                            'commercial' => $user->commercial ? $user->commercial->nomComplet : null,
                                            'grande_caisse' => $user->grandeCaisseMobile ? $user->grandeCaisseMobile->nomComplet : null,
                                            'paiements' => $paiementsOptimises,
                                            'est_actif' => $user->est_actif
                                        ];
                                    });
            
            return response()->json([
                'success' => true,
                'mobile_caissiers' => $mobileCaissiers,
                'annee' => $annee,
                'total' => $mobileCaissiers->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur getAllPaiementsForDashboard: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * NOUVELLE MÉTHODE : Obtenir tous les paiements d'un mobile caissier spécifique
     */
    public function getAllPaiementsForUser(User $user)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé.'
            ], 403);
        }

        try {
            $annee = request('annee', now()->year);
            
            // Utiliser la méthode optimisée
            $paiementsOptimises = $user->getPaiementsOptimises($annee);
            
            return response()->json([
                'success' => true,
                'paiements' => $paiementsOptimises,
                'user_id' => $user->id,
                'user_nom' => $user->nomComplet,
                'annee' => $annee
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur getAllPaiementsForUser: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * CORRECTION COMPLÈTE : Méthode unifiée pour valider/annuler les paiements - VERSION OPTIMISÉE
     */
    public function togglePaiement(Request $request)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé. Seul le Super Admin peut gérer les paiements.'
            ], 403);
        }

        $validated = $request->validate([
            'caissier_id' => 'required|exists:users,id',
            'mois' => 'required|integer|min:1|max:12',
            'annee' => 'required|integer|min:2020|max:2030',
            'action' => 'required|in:valider,annuler'
        ]);

        $user = User::find($validated['caissier_id']);
        
        if ($user->fonction !== 'mobile_caissier') {
            return response()->json([
                'success' => false,
                'message' => 'Cette action n\'est possible que pour les mobile caissiers.'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $success = false;
            
            if ($validated['action'] === 'valider') {
                $success = $user->marquerPaiementMensuel($validated['annee'], $validated['mois']);
                $message = 'Paiement mensuel marqué avec succès!';
            } else {
                $success = $user->annulerPaiementMensuel($validated['annee'], $validated['mois']);
                $message = 'Paiement mensuel annulé avec succès!';
            }

            if ($success) {
                DB::commit();
                
                // Recharger l'utilisateur pour obtenir les données fraîches
                $user->refresh();
                
                // Obtenir le paiement mis à jour
                $paiementMisAJour = $user->getPaiementMensuel($validated['annee'], $validated['mois']);
                
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'paiement' => $paiementMisAJour,
                    'action' => $validated['action'],
                    'user_id' => $user->id
                ]);
            } else {
                DB::rollBack();
                
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'opération de paiement.'
                ], 500);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('❌ ERREUR dans togglePaiement: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * NOUVELLE MÉTHODE : Obtenir le statut d'un paiement spécifique
     */
    public function getStatutPaiement(User $user, $annee, $mois)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé.'
            ], 403);
        }

        try {
            $paiement = $user->getPaiementMensuel($annee, $mois);
            
            return response()->json([
                'success' => true,
                'paiement' => $paiement
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur getStatutPaiement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du statut: ' . $e->getMessage()
            ], 500);
        }
    }

    // Helper pour les noms de mois
    private function getNomMois($mois)
    {
        $moisNoms = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];
        
        return $moisNoms[$mois] ?? "Mois $mois";
    }
}