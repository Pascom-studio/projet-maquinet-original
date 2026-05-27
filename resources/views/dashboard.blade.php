@extends('layouts.app')

@section('content')
@php
    use App\Models\Caisse;
    use App\Models\Product;
    use App\Models\Categorie;
    use App\Models\Vente;
    use App\Models\User;
    use App\Models\Commande;
    use App\Models\Table;
    use App\Models\MobileMoneyTransaction;
    use App\Models\MobileMoneyStock;
    
    $user = Auth::user();
    $isCaissier = $user->isCaissier();
    $isGerant = $user->isGerant();
    $isAdmin = $user->isAdmin() || $user->isSuperAdmin();
    $isGerantOrAdmin = $isGerant || $isAdmin;
    $isManager = $user->isManager();
    $isHotesse = $user->isHotesse();
    $isMobileCaissier = $user->isMobileCaissier();
    
    // Récupérer les données pour les filtres (uniquement pour admin/gérant)
    if ($isGerantOrAdmin) {
        $hotesses = User::where('fonction', 'hotesse')
            ->where('admin_id', $user->isAdmin() ? $user->id : $user->admin_id)
            ->get();
        $caissiers = User::where('fonction', 'caissier')
            ->where('admin_id', $user->isAdmin() ? $user->id : $user->admin_id)
            ->get();
    } else {
        $hotesses = collect([]);
        $caissiers = collect([]);
    }

    // NOUVEAU : Récupérer les données pour le Super Admin
    if ($user->isSuperAdmin()) {
        $commerciaux = User::where('fonction', 'commercial')->get();
        $grandesCaissesMobile = User::where('fonction', 'grande_caisse_mobile')->get();
        $mobileCaissiers = User::where('fonction', 'mobile_caissier')->get();
    } else {
        $commerciaux = collect([]);
        $grandesCaissesMobile = collect([]);
        $mobileCaissiers = collect([]);
    }
    
    // Statistiques des tables (uniquement pour admin/gérant)
    if ($isGerantOrAdmin) {
        $tablesTotal = Table::visibleTo($user)->count();
        $tablesAffectees = Table::visibleTo($user)->whereNotNull('user_id')->count();
        $tablesLibres = Table::visibleTo($user)->whereNull('user_id')->count();
    } else {
        $tablesTotal = 0;
        $tablesAffectees = 0;
        $tablesLibres = 0;
    }
    
    // Commandes pour le tableau
    $commandesQuery = Commande::visibleTo($user)
        ->with(['user', 'hotesse', 'table', 'produits.produit'])
        ->where('statut', '!=', 'soldée')
        ->latest();
    
    $commandes = $commandesQuery->take(10)->get();
    
    // Vérifier si la caisse est ouverte (uniquement pour caissier, gérant et admin)
    if ($isCaissier || $isGerant || $isAdmin) {
        $caisse_actuelle = Caisse::where('user_id', $user->id)
                                ->where('statut', 'ouverte')
                                ->first();
    } else {
        $caisse_actuelle = null;
    }

    // Statistiques des produits
    $totalProduits = Product::visibleTo($user)->count();
    $stockTotal = Product::visibleTo($user)->sum('quantite');
    $totalCategories = $isGerantOrAdmin ? Categorie::visibleTo($user)->count() : 0;
    
    // Statistiques des commandes
    if ($user->isSuperAdmin()) {
        $commandesEnCours = Commande::where('statut', 'en cours')->count();
        $chiffreAffairesCommandes = Commande::where('statut', 'soldée')->sum('montant');
    } elseif ($user->isAdmin()) {
        $commandesEnCours = Commande::where('statut', 'en cours')
            ->where('admin_id', $user->id)
            ->count();
        $chiffreAffairesCommandes = Commande::where('statut', 'soldée')
            ->where('admin_id', $user->id)
            ->sum('montant');
    } elseif ($user->isGerant()) {
        $commandesEnCours = Commande::where('statut', 'en cours')
            ->where('admin_id', $user->admin_id)
            ->count();
        $chiffreAffairesCommandes = Commande::where('statut', 'soldée')
            ->where('admin_id', $user->admin_id)
            ->sum('montant');
    } else {
        $commandesEnCours = Commande::where('user_id', $user->id)
            ->where('statut', 'en cours')
            ->count();
        $chiffreAffairesCommandes = Commande::where('user_id', $user->id)
            ->where('statut', 'soldée')
            ->sum('montant');
    }
    
    // Statistiques des ventes
    if ($user->isSuperAdmin()) {
        $ventesAujourdhui = Vente::whereDate('created_at', today())->count();
    } elseif ($user->isAdmin()) {
        $ventesAujourdhui = Vente::whereDate('created_at', today())
            ->where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('user', function($q) use ($user) {
                          $q->where('admin_id', $user->id);
                      });
            })->count();
    } elseif ($user->isGerant()) {
        $ventesAujourdhui = Vente::whereDate('created_at', today())
            ->whereHas('user', function($query) use ($user) {
                $query->where('admin_id', $user->admin_id);
            })->count();
    } else {
        $ventesAujourdhui = Vente::where('user_id', $user->id)->whereDate('created_at', today())->count();
    }

    // Statistiques Mobile Money (uniquement pour Mobile Caissier)
    $statistiquesMobileMoney = [];
    if ($isMobileCaissier) {
        // Transactions aujourd'hui
        $transactionsAujourdhui = MobileMoneyTransaction::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->count();
        
        // Commissions du mois
        $commissionsMois = MobileMoneyTransaction::where('user_id', $user->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('commission');
        
        // Total commissions
        $totalCommissions = MobileMoneyTransaction::where('user_id', $user->id)
            ->sum('commission');
        
        // Soldes par opérateur
        $soldesOperateurs = [];
        $operateurs = ['orange_money', 'telecel_money', 'moov_money', 'coris_money'];
        
        foreach ($operateurs as $operateur) {
            $depots = MobileMoneyTransaction::where('user_id', $user->id)
                ->where('nature', $operateur)
                ->where('type_operation', 'depot')
                ->sum('montant');

            $retraits = MobileMoneyTransaction::where('user_id', $user->id)
                ->where('nature', $operateur)
                ->where('type_operation', 'retrait')
                ->sum('montant');

            $approvisionnements = MobileMoneyStock::where('user_id', $user->id)
                ->where('operateur', $operateur)
                ->where('type_mouvement', 'approvisionnement')
                ->sum('montant');

            $remboursements = MobileMoneyStock::where('user_id', $user->id)
                ->where('operateur', $operateur)
                ->where('type_mouvement', 'remboursement')
                ->sum('montant');

            $solde = ($retraits - $depots) + ($approvisionnements - $remboursements);

            $soldesOperateurs[$operateur] = $solde;
        }

        $statistiquesMobileMoney = [
            'transactions_ajourdhui' => $transactionsAujourdhui,
            'commissions_mois' => $commissionsMois,
            'total_commissions' => $totalCommissions,
            'soldes_operateurs' => $soldesOperateurs
        ];
    }
@endphp


<!-- Loader pour les paiements -->
<div id="paiements-loader" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center h-full">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex flex-col items-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#0b5f37] mb-4"></div>
                <p class="text-gray-700">Chargement des paiements...</p>
            </div>
        </div>
    </div>
</div>
<!-- BOUTON DÉCONNEXION FIXE POUR MOBILE - TOUJOURS VISIBLE -->
<div class="lg:hidden fixed bottom-6 right-6 z-50">
    <form action="{{ route('logout') }}" method="POST" class="logout-form-mobile">
        @csrf
        <button type="submit" 
                class="bg-red-500 hover:bg-red-600 text-white p-4 rounded-full shadow-lg transition-all transform hover:scale-105 flex items-center justify-center mobile-logout-btn"
                onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')"
                title="Déconnexion">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
        </button>
    </form>
</div>

<div class="py-4 sm:py-6">
    <!-- En-tête du dashboard avec bouton thème pour les admins -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[#0b5f37] px-2 sm:px-0">Tableau de Bord</h1>
            <p class="text-gray-600 px-2 sm:px-0">Bienvenue {{ $user->prenom }} {{ $user->nom }} - {{ $user->fonction }}</p>
        </div>
        
        <!-- BOUTON THÈME UNIQUEMENT POUR LES ADMINS -->
        @if($isAdmin)
        <div class="mt-2 sm:mt-0 px-2 sm:px-0">
            <a href="{{ route('admin.theme.edit') }}" 
               class="inline-flex items-center bg-[#8c52ff] hover:bg-[#7a41e6] text-white px-4 py-2 rounded-lg transition-colors text-sm font-medium"
               title="Personnaliser l'apparence de l'application">
                <span class="mr-2">🎨</span>
                Personnaliser le Thème
            </a>
        </div>
        @endif
    </div>

    <!-- Statut de caisse - UNIQUEMENT pour Caissier, Gérant et Admin -->
    @if($isCaissier || $isGerant || $isAdmin)
        @if($caisse_actuelle)
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4 mx-2 sm:mx-0">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                <div class="flex-1">
                    <h3 class="text-base sm:text-lg font-semibold text-green-800">✅ Caisse Ouverte</h3>
                    <p class="text-green-600 text-sm sm:text-base">Solde: <strong>{{ number_format($caisse_actuelle->solde_actuel, 0, ',', ' ') }} FCFA</strong></p>
                    <p class="text-green-600 text-xs sm:text-sm">Ouverte le: {{ $caisse_actuelle->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                    <!-- BOUTON GÉRER LA CAISSE -->
                    <a href="{{ route('caisse.index') }}" class="bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700 text-sm flex items-center justify-center min-w-[120px]">
                        <span class="mr-1">🏦</span>
                        Gérer
                    </a>
                    <!-- BOUTON DÉCONNEXION SÉPARÉ - CACHÉ SUR MOBILE -->
                    <form action="{{ route('logout') }}" method="POST" class="w-full sm:w-auto hidden lg:block">
                        @csrf
                        <button type="submit" 
                                class="w-full bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded text-sm transition-colors flex items-center justify-center min-w-[120px]"
                                onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4 mx-2 sm:mx-0">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                <div class="flex-1">
                    <h3 class="text-base sm:text-lg font-semibold text-yellow-800">🔒 Caisse Fermée</h3>
                    <p class="text-yellow-600 text-sm">Veuillez ouvrir votre caisse pour commencer</p>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    @if($isCaissier || $isGerant)
                        @php
                            $derniere_caisse = \App\Models\Caisse::where('user_id', $user->id)
                                                                ->where('statut', 'fermee')
                                                                ->latest()
                                                                ->first();
                            $solde_ouverture = $derniere_caisse ? $derniere_caisse->solde_fermeture : 0;
                        @endphp
                        <div class="w-full sm:w-auto">
                            <form action="{{ route('caisse.ouvrir') }}" method="POST" class="w-full">
                                @csrf
                                <input type="hidden" name="solde_ouverture" value="{{ $solde_ouverture }}">
                                <div class="bg-blue-50 p-3 rounded border border-blue-200 mb-3">
                                    <p class="text-blue-700 text-sm">
                                        @if($derniere_caisse)
                                            💰 <strong>Solde de fermeture précédent:</strong><br>
                                            <span class="font-bold text-lg">{{ number_format($solde_ouverture, 0, ',', ' ') }} FCFA</span>
                                        @else
                                            🆕 <strong>Première ouverture de caisse</strong>
                                        @endif
                                    </p>
                                </div>
                                <!-- BOUTONS CÔTE À CÔTE CORRIGÉS -->
                                <div class="flex flex-col sm:flex-row gap-2 w-full">
                                    <button type="submit" class="bg-[#0b5f37] text-white px-4 py-2 rounded hover:bg-[#0a4d2c] font-semibold flex-1 text-sm flex items-center justify-center min-w-[140px]">
                                        <span class="mr-1">🏦</span>
                                        Ouvrir Caisse
                                    </button>
                                    <!-- BOUTON DÉCONNEXION SÉPARÉ - CACHÉ SUR MOBILE -->
                                    <div class="flex-1 hidden lg:block">
                                        <form action="{{ route('logout') }}" method="POST" class="h-full">
                                            @csrf
                                            <button type="submit" 
                                                    class="w-full h-full bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded text-sm transition-colors flex items-center justify-center min-w-[120px]"
                                                    onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                                </svg>
                                                Déconnexion
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-2 text-center">
                                    ⓘ Le solde d'ouverture est automatiquement calculé
                                </p>
                            </form>
                        </div>
                    @elseif($isAdmin)
                        <!-- Pour Admin uniquement, afficher le formulaire d'ouverture avec saisie manuelle -->
                        <div class="w-full sm:w-auto">
                            <form action="{{ route('caisse.ouvrir') }}" method="POST" class="w-full">
                                @csrf
                                <div class="space-y-3">
                                    <div>
                                        <label for="solde_ouverture" class="block text-sm font-medium text-gray-700 mb-1">Solde d'ouverture *</label>
                                        <div class="relative">
                                            <input type="number" name="solde_ouverture" id="solde_ouverture" required min="0" step="0.01"
                                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37] pr-10"
                                                   placeholder="0.00">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 text-sm">F</span>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- BOUTONS CÔTE À CÔTE CORRIGÉS -->
                                    <div class="flex flex-col sm:flex-row gap-2 w-full">
                                        <button type="submit" class="bg-[#0b5f37] text-white px-4 py-2 rounded hover:bg-[#0a4d2c] font-semibold flex-1 text-sm flex items-center justify-center min-w-[140px]">
                                            <span class="mr-1">🏦</span>
                                            Ouvrir Caisse
                                        </button>
                                        <!-- BOUTON DÉCONNEXION SÉPARÉ - CACHÉ SUR MOBILE -->
                                        <div class="flex-1 hidden lg:block">
                                            <form action="{{ route('logout') }}" method="POST" class="h-full">
                                                @csrf
                                                <button type="submit" 
                                                        class="w-full h-full bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded text-sm transition-colors flex items-center justify-center min-w-[120px]"
                                                        onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                                    </svg>
                                                    Déconnexion
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    @else
        <!-- BOUTON DÉCONNEXION POUR LES AUTRES UTILISATEURS (Manager, Hôtesse, Mobile Caissier) - CACHÉ SUR MOBILE -->
        <div class="flex justify-end mb-4 px-2 sm:px-0 hidden lg:flex">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" 
                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm transition-colors flex items-center justify-center min-w-[120px]"
                        onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Déconnexion
                </button>
            </form>
        </div>
    @endif

    <!-- Cartes Statistiques -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6 px-2 sm:px-0">
        <!-- Total Produits -->
        @if($isCaissier)
        <div class="bg-white rounded-lg shadow p-3 sm:p-4 border-l-4 border-[#0b5f37]">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Total Produits</h3>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#0b5f37]">{{ $totalProduits }}</p>
                </div>
                <div class="text-lg sm:text-xl lg:text-2xl ml-2">📦</div>
            </div>
            <p class="text-xs text-gray-500 mt-1 truncate">
                Accessibles
            </p>
        </div>
        @else
        <a href="{{ route('products.index') }}" class="block bg-white rounded-lg shadow p-3 sm:p-4 border-l-4 border-[#0b5f37] hover:shadow-md transition-shadow cursor-pointer">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Total Produits</h3>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#0b5f37]">{{ $totalProduits }}</p>
                </div>
                <div class="text-lg sm:text-xl lg:text-2xl ml-2">📦</div>
            </div>
            <p class="text-xs text-gray-500 mt-1 truncate">
                @if($user->isSuperAdmin())
                    Tous produits
                @elseif($user->isAdmin())
                    Votre organisation
                @else
                    Accessibles
                @endif
            </p>
        </a>
        @endif

        <!-- Mobile Money - UNIQUEMENT pour Mobile Caissier -->
        @if($isMobileCaissier)
        <a href="{{ route('mobile-money.index') }}" class="block bg-white rounded-lg shadow p-3 sm:p-4 border-l-4 border-[#25D366] hover:shadow-md transition-shadow cursor-pointer">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Mobile Money</h3>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#25D366]">{{ $statistiquesMobileMoney['transactions_ajourdhui'] ?? 0 }}</p>
                </div>
                <div class="text-lg sm:text-xl lg:text-2xl ml-2">📱</div>
            </div>
            <p class="text-xs text-gray-500 mt-1 truncate">
                Transactions aujourd'hui
            </p>
        </a>
        @endif

        <!-- Tables (uniquement pour admin/gérant) -->
        @if($isGerantOrAdmin)
        <a href="{{ route('tables.index') }}" class="block bg-white rounded-lg shadow p-3 sm:p-4 border-l-4 border-[#8c52ff] hover:shadow-md transition-shadow cursor-pointer">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Total Tables</h3>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#8c52ff]">{{ $tablesTotal }}</p>
                </div>
                <div class="text-lg sm:text-xl lg:text-2xl ml-2">🏪</div>
            </div>
            <p class="text-xs text-gray-500 mt-1 truncate">
                {{ $tablesAffectees }} affectées
            </p>
        </a>
        @endif

        <!-- Commandes en Cours -->
        <a href="#commandes-section" class="block bg-white rounded-lg shadow p-3 sm:p-4 border-l-4 border-[#ff6b6b] hover:shadow-md transition-shadow cursor-pointer">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Commandes en Cours</h3>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#ff6b6b]">{{ $commandesEnCours }}</p>
                </div>
                <div class="text-lg sm:text-xl lg:text-2xl ml-2">📋</div>
            </div>
            <p class="text-xs text-gray-500 mt-1 truncate">
                @if($user->isSuperAdmin())
                    Toutes commandes
                @elseif($user->isAdmin())
                    Organisation
                @else
                    Vos commandes
                @endif
            </p>
        </a>

        <!-- Ventes du jour -->
        <a href="{{ route('ventes.index') }}" class="block bg-white rounded-lg shadow p-3 sm:p-4 border-l-4 border-[#cb6ce6] hover:shadow-md transition-shadow cursor-pointer">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Ventes Jour</h3>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#cb6ce6]">{{ $ventesAujourdhui }}</p>
                </div>
                <div class="text-lg sm:text-xl lg:text-2xl ml-2">📊</div>
            </div>
            <p class="text-xs text-gray-500 mt-1 truncate">
                @if($user->isSuperAdmin())
                    Toutes
                @elseif($user->isAdmin())
                    Organisation
                @elseif($user->isGerant())
                    Établissement
                @else
                    Personnelles
                @endif
            </p>
        </a>
    </div>

    <!-- NOUVELLES CARTES POUR SUPER ADMIN - Mobile Caissiers et Grandes Caisses -->
@if($user->isSuperAdmin())
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6 px-2 sm:px-0">
    <!-- Total Mobile Caissiers -->
    <a href="{{ route('users.index') }}?fonction=mobile_caissier" class="block bg-white rounded-lg shadow p-3 sm:p-4 border-l-4 border-[#25D366] hover:shadow-md transition-shadow cursor-pointer">
        <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
                <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Mobile Caissiers</h3>
                <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#25D366]">{{ $totalMobileCaissiers ?? 0 }}</p>
            </div>
            <div class="text-lg sm:text-xl lg:text-2xl ml-2">📱</div>
        </div>
        <p class="text-xs text-gray-500 mt-1 truncate">
            Total des comptes Mobile Money
        </p>
    </a>

    <!-- Total Grandes Caisses Mobile -->
    <a href="{{ route('users.index') }}?fonction=grande_caisse_mobile" class="block bg-white rounded-lg shadow p-3 sm:p-4 border-l-4 border-[#8c52ff] hover:shadow-md transition-shadow cursor-pointer">
        <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
                <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Grandes Caisses</h3>
                <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#8c52ff]">{{ $totalGrandesCaisses ?? 0 }}</p>
            </div>
            <div class="text-lg sm:text-xl lg:text-2xl ml-2">🏦</div>
        </div>
        <p class="text-xs text-gray-500 mt-1 truncate">
            Grandes caisses mobile
        </p>
    </a>

    <!-- Mobile Caissiers Actifs -->
    <div class="block bg-white rounded-lg shadow p-3 sm:p-4 border-l-4 border-[#10b981] hover:shadow-md transition-shadow cursor-pointer">
        <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
                <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Caissiers Actifs</h3>
                <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#10b981]">
                    @php
                        $mobileCaissiersActifs = \App\Models\User::where('fonction', 'mobile_caissier')
                            ->where('est_actif', true)
                            ->count();
                    @endphp
                    {{ $mobileCaissiersActifs }}
                </p>
            </div>
            <div class="text-lg sm:text-xl lg:text-2xl ml-2">✅</div>
        </div>
        <p class="text-xs text-gray-500 mt-1 truncate">
            Mobile caissiers activés
        </p>
    </div>

    <!-- Taux d'Affectation -->
    <div class="block bg-white rounded-lg shadow p-3 sm:p-4 border-l-4 border-[#f59e0b] hover:shadow-md transition-shadow cursor-pointer">
        <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
                <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Taux Affectation</h3>
                <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#f59e0b]">
                    @php
                        $totalAffectes = \App\Models\User::where('fonction', 'mobile_caissier')
                            ->whereNotNull('commercial_id')
                            ->count();
                        $tauxAffectation = $totalMobileCaissiers > 0 ? round(($totalAffectes / $totalMobileCaissiers) * 100, 0) : 0;
                    @endphp
                    {{ $tauxAffectation }}%
                </p>
            </div>
            <div class="text-lg sm:text-xl lg:text-2xl ml-2">📊</div>
        </div>
        <p class="text-xs text-gray-500 mt-1 truncate">
            Mobile caissiers affectés
        </p>
    </div>
</div>
@endif
    <!-- Deuxième ligne de cartes (uniquement pour admin/gérant et mobile caissier) -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6 px-2 sm:px-0">
        @if($isGerantOrAdmin)
        <!-- Stock Total -->
        <div class="bg-white rounded-lg shadow p-3 sm:p-4 border-l-4 border-[#4ecdc4]">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Stock Total</h3>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#4ecdc4]">{{ $stockTotal }}</p>
                </div>
                <div class="text-lg sm:text-xl lg:text-2xl ml-2">📈</div>
            </div>
            <p class="text-xs text-gray-500 mt-1 truncate">Unités disponibles</p>
        </div>

        <!-- Tables Affectées -->
        <div class="bg-white rounded-lg shadow p-3 sm:p-4 border-l-4 border-[#10b981]">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Tables Affectées</h3>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#10b981]">{{ $tablesAffectees }}</p>
                </div>
                <div class="text-lg sm:text-xl lg:text-2xl ml-2">👩‍💼</div>
            </div>
            <p class="text-xs text-gray-500 mt-1 truncate">Avec hôtesse</p>
        </div>

        <!-- Tables Libres -->
        <div class="bg-white rounded-lg shadow p-3 sm:p-4 border-l-4 border-[#6b7280]">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Tables Libres</h3>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#6b7280]">{{ $tablesLibres }}</p>
                </div>
                <div class="text-lg sm:text-xl lg:text-2xl ml-2">🆓</div>
            </div>
            <p class="text-xs text-gray-500 mt-1 truncate">À affecter</p>
        </div>

        <!-- CA Commandes -->
        <div class="bg-white rounded-lg shadow p-3 sm:p-4 border-l-4 border-[#f59e0b]">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">CA Commandes</h3>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#f59e0b]">{{ number_format($chiffreAffairesCommandes, 0, ',', ' ') }}</p>
                </div>
                <div class="text-lg sm:text-xl lg:text-2xl ml-2">💰</div>
            </div>
            <p class="text-xs text-gray-500 mt-1 truncate">FCFA</p>
        </div>
        @elseif($isMobileCaissier)
        <!-- Cartes spécifiques pour Mobile Caissier -->
        <!-- Commissions du Mois -->
        <div class="bg-white rounded-lg shadow p-3 sm:p-4 border-l-4 border-[#FF6B35]">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Commissions Mois</h3>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#FF6B35]">{{ number_format($statistiquesMobileMoney['commissions_mois'] ?? 0, 0, ',', ' ') }}</p>
                </div>
                <div class="text-lg sm:text-xl lg:text-2xl ml-2">💸</div>
            </div>
            <p class="text-xs text-gray-500 mt-1 truncate">FCFA</p>
        </div>

        <!-- Total Commissions -->
        <div class="bg-white rounded-lg shadow p-3 sm:p-4 border-l-4 border-[#9C27B0]">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Total Commissions</h3>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#9C27B0]">{{ number_format($statistiquesMobileMoney['total_commissions'] ?? 0, 0, ',', ' ') }}</p>
                </div>
                <div class="text-lg sm:text-xl lg:text-2xl ml-2">💰</div>
            </div>
            <p class="text-xs text-gray-500 mt-1 truncate">FCFA cumulées</p>
        </div>

        <!-- Solde Orange Money -->
        <div class="bg-white rounded-lg shadow p-3 sm:p-4 border-l-4 border-[#FF9800]">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Orange Money</h3>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#FF9800]">{{ number_format($statistiquesMobileMoney['soldes_operateurs']['orange_money'] ?? 0, 0, ',', ' ') }}</p>
                </div>
                <div class="text-lg sm:text-xl lg:text-2xl ml-2">🟠</div>
            </div>
            <p class="text-xs text-gray-500 mt-1 truncate">FCFA</p>
        </div>

        <!-- Solde Moov Money -->
        <div class="bg-white rounded-lg shadow p-3 sm:p-4 border-l-4 border-[#00BCD4]">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Moov Money</h3>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#00BCD4]">{{ number_format($statistiquesMobileMoney['soldes_operateurs']['moov_money'] ?? 0, 0, ',', ' ') }}</p>
                </div>
                <div class="text-lg sm:text-xl lg:text-2xl ml-2">🔵</div>
            </div>
            <p class="text-xs text-gray-500 mt-1 truncate">FCFA</p>
        </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 px-2 sm:px-0">
        <!-- Actions Rapides -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <h3 class="text-lg sm:text-xl font-semibold text-[#0b5f37] mb-3 sm:mb-4">🚀 Actions Rapides</h3>
            <div class="space-y-2 sm:space-y-3">
                <!-- Actions Mobile Money - UNIQUEMENT pour Mobile Caissier -->
                @if($isMobileCaissier)
                    <a href="{{ route('mobile-money.create') }}" class="flex items-center justify-center w-full bg-[#25D366] text-white py-2 sm:py-3 px-3 sm:px-4 rounded hover:bg-[#20bd5a] transition-colors text-sm sm:text-base">
                        <span class="mr-2 text-base">💰</span>
                        <span class="truncate">Nouvelle Transaction</span>
                    </a>
                    
                    <a href="{{ route('mobile-money.historique') }}" class="flex items-center justify-center w-full bg-[#0b5f37] text-white py-2 sm:py-3 px-3 sm:px-4 rounded hover:bg-[#0a4d2c] transition-colors text-sm sm:text-base">
                        <span class="mr-2 text-base">📱</span>
                        <span class="truncate">Historique Transactions</span>
                    </a>
                    
                    <a href="{{ route('mobile-money.historique-commission') }}" class="flex items-center justify-center w-full bg-[#FF6B35] text-white py-2 sm:py-3 px-3 sm:px-4 rounded hover:bg-[#e55a2b] transition-colors text-sm sm:text-base">
                        <span class="mr-2 text-base">💸</span>
                        <span class="truncate">Commissions</span>
                    </a>
                    
                    <a href="{{ route('mobile-money.stock') }}" class="flex items-center justify-center w-full bg-[#9C27B0] text-white py-2 sm:py-3 px-3 sm:px-4 rounded hover:bg-[#8a24a0] transition-colors text-sm sm:text-base">
                        <span class="mr-2 text-base">📦</span>
                        <span class="truncate">Gestion du Stock</span>
                    </a>
                @endif

                <!-- Ventes et Commandes - BLOQUÉ pour Manager et Hôtesse, AUTORISÉ pour Caissier, Gérant et Admin -->
                @if($user->isCaissier() || $user->isGerant() || $user->isAdmin())
                    @if($caisse_actuelle || $user->isGerant() || $user->isAdmin())
                        <a href="{{ route('ventes.create') }}" class="flex items-center justify-center w-full bg-[#0b5f37] text-white py-2 sm:py-3 px-3 sm:px-4 rounded hover:bg-[#0a4d2c] transition-colors text-sm sm:text-base">
                            <span class="mr-2 text-base">🛒</span>
                            <span class="truncate">Nouvelle Vente Multiple</span>
                        </a>
                        
                        <!-- Nouvelle Commande -->
                        <a href="{{ route('commandes.create') }}" class="flex items-center justify-center w-full bg-[#ff6b6b] text-white py-2 sm:py-3 px-3 sm:px-4 rounded hover:bg-[#ff5252] transition-colors text-sm sm:text-base">
                            <span class="mr-2 text-base">➕</span>
                            <span class="truncate">Nouvelle Commande</span>
                        </a>
                    @else
                        <button class="flex items-center justify-center w-full bg-gray-400 text-white py-2 sm:py-3 px-3 sm:px-4 rounded cursor-not-allowed opacity-50 text-sm sm:text-base" 
                                onclick="alert('Veuillez ouvrir votre caisse d\\'abord')">
                            <span class="mr-2 text-base">🛒</span>
                            <span class="truncate">Nouvelle Vente</span>
                        </button>
                        
                        <button class="flex items-center justify-center w-full bg-gray-400 text-white py-2 sm:py-3 px-3 sm:px-4 rounded cursor-not-allowed opacity-50 text-sm sm:text-base" 
                                onclick="alert('Veuillez ouvrir votre caisse d\\'abord pour créer une commande')">
                            <span class="mr-2 text-base">➕</span>
                            <span class="truncate">Nouvelle Commande</span>
                        </button>
                    @endif
                    
                    <a href="{{ route('caisse.index') }}" class="flex items-center justify-center w-full bg-[#ee8f13] text-white py-2 sm:py-3 px-3 sm:px-4 rounded hover:bg-[#d67f11] transition-colors text-sm sm:text-base">
                        <span class="mr-2 text-base">🏦</span>
                        <span class="truncate">Gestion de Caisse</span>
                    </a>
                @endif

                <!-- Gérant et Admin peuvent gérer produits, catégories, mouvements -->
                @if($user->isGerant() || $user->isAdmin())
                    <a href="{{ route('products.create') }}" class="flex items-center justify-center w-full bg-[#0b5f37] text-white py-2 sm:py-3 px-3 sm:px-4 rounded hover:bg-[#0a4d2c] transition-colors text-sm sm:text-base">
                        <span class="mr-2 text-base">➕</span>
                        <span class="truncate">Ajouter un Produit</span>
                    </a>
                    
                    <a href="{{ route('categories.create') }}" class="flex items-center justify-center w-full bg-[#ee8f13] text-white py-2 sm:py-3 px-3 sm:px-4 rounded hover:bg-[#d67f11] transition-colors text-sm sm:text-base">
                        <span class="mr-2 text-base">🏷️</span>
                        <span class="truncate">Ajouter Catégorie</span>
                    </a>
                    
                    <!-- Gestion des tables (uniquement admin/gérant) -->
                    @if($isGerantOrAdmin)
                    <a href="{{ route('tables.create') }}" class="flex items-center justify-center w-full bg-[#8c52ff] text-white py-2 sm:py-3 px-3 sm:px-4 rounded hover:bg-[#7a41e6] transition-colors text-sm sm:text-base">
                        <span class="mr-2 text-base">🏪</span>
                        <span class="truncate">Ajouter une Table</span>
                    </a>
                    @endif
                    
                    <a href="{{ route('mouvements.create') }}" class="flex items-center justify-center w-full bg-[#cb6ce6] text-white py-2 sm:py-3 px-3 sm:px-4 rounded hover:bg-[#b85acf] transition-colors text-sm sm:text-base">
                        <span class="mr-2 text-base">📦</span>
                        <span class="truncate">Mouvement Stock</span>
                    </a>

                    <a href="{{ route('audit.index') }}" class="flex items-center justify-center w-full bg-[#6366f1] text-white py-2 sm:py-3 px-3 sm:px-4 rounded hover:bg-[#5b5cd9] transition-colors text-sm sm:text-base">
                        <span class="mr-2 text-base">📋</span>
                        <span class="truncate">Voir l'Audit</span>
                    </a>
                @endif

                <!-- Lien vers la liste des ventes pour tous -->
                <a href="{{ route('ventes.index') }}" class="flex items-center justify-center w-full bg-[#0b5f37] bg-opacity-10 text-[#0b5f37] py-2 sm:py-3 px-3 sm:px-4 rounded hover:bg-opacity-20 transition-colors border border-[#0b5f37] border-opacity-30 text-sm sm:text-base">
                    <span class="mr-2 text-base">📊</span>
                    <span class="truncate">Toutes les Ventes</span>
                </a>

                <!-- Lien vers la liste des commandes pour tous -->
                <a href="#commandes-section" class="flex items-center justify-center w-full bg-[#ff6b6b] bg-opacity-10 text-[#ff6b6b] py-2 sm:py-3 px-3 sm:px-4 rounded hover:bg-opacity-20 transition-colors border border-[#ff6b6b] border-opacity-30 text-sm sm:text-base">
                    <span class="mr-2 text-base">📋</span>
                    <span class="truncate">Toutes les Commandes</span>
                </a>

                <!-- Lien vers la gestion des tables (uniquement admin/gérant) -->
                @if($isGerantOrAdmin)
                <a href="{{ route('tables.index') }}" class="flex items-center justify-center w-full bg-[#8c52ff] bg-opacity-10 text-[#8c52ff] py-2 sm:py-3 px-3 sm:px-4 rounded hover:bg-opacity-20 transition-colors border border-[#8c52ff] border-opacity-30 text-sm sm:text-base">
                    <span class="mr-2 text-base">🏪</span>
                    <span class="truncate">Gérer les Tables</span>
                </a>
                @endif
            </div>
        </div>

        <!-- Commandes Récentes -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg sm:text-xl font-semibold text-[#0b5f37]">📋 Commandes Récentes</h3>
                <a href="#commandes-section" class="text-sm text-[#0b5f37] hover:text-[#0a4d2c] font-medium">
                    Voir tout
                </a>
            </div>
            
            @php
                // Récupérer les commandes récentes
                if ($user->isSuperAdmin()) {
                    $commandesRecentes = Commande::with(['user', 'hotesse', 'table', 'produits.produit'])
                        ->latest()
                        ->take(5)
                        ->get();
                } elseif ($user->isAdmin()) {
                    $commandesRecentes = Commande::with(['user', 'hotesse', 'table', 'produits.produit'])
                        ->where('admin_id', $user->id)
                        ->latest()
                        ->take(5)
                        ->get();
                } elseif ($user->isGerant()) {
                    $commandesRecentes = Commande::with(['user', 'hotesse', 'table', 'produits.produit'])
                        ->where('admin_id', $user->admin_id)
                        ->latest()
                        ->take(5)
                        ->get();
                } else {
                    $commandesRecentes = Commande::with(['user', 'hotesse', 'table', 'produits.produit'])
                        ->where('user_id', $user->id)
                        ->latest()
                        ->take(5)
                        ->get();
                }
            @endphp
            
            <div class="space-y-3">
                @foreach($commandesRecentes as $commande)
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium text-gray-900 truncate">
                                    {{ $commande->numero_commande }}
                                </span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                    {{ $commande->statut === 'en cours' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $commande->statut === 'soldée' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $commande->statut === 'annulée' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ $commande->statut }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1 truncate">
                                {{ $commande->produits->count() }} produit(s) • {{ $commande->table->nom_complet ?? 'Table supprimée' }}
                                @if($user->isSuperAdmin() || $user->isAdmin())
                                    • {{ $commande->user->prenom ?? 'Utilisateur supprimé' }}
                                @endif
                            </div>
                        </div>
                        <div class="text-right ml-2">
                            <div class="text-sm font-bold text-[#0b5f37] whitespace-nowrap">
                                {{ number_format($commande->montant, 0, ',', ' ') }} F
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $commande->created_at->format('H:i') }}
                            </div>
                        </div>
                    </div>
                @endforeach
                
                @if($commandesRecentes->count() == 0)
                    <div class="text-center py-4">
                        <div class="text-gray-400 text-2xl mb-2">📋</div>
                        <p class="text-gray-500 text-sm">Aucune commande récente</p>
                        @if($user->isCaissier() || $user->isGerant() || $user->isAdmin())
                            @if($caisse_actuelle || $user->isGerant() || $user->isAdmin())
                                <a href="{{ route('commandes.create') }}" class="text-[#0b5f37] hover:text-[#0a4d2c] text-sm font-medium mt-2 inline-block">
                                    Créer une commande
                                </a>
                            @else
                                <button onclick="alert('Veuillez ouvrir votre caisse d\\'abord pour créer une commande')" class="text-gray-400 text-sm font-medium mt-2 inline-block cursor-not-allowed">
                                    Créer une commande
                                </button>
                            @endif
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Section Tables (uniquement pour admin/gérant) -->
    @if($isGerantOrAdmin)
    <div class="mt-8 px-2 sm:px-0">
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-lg sm:text-xl font-semibold text-[#0b5f37]">🏪 Gestion des Tables</h3>
                    <div class="mt-2 sm:mt-0 flex space-x-2">
                        @if($user->isAdmin() || $user->isGerant())
                            <a href="{{ route('tables.create') }}" class="bg-[#0b5f37] text-white px-3 sm:px-4 py-2 rounded hover:bg-[#0a4d2c] text-sm">
                                + Nouvelle Table
                            </a>
                        @endif
                        <a href="{{ route('tables.index') }}" class="bg-[#8c52ff] text-white px-3 sm:px-4 py-2 rounded hover:bg-[#7a41e6] text-sm">
                            Voir toutes les tables
                        </a>
                    </div>
                </div>
            </div>

            <!-- Liste des tables récentes -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Table</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hôtesse Affectée</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commandes en Cours</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($tablesRecentes = Table::visibleTo($user)->with(['user', 'commandesEnCours'])->latest()->take(8)->get() as $table)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $table->nom_complet }}</div>
                                    <div class="text-xs text-gray-500">#{{ $table->numero }}</div>
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $table->estAffectee() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $table->statut_formatted }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($table->estAffectee())
                                        {{ $table->user->prenom }} {{ $table->user->nom }}
                                    @else
                                        <span class="text-gray-400">Non affectée</span>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $table->commandesEnCours->count() }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-1">
                                        <a href="{{ route('tables.show', $table) }}" class="text-blue-600 hover:text-blue-900" title="Voir">
                                            👁️
                                        </a>
                                        @if($user->isAdmin() || $user->isGerant())
                                            @if($table->estAffectee())
                                                <form action="{{ route('tables.liberer', $table) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-yellow-600 hover:text-yellow-900" title="Libérer">
                                                        🔓
                                                    </button>
                                                </form>
                                            @else
                                                <button type="button" 
                                                        onclick="afficherModalAffectation({{ $table->id }})"
                                                        class="text-green-600 hover:text-green-900"
                                                        title="Affecter">
                                                    👩‍💼
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 sm:px-6 py-4 text-center text-sm text-gray-500">
                                    Aucune table trouvée
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tablesRecentes->count() > 0)
            <div class="px-4 sm:px-6 py-4 bg-gray-50 border-t border-gray-200">
                <a href="{{ route('tables.index') }}" class="text-sm text-[#0b5f37] hover:text-[#0a4d2c] font-medium">
                    Voir toutes les tables →
                </a>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Section Commandes en Cours -->
    <div id="commandes-section" class="mt-8 px-2 sm:px-0">
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-lg sm:text-xl font-semibold text-[#0b5f37]">📋 Commandes en Cours</h3>
                    <div class="mt-2 sm:mt-0 flex space-x-2">
                        @if($user->isCaissier() || $user->isGerant() || $user->isAdmin())
                            @if($caisse_actuelle || $user->isGerant() || $user->isAdmin())
                                <a href="{{ route('commandes.create') }}" class="bg-[#0b5f37] text-white px-3 sm:px-4 py-2 rounded hover:bg-[#0a4d2c] text-sm">
                                    Nouvelle Commande
                                </a>
                            @else
                                <button class="bg-gray-400 text-white px-3 sm:px-4 py-2 rounded cursor-not-allowed opacity-50 text-sm"
                                        onclick="alert('Veuillez ouvrir votre caisse d\\'abord pour créer une commande')">
                                    Nouvelle Commande
                                </button>
                            @endif
                        @endif
                        <a href="{{ route('commandes.soldees') }}" class="bg-[#ee8f13] text-white px-3 sm:px-4 py-2 rounded hover:bg-[#d67f11] text-sm">
                            Commandes Soldées
                        </a>
                    </div>
                </div>
            </div>

            <!-- Liste des commandes -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Commande</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Table</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hôtesse</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produits</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Caissier</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($commandes as $commande)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $commande->numero_commande }}</div>
                                    @if($commande->notes)
                                        <div class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit($commande->notes, 30) }}</div>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $commande->table->nom_complet ?? 'N/A' }}</td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $commande->hotesse->prenom ?? 'N/A' }} {{ $commande->hotesse->nom ?? '' }}</td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $commande->produits->count() }} produit(s)
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ number_format($commande->montant, 0, ',', ' ') }} F</td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $commande->statut === 'en cours' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $commande->statut === 'soldée' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $commande->statut === 'annulée' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ $commande->statut_formatted }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $commande->user->prenom ?? 'N/A' }} {{ $commande->user->nom ?? '' }}</td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $commande->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-1">
                                        <a href="{{ route('commandes.show', $commande) }}" class="text-blue-600 hover:text-blue-900" title="Voir">
                                            👁️
                                        </a>
                                        
                                        @if($commande->peutEtreModifieePar($user))
                                            <a href="{{ route('commandes.edit', $commande) }}" class="text-yellow-600 hover:text-yellow-900" title="Modifier">
                                                ✏️
                                            </a>
                                        @endif
                                        
                                        @if($commande->statut !== 'soldée' && ($user->isAdmin() || $user->isGerant() || $commande->user_id === $user->id))
                                            <form action="{{ route('commandes.solder', $commande) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-900" title="Solder" onclick="return confirm('Soldée cette commande?')">
                                                    ✅
                                                </button>
                                            </form>
                                        @endif
                                        
                                        @if($commande->peutEtreModifieePar($user))
                                            <form action="{{ route('commandes.destroy', $commande) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" title="Supprimer" onclick="return confirm('Supprimer cette commande?')">
                                                    🗑️
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 sm:px-6 py-4 text-center text-sm text-gray-500">
                                    Aucune commande trouvée
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($commandes->count() > 0)
            <div class="px-4 sm:px-6 py-4 bg-gray-50 border-t border-gray-200">
                <a href="{{ route('commandes.index') }}" class="text-sm text-[#0b5f37] hover:text-[#0a4d2c] font-medium">
                    Voir toutes les commandes →
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- NOUVELLE SECTION : Gestion des Mobile Caissiers pour Super Admin -->
    @if($user->isSuperAdmin())
    <div class="mt-8 px-2 sm:px-0">
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-lg sm:text-xl font-semibold text-[#0b5f37]">👥 Gestion des Mobile Caissiers</h3>
                    <div class="mt-2 sm:mt-0 flex space-x-2">
                        <a href="{{ route('users.create') }}" class="bg-[#0b5f37] text-white px-3 sm:px-4 py-2 rounded hover:bg-[#0a4d2c] text-sm">
                            + Nouveau Mobile Caissier
                        </a>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-6">
                <!-- Affectation aux Commerciaux -->
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">🏢 Affectation aux Commerciaux</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- NOUVELLE SECTION : Gestion des Mobile Caissiers pour Super Admin -->
@if($user->isSuperAdmin())
<div class="mt-8 px-2 sm:px-0">
    <div class="bg-white rounded-lg shadow">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-lg sm:text-xl font-semibold text-[#0b5f37]">👥 Gestion des Mobile Caissiers</h3>
                <div class="mt-2 sm:mt-0 flex space-x-2">
                    <a href="{{ route('users.create') }}" class="bg-[#0b5f37] text-white px-3 sm:px-4 py-2 rounded hover:bg-[#0a4d2c] text-sm">
                        + Nouveau Mobile Caissier
                    </a>
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-6">
            <!-- Affectation aux Commerciaux -->
            <div class="mb-6">
                <h4 class="text-lg font-semibold text-gray-800 mb-4">🏢 Affectation aux Commerciaux</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($commerciaux as $commercial)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h5 class="font-semibold text-gray-700">{{ $commercial->prenom }} {{ $commercial->name }}</h5>
                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Commercial</span>
                            </div>
                            
                            @php
                                // CORRECTION : Utiliser la variable définie dans le contrôleur
                                $mobileCaissiersCommercial = $commerciauxAvecCaissiers[$commercial->id] ?? collect();
                            @endphp
                            
                            <div class="space-y-2">
                                @if($mobileCaissiersCommercial->count() > 0)
                                    @foreach($mobileCaissiersCommercial as $caissier)
                                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                            <div>
                                                <span class="font-medium">{{ $caissier->prenom }} {{ $caissier->name }}</span>
                                                <span class="text-xs text-gray-500 ml-2">{{ $caissier->email }}</span>
                                            </div>
                                            <div class="flex space-x-2">
                                                <!-- CORRECTION : Formulaire avec ID unique et bonne route -->
                                                <form id="form-retirer-commercial-{{ $caissier->id }}" 
                                                      action="{{ route('users.retirer-affectation', $caissier) }}" 
                                                      method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="text-red-600 hover:text-red-800 text-sm" 
                                                            title="Retirer"
                                                            onclick="return confirmRetirerAffectation(event, {{ $caissier->id }})">
                                                        🗑️
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-gray-500 text-sm text-center py-2">Aucun mobile caissier assigné</p>
                                @endif
                            </div>
                            
                            <!-- Formulaire d'ajout -->
                            <form action="{{ route('users.affecter-commercial') }}" method="POST" class="mt-3">
                                @csrf
                                <input type="hidden" name="commercial_id" value="{{ $commercial->id }}">
                                <div class="flex space-x-2">
                                    <select name="mobile_caissier_id" class="flex-1 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37]">
                                        <option value="">Sélectionner un mobile caissier</option>
                                        @foreach($mobileCaissiers->where('commercial_id', '!=', $commercial->id)->whereNull('commercial_id') as $caissier)
                                            <option value="{{ $caissier->id }}">{{ $caissier->prenom }} {{ $caissier->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="bg-[#0b5f37] text-white px-3 py-2 rounded text-sm hover:bg-[#0a4d2c]">
                                        Affecter
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Regroupement dans les Grandes Caisses Mobile -->
            <div>
                <h4 class="text-lg font-semibold text-gray-800 mb-4">🏦 Regroupement dans les Grandes Caisses Mobile</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($grandesCaissesMobile as $grandeCaisse)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h5 class="font-semibold text-gray-700">{{ $grandeCaisse->prenom }} {{ $grandeCaisse->name }}</h5>
                                <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded">Grande Caisse</span>
                            </div>
                            
                            @php
                                // CORRECTION : Utiliser la variable définie dans le contrôleur
                                $comptesRegroupes = $grandesCaissesAvecComptes[$grandeCaisse->id] ?? collect();
                            @endphp
                            
                            <div class="space-y-2">
                                @if($comptesRegroupes->count() > 0)
                                    @foreach($comptesRegroupes as $compte)
                                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                            <div>
                                                <span class="font-medium">{{ $compte->prenom }} {{ $compte->name }}</span>
                                                <span class="text-xs text-gray-500 ml-2">{{ $compte->email }}</span>
                                            </div>
                                            <div class="flex space-x-2">
                                                <form id="form-retirer-grande-caisse-{{ $compte->id }}" 
                                                      action="{{ route('users.retirer-affectation-grande-caisse', $compte) }}" 
                                                      method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="text-red-600 hover:text-red-800 text-sm" 
                                                            title="Retirer"
                                                            onclick="return confirmRetirerAffectation(event, {{ $compte->id }})">
                                                        🗑️
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-gray-500 text-sm text-center py-2">Aucun compte regroupé</p>
                                @endif
                            </div>
                            
                            <!-- Formulaire d'ajout -->
                            <form action="{{ route('users.affecter-grande-caisse') }}" method="POST" class="mt-3">
                                @csrf
                                <input type="hidden" name="grande_caisse_id" value="{{ $grandeCaisse->id }}">
                                <div class="flex space-x-2">
                                    <select name="mobile_caissier_id" class="flex-1 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37]">
                                        <option value="">Sélectionner un mobile caissier</option>
                                        @foreach($mobileCaissiers->where('grande_caisse_id', '!=', $grandeCaisse->id)->whereNull('grande_caisse_id') as $caissier)
                                            <option value="{{ $caissier->id }}">{{ $caissier->prenom }} {{ $caissier->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="bg-[#0b5f37] text-white px-3 py-2 rounded text-sm hover:bg-[#0a4d2c]">
                                        Regrouper
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Liste des Mobile Caissiers non affectés -->
            <div class="mt-6">
                <h4 class="text-lg font-semibold text-gray-800 mb-4">📋 Mobile Caissiers non affectés</h4>
                <div class="bg-gray-50 rounded-lg p-4">
                    @php
                        $mobileCaissiersNonAffectes = $mobileCaissiers->whereNull('commercial_id')->whereNull('grande_caisse_id');
                    @endphp
                    
                    @if($mobileCaissiersNonAffectes->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($mobileCaissiersNonAffectes as $caissier)
                                <div class="flex items-center justify-between p-3 bg-white rounded border">
                                    <div>
                                        <span class="font-medium">{{ $caissier->prenom }} {{ $caissier->name }}</span>
                                        <span class="text-xs text-gray-500 ml-2">{{ $caissier->email }}</span>
                                    </div>
                                    <span class="text-xs text-yellow-600 bg-yellow-100 px-2 py-1 rounded">Non affecté</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-sm text-center py-4">Tous les mobile caissiers sont affectés</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif
                    </div>
                </div>

                <!-- Regroupement dans les Grandes Caisses Mobile -->
                <div>
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">🏦 Regroupement dans les Grandes Caisses Mobile</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                   @foreach(($comptesRegroupes ?? []) as $compte)
    <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
        <div>
            <span class="font-medium">{{ $compte->prenom }} {{ $compte->name }}</span>
            <span class="text-xs text-gray-500 ml-2">{{ $compte->email }}</span>
        </div>
        <div class="flex space-x-2">
            <form id="form-retirer-grande-caisse-{{ $compte->id }}" 
                  action="{{ route('users.retirer-affectation-grande-caisse', $compte) }}" 
                  method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="text-red-600 hover:text-red-800 text-sm" 
                        title="Retirer"
                        onclick="return confirmRetirerAffectation(event, {{ $compte->id }})">
                    🗑️
                </button>
            </form>
        </div>
    </div>
@endforeach
                    </div>
                </div>

                <!-- Liste des Mobile Caissiers non affectés -->
                <div class="mt-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">📋 Mobile Caissiers non affectés</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        @php
                            $mobileCaissiersNonAffectes = $mobileCaissiers->whereNull('commercial_id')->whereNull('grande_caisse_id');
                        @endphp
                        
                        @if($mobileCaissiersNonAffectes->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($mobileCaissiersNonAffectes as $caissier)
                                    <div class="flex items-center justify-between p-3 bg-white rounded border">
                                        <div>
                                            <span class="font-medium">{{ $caissier->prenom }} {{ $caissier->name }}</span>
                                            <span class="text-xs text-gray-500 ml-2">{{ $caissier->email }}</span>
                                        </div>
                                        <span class="text-xs text-yellow-600 bg-yellow-100 px-2 py-1 rounded">Non affecté</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-sm text-center py-4">Tous les mobile caissiers sont affectés</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Section Paiements des Mobile Caissiers pour Super Admin -->
    @if($user->isSuperAdmin())
    <div class="mt-8 px-2 sm:px-0">
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-lg sm:text-xl font-semibold text-[#0b5f37]">💰 Gestion des Paiements Mobile Caissiers</h3>
                    <p class="text-sm text-gray-600 mt-2 sm:mt-0">Cliquez sur les cercles pour valider/annuler les paiements</p>
                </div>
            </div>

            <div class="p-4 sm:p-6">
                <!-- Filtres -->
                <div class="mb-6">
                    <form method="GET" action="{{ url()->current() }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Commercial</label>
                            <select name="commercial_id" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                                <option value="">Tous les commerciaux</option>
                                @foreach($commerciaux as $commercial)
                                    <option value="{{ $commercial->id }}" {{ request('commercial_id') == $commercial->id ? 'selected' : '' }}>
                                        {{ $commercial->prenom }} {{ $commercial->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mois</label>
                            <select name="mois" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                                @php
                                    $moisNoms = [
                                        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 
                                        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 
                                        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
                                    ];
                                @endphp
                                @foreach($moisNoms as $numero => $nom)
                                    <option value="{{ $numero }}" {{ request('mois', now()->month) == $numero ? 'selected' : '' }}>
                                        {{ $nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Année</label>
                            <input type="number" name="annee" value="{{ request('annee', now()->year) }}" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        </div>
                        
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="bg-[#0b5f37] text-white px-4 py-2 rounded hover:bg-[#0a4d2c] text-sm w-full">
                                🔍 Filtrer
                            </button>
                            <a href="{{ url()->current() }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">
                                🔄
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Liste des Mobile Caissiers avec cercles de paiement -->
                <div class="space-y-6">
                    @php
                        $mobileCaissiersFiltres = $mobileCaissiers;
                        
                        // Appliquer les filtres
                        if (request('commercial_id')) {
                            $mobileCaissiersFiltres = $mobileCaissiersFiltres->where('commercial_id', request('commercial_id'));
                        }
                    @endphp
                    
                    @foreach($mobileCaissiersFiltres as $caissier)
                        <div class="border border-gray-200 rounded-lg p-4 sm:p-6">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                                <div class="mb-3 sm:mb-0">
                                    <h4 class="text-lg font-semibold text-gray-900">
                                        {{ $caissier->prenom }} {{ $caissier->name }}
                                    </h4>
                                    <p class="text-sm text-gray-600">{{ $caissier->email }}</p>
                                    @if($caissier->commercial)
                                        <p class="text-xs text-blue-600">
                                            Commercial: {{ $caissier->commercial->prenom }} {{ $caissier->commercial->name }}
                                        </p>
                                    @endif
                                </div>
                                
                                <div class="flex space-x-2">
                                    <!-- Bouton Supprimer -->
                                    <form action="{{ route('users.destroy', $caissier) }}" method="POST" 
                                          onsubmit="return confirmSuppressionMobileCaissier(this)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600 text-sm flex items-center text-xs">
                                            🗑️ Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>

                         <!-- Cercles de paiement INTERACTIFS - VERSION OPTIMISÉE -->
<div class="mb-4">
    <h5 class="text-sm font-medium text-gray-700 mb-3">Paiements {{ request('annee', now()->year) }}</h5>
    <div class="flex flex-wrap gap-3 sm:gap-4 justify-center">
        @for($i = 1; $i <= 12; $i++)
            @php
                // Utiliser la méthode optimisée
                $paiementsOptimises = $caissier->getPaiementsOptimises($anneeEnCours);
                $paiementMois = $paiementsOptimises[$i] ?? ['paye' => false];
                $estPaye = $paiementMois['paye'];
                $estMoisPasse = $i < $moisEnCours;
                $estMoisFutur = $i > $moisEnCours;
                $estMoisActuel = $i == $moisEnCours;
            @endphp
            
            <div class="flex flex-col items-center">
                <!-- CERCLE CLICKABLE - AVEC DATA ATTRIBUTES -->
                <button type="button"
                        class="relative w-10 h-10 sm:w-12 sm:h-12 rounded-full border-2 flex items-center justify-center text-sm font-medium transition-all duration-200 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-offset-2 paiement-cercle"
                        data-caissier-id="{{ $caissier->id }}"
                        data-mois="{{ $i }}"
                        onclick="gererPaiementMobileCaissier({{ $caissier->id }}, {{ $i }}, {{ $anneeEnCours }}, {{ $estPaye ? 'true' : 'false' }})"
                        title="{{ $estPaye ? 'Annuler le paiement' : 'Valider le paiement' }} - {{ $nomsMoisCourts[$i] }} {{ $anneeEnCours }}">
                    
                    {{ $estPaye ? '✓' : $i }}
                    
                    @if($estMoisActuel && !$estPaye)
                        <div class="absolute -top-1 -right-1 w-3 h-3 bg-yellow-500 rounded-full animate-pulse"></div>
                    @endif
                </button>
                
                <span class="text-xs mt-2 text-gray-600 font-medium">
                    {{ $nomsMoisCourts[$i] }}
                </span>
                
                <div class="text-xs mt-1 text-center min-h-[20px]">
                    @if($estPaye)
                        <span class="text-green-600 font-semibold">✓</span>
                    @elseif($estMoisPasse)
                        <span class="text-red-600 font-semibold">!</span>
                    @endif
                </div>
            </div>
        @endfor
    </div>
</div>

                     <!-- Résumé des paiements - VERSION CORRIGÉE AVEC DATA ATTRIBUTES -->
<div class="bg-gray-50 rounded-lg p-3 sm:p-4" data-caissier-resume="{{ $caissier->id }}">
    <h6 class="text-sm font-medium text-gray-700 mb-2">Résumé {{ $anneeEnCours }}</h6>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
        @php
            // UTILISER LA NOUVELLE MÉTHODE OPTIMISÉE
            $paiementsOptimises = $caissier->getPaiementsOptimises($anneeEnCours);
            
            $moisPayes = 0;
            $moisEnRetard = 0;
            $enAttente = 0;
            $moisFuturs = 0;
            
            // Calculer avec la nouvelle structure
            foreach ($paiementsOptimises as $mois => $paiement) {
                if ($paiement['paye']) {
                    $moisPayes++;
                } else if ($mois < $moisEnCours) {
                    $moisEnRetard++;
                } else if ($mois == $moisEnCours) {
                    $enAttente = 1;
                } else if ($mois > $moisEnCours) {
                    $moisFuturs++;
                }
            }
        @endphp
        
        <div class="text-center p-2 bg-green-50 rounded border border-green-200">
            <div class="text-lg font-bold text-green-600 mois-payes">{{ $moisPayes }}</div>
            <div class="text-green-700 text-xs">Payés</div>
        </div>
        
        <div class="text-center p-2 bg-red-50 rounded border border-red-200">
            <div class="text-lg font-bold text-red-600 mois-retard">{{ $moisEnRetard }}</div>
            <div class="text-red-700 text-xs">En retard</div>
        </div>
        
        <div class="text-center p-2 bg-yellow-50 rounded border border-yellow-200">
            <div class="text-lg font-bold text-yellow-600 mois-attente">{{ $enAttente }}</div>
            <div class="text-yellow-700 text-xs">En attente</div>
        </div>
        
        <div class="text-center p-2 bg-gray-100 rounded border border-gray-300">
            <div class="text-lg font-bold text-gray-600 mois-futurs">{{ $moisFuturs }}</div>
            <div class="text-gray-700 text-xs">Futurs</div>
        </div>
    </div>
</div>
</div>
@endforeach

@if($mobileCaissiersFiltres->count() == 0)
    <div class="text-center py-8">
        <div class="text-gray-400 text-4xl mb-4">👥</div>
        <p class="text-gray-500">Aucun mobile caissier trouvé</p>
        <p class="text-sm text-gray-400 mt-2">Aucun mobile caissier ne correspond aux critères de recherche</p>
    </div>
@endif
</div>
</div>
</div>
</div>
@endif
</div>

<!-- Modal d'affectation des tables (uniquement pour admin/gérant) -->
@if($isGerantOrAdmin)
<div id="modal-affectation" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Affecter la table</h3>
            
            <form id="form-affectation" method="POST">
                @csrf
                <input type="hidden" name="table_id" id="table_id_modal">
                
                <div class="mb-4">
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Sélectionner une hôtesse
                    </label>
                    <select name="user_id" id="user_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-[#0b5f37] focus:ring-[#0b5f37]" required>
                        <option value="">Choisir une hôtesse</option>
                        @foreach($hotesses as $hotesse)
                            <option value="{{ $hotesse->id }}">
                                {{ $hotesse->prenom }} {{ $hotesse->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="fermerModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Annuler
                    </button>
                    <button type="submit" class="bg-[#0b5f37] text-white px-4 py-2 rounded hover:bg-[#0a4d2c]">
                        Affecter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if($isGerantOrAdmin)
<script>
function afficherModalAffectation(tableId) {
    document.getElementById('table_id_modal').value = tableId;
    document.getElementById('modal-affectation').classList.remove('hidden');
}

function fermerModal() {
    document.getElementById('modal-affectation').classList.add('hidden');
    document.getElementById('user_id').value = '';
}

// Fermer la modal en cliquant à l'extérieur
document.getElementById('modal-affectation').addEventListener('click', function(e) {
    if (e.target.id === 'modal-affectation') {
        fermerModal();
    }
});

// Gérer la soumission du formulaire d'affectation
document.getElementById('form-affectation').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const tableId = document.getElementById('table_id_modal').value;
    const formData = new FormData(this);
    
    fetch(`/tables/${tableId}/affecter`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Erreur lors de l\'affectation');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur lors de l\'affectation');
    });
});
</script>
@endif

<!-- Script pour la gestion des paiements - VERSION CORRIGÉE -->
<!-- NOUVEAU SCRIPT OPTIMISÉ - Version corrigée pour éviter les requêtes parallèles -->

<script>
// NOUVELLE FONCTION : Charger tous les paiements en UNE SEULE requête
function chargerTousLesPaiements() {
    const annee = document.querySelector('input[name="annee"]')?.value || new Date().getFullYear();
    const commercialId = document.querySelector('select[name="commercial_id"]')?.value || '';
    const mois = document.querySelector('select[name="mois"]')?.value || new Date().getMonth() + 1;
    
    console.log('Chargement OPTIMISÉ des paiements...', { annee, commercialId, mois });
    
    // Afficher le loader
    const loader = document.getElementById('paiements-loader');
    if (loader) loader.style.display = 'block';
    
    // UNE SEULE requête pour TOUS les paiements
    fetch(`/users/dashboard-paiements?annee=${annee}&commercial_id=${commercialId}&mois=${mois}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('✅ Données reçues pour', data.mobile_caissiers?.length || 0, 'caissiers');
        
        if (data.success && data.mobile_caissiers) {
            // Traiter chaque caissier
            data.mobile_caissiers.forEach(caissier => {
                // Mettre à jour TOUS les cercles pour ce caissier
                for (let mois = 1; mois <= 12; mois++) {
                    const paiement = caissier.paiements[mois];
                    const cercle = document.querySelector(`button[data-caissier-id="${caissier.id}"][data-mois="${mois}"]`);
                    
                    if (cercle) {
                        mettreAJourCercle(cercle, paiement.paye, caissier.id, mois, data.annee);
                    }
                }
                
                // Mettre à jour le résumé
                mettreAJourResumeCaissier(caissier.id, caissier.paiements, data.annee);
            });
        }
    })
    .catch(error => {
        console.error('❌ Erreur chargement paiements:', error);
        // En cas d'erreur, on ne fait rien (les cercles restent dans leur état initial)
        console.log('Les cercles gardent leur état initial');
    })
    .finally(() => {
        if (loader) loader.style.display = 'none';
    });
}

// Fonction pour mettre à jour un cercle
function mettreAJourCercle(cercle, estPaye, caissierId, mois, annee) {
    const moisActuel = new Date().getMonth() + 1;
    const anneeActuelle = new Date().getFullYear();
    
    // Mettre à jour les classes CSS
    cercle.className = 'relative w-10 h-10 sm:w-12 sm:h-12 rounded-full border-2 flex items-center justify-center text-sm font-medium transition-all duration-200 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-offset-2 paiement-cercle';
    
    if (estPaye) {
        cercle.classList.add('bg-green-500', 'border-green-600', 'text-white', 'hover:bg-green-600', 'focus:ring-green-500');
        cercle.innerHTML = '✓';
        cercle.setAttribute('onclick', `gererPaiementMobileCaissier(${caissierId}, ${mois}, ${annee}, true)`);
        cercle.title = `Annuler le paiement - ${mois}/${annee}`;
    } else {
        const estMoisPasse = mois < moisActuel || annee < anneeActuelle;
        const estMoisActuel = mois === moisActuel && annee === anneeActuelle;
        
        if (estMoisPasse) {
            cercle.classList.add('bg-red-500', 'border-red-600', 'text-white', 'hover:bg-red-600', 'focus:ring-red-500');
        } else if (estMoisActuel) {
            cercle.classList.add('bg-yellow-500', 'border-yellow-600', 'text-white', 'hover:bg-yellow-600', 'focus:ring-yellow-500');
            
            // Ajouter l'indicateur de mois en cours
            if (!cercle.querySelector('.animate-pulse')) {
                const pulse = document.createElement('div');
                pulse.className = 'absolute -top-1 -right-1 w-3 h-3 bg-yellow-500 rounded-full animate-pulse';
                cercle.appendChild(pulse);
            }
        } else {
            cercle.classList.add('bg-gray-200', 'border-gray-300', 'text-gray-500', 'hover:bg-gray-300', 'focus:ring-gray-500');
        }
        
        cercle.innerHTML = mois;
        cercle.setAttribute('onclick', `gererPaiementMobileCaissier(${caissierId}, ${mois}, ${annee}, false)`);
        cercle.title = `Valider le paiement - ${mois}/${annee}`;
        
        // Retirer l'indicateur pulse si présent et pas le mois actuel
        if (!estMoisActuel) {
            const pulse = cercle.querySelector('.animate-pulse');
            if (pulse) pulse.remove();
        }
    }
}

// Fonction pour mettre à jour le résumé d'un caissier
function mettreAJourResumeCaissier(caissierId, paiements, annee) {
    const moisActuel = new Date().getMonth() + 1;
    const anneeActuelle = new Date().getFullYear();
    
    let moisPayes = 0;
    let moisEnRetard = 0;
    let enAttente = 0;
    let moisFuturs = 0;
    
    for (let mois = 1; mois <= 12; mois++) {
        const paiement = paiements[mois];
        
        if (paiement.paye) {
            moisPayes++;
        } else if (mois < moisActuel || annee < anneeActuelle) {
            moisEnRetard++;
        } else if (mois === moisActuel && annee === anneeActuelle) {
            enAttente = 1;
        } else if (mois > moisActuel) {
            moisFuturs++;
        }
    }
    
    // Mettre à jour les éléments HTML
    const carte = document.querySelector(`[data-caissier-resume="${caissierId}"]`);
    if (carte) {
        const payesElem = carte.querySelector('.mois-payes');
        const retardElem = carte.querySelector('.mois-retard');
        const attenteElem = carte.querySelector('.mois-attente');
        const futursElem = carte.querySelector('.mois-futurs');
        
        if (payesElem) payesElem.textContent = moisPayes;
        if (retardElem) retardElem.textContent = moisEnRetard;
        if (attenteElem) attenteElem.textContent = enAttente;
        if (futursElem) futursElem.textContent = moisFuturs;
    }
}

// Fonction pour gérer les paiements des mobile caissiers - VERSION SIMPLIFIÉE
function gererPaiementMobileCaissier(caissierId, mois, annee, estDejaPaye) {
    const action = estDejaPaye ? 'annuler' : 'valider';
    const nomsMois = {
        1: 'Janvier', 2: 'Février', 3: 'Mars', 4: 'Avril', 5: 'Mai', 6: 'Juin',
        7: 'Juillet', 8: 'Août', 9: 'Septembre', 10: 'Octobre', 11: 'Novembre', 12: 'Décembre'
    };
    const moisNom = nomsMois[mois];
    
    // Désactiver le bouton pendant le traitement
    const boutonCercle = document.querySelector(`button[data-caissier-id="${caissierId}"][data-mois="${mois}"]`);
    if (boutonCercle) {
        boutonCercle.disabled = true;
        boutonCercle.style.opacity = '0.5';
        boutonCercle.style.cursor = 'wait';
    }
    
    if (typeof Swal === 'undefined') {
        if (confirm(`${action.toUpperCase()} le paiement pour ${moisNom} ${annee} ?`)) {
            soumettrePaiement(caissierId, mois, annee, action, boutonCercle);
        } else {
            reactiverBouton(boutonCercle);
        }
        return;
    }
    
    Swal.fire({
        title: `${action.toUpperCase()} le paiement ?`,
        html: `
            <div class="text-left text-sm">
                <p><strong>Période:</strong> ${moisNom} ${annee}</p>
                <p><strong>Action:</strong> ${estDejaPaye ? 'Annuler le paiement' : 'Valider le paiement'}</p>
                <p class="text-gray-600 mt-2">Cette action sera enregistrée dans le système.</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: estDejaPaye ? '#d33' : '#0b5f37',
        cancelButtonColor: '#6b7280',
        confirmButtonText: estDejaPaye ? 'Oui, annuler' : 'Oui, valider',
        cancelButtonText: 'Annuler',
        showLoaderOnConfirm: true,
        allowOutsideClick: () => !Swal.isLoading(),
        preConfirm: () => {
            return soumettrePaiement(caissierId, mois, annee, action, boutonCercle);
        }
    }).then((result) => {
        if (result.isConfirmed && result.value && result.value.success) {
            Swal.fire({
                title: 'Succès !',
                text: result.value.message,
                icon: 'success',
                confirmButtonColor: '#0b5f37',
                timer: 1500,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                // Après succès, rafraîchir tous les paiements (une seule requête)
                chargerTousLesPaiements();
            });
        } else if (result.isConfirmed && result.value && !result.value.success) {
            Swal.fire({
                title: 'Erreur !',
                text: result.value.message || 'Erreur inconnue',
                icon: 'error',
                confirmButtonColor: '#d33'
            });
            reactiverBouton(boutonCercle);
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            reactiverBouton(boutonCercle);
        }
    });
}

// Fonction pour soumettre le paiement
function soumettrePaiement(caissierId, mois, annee, action, boutonCercle) {
    return fetch('{{ route("users.toggle-paiement") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            caissier_id: caissierId,
            mois: mois,
            annee: annee,
            action: action
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur réseau: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        return data;
    })
    .catch(error => {
        console.error('Erreur:', error);
        reactiverBouton(boutonCercle);
        return {
            success: false,
            message: 'Erreur réseau: ' + error.message
        };
    });
}

// Fonction pour réactiver un bouton
function reactiverBouton(bouton) {
    if (bouton) {
        bouton.disabled = false;
        bouton.style.opacity = '1';
        bouton.style.cursor = 'pointer';
    }
}

// Fonction pour retirer l'affectation
function confirmRetirerAffectation(event, caissierId) {
    event.preventDefault();
    
    if (typeof Swal === 'undefined') {
        if (confirm('Êtes-vous sûr de vouloir retirer cette affectation ?')) {
            event.target.closest('form').submit();
        }
        return false;
    }
    
    Swal.fire({
        title: 'Retirer l\'affectation ?',
        text: "Ce mobile caissier ne sera plus associé à ce commercial/grande caisse.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Oui, retirer',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            event.target.closest('form').submit();
        }
    });
    
    return false;
}

// Initialisation optimisée - UNE SEULE REQUÊTE au chargement
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard chargé, initialisation OPTIMISÉE...');
    
    // Charger tous les paiements en UNE SEULE requête après un délai
    setTimeout(() => {
        chargerTousLesPaiements();
    }, 800);
    
    // Écouter les changements de filtres
    document.querySelectorAll('select[name="commercial_id"], input[name="annee"], select[name="mois"]').forEach(element => {
        element.addEventListener('change', function() {
            // Débounce pour éviter les requêtes multiples
            clearTimeout(window.filterTimeout);
            window.filterTimeout = setTimeout(() => {
                chargerTousLesPaiements();
            }, 500);
        });
    });
    
    // Améliorer l'UX des cercles
    document.addEventListener('mouseover', function(e) {
        if (e.target.classList.contains('paiement-cercle')) {
            e.target.style.transform = 'scale(1.1)';
            e.target.style.transition = 'transform 0.2s ease';
        }
    });
    
    document.addEventListener('mouseout', function(e) {
        if (e.target.classList.contains('paiement-cercle')) {
            e.target.style.transform = 'scale(1)';
        }
    });
});
</script>

<style>
/* Style pour le bouton de déconnexion mobile */
.mobile-logout-btn {
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    backdrop-filter: blur(10px);
    border: 2px solid white;
    z-index: 1000;
}

.mobile-logout-btn:hover {
    box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
    transform: scale(1.1);
}

/* Animation pour le bouton mobile */
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
    }
}

.mobile-logout-btn {
    animation: pulse 2s infinite;
}

/* Style pour la scrollbar personnalisée */
.max-h-32::-webkit-scrollbar,
.max-h-40::-webkit-scrollbar,
.max-h-60::-webkit-scrollbar {
    width: 3px;
}

.max-h-32::-webkit-scrollbar-track,
.max-h-40::-webkit-scrollbar-track,
.max-h-60::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.max-h-32::-webkit-scrollbar-thumb,
.max-h-40::-webkit-scrollbar-thumb,
.max-h-60::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.max-h-32::-webkit-scrollbar-thumb:hover,
.max-h-40::-webkit-scrollbar-thumb:hover,
.max-h-60::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Amélioration du responsive */
@media (max-width: 640px) {
    .min-w-0 {
        min-width: 0;
    }
    
    table {
        font-size: 0.75rem;
    }
    
    .px-4 {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    
    /* Styles spécifiques pour les boutons côte à côte */
    .flex-row {
        flex-direction: row !important;
    }
    
    .min-w-\[120px\] {
        min-width: 120px;
    }
    
    .min-w-\[140px\] {
        min-width: 140px;
    }
    
    /* Assurer que les boutons restent sur la même ligne */
    .flex-row .w-auto {
        width: auto !important;
    }
}

/* Empêcher le zoom sur les inputs sur mobile -->
@media (max-width: 768px) {
    input, select, textarea {
        font-size: 16px !important;
    }
}

/* Styles pour les boutons côte à côte */
.flex-row {
    display: flex;
    flex-direction: row;
    gap: 8px;
}

.min-w-\[120px\] {
    min-width: 120px;
}

.min-w-\[140px\] {
    min-width: 140px;
}

/* S'assurer que les boutons ont la même hauteur */
.flex-row button,
.flex-row a {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
}

/* Couleurs pour le bouton déconnexion */
.bg-red-500 {
    background-color: #ef4444;
}

.bg-red-500:hover {
    background-color: #dc2626;
}

/* Correction des formulaires imbriqués */
form {
    position: relative;
}

/* S'assurer que les boutons de déconnexion sont bien séparés */
.mobile-logout-fixed {
    z-index: 1001;
}

/*style boutton whatsapp */
.whatsapp-float-premium {
    position: fixed;
    bottom: 15px;
    right: 15px;

    width: 50px;
    height: 50px;

    background: #25D366;
    border-radius: 50%;

    display: flex;
    align-items: center;
    justify-content: center;

    z-index: 9999;
    cursor: pointer;

    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

/* Icône */
.whatsapp-float-premium {
    position: fixed;
    bottom: 12px;
    right: 12px;

    width: 40px;   /* 🔽 réduit (avant 50px) */
    height: 40px;

    background: #25D366;
    border-radius: 50%;

    display: flex;
    align-items: center;
    justify-content: center;

    z-index: 9999;
    cursor: pointer;

    box-shadow: 0 3px 8px rgba(0,0,0,0.25);
}

/* Icône encore plus petite */
.whatsapp-icon {
    width: 18px;   /* 🔽 réduit (avant 24px) */
    height: 18px;
    fill: white;
    z-index: 2;
}

/* Pulse plus discret */
.pulse-ring {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: rgba(37, 211, 102, 0.4);
    animation: pulse-animation 2s infinite; /* plus doux */
    z-index: 1;
}

/* Animation plus élégante */
@keyframes pulse-animation {
    0% {
        transform: scale(1);
        opacity: 0.6;
    }
    70% {
        transform: scale(1.4); /* moins agressif */
        opacity: 0;
    }
    100% {
        transform: scale(1.4);
        opacity: 0;
    }
}



</style>
@endsection