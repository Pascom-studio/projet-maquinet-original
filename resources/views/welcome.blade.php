@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-[#0b5f37]">Tableau de Bord - {{ Auth::user()->fonction }}</h1>
        <p class="text-gray-600">Bienvenue {{ Auth::user()->prenom }} {{ Auth::user()->nom }}</p>
    </div>

    <!-- Statut de caisse -->
    @php
        $caisse_actuelle = \App\Models\Caisse::where('user_id', Auth::id())
                                           ->where('statut', 'ouverte')
                                           ->first();
    @endphp
    
    @if($caisse_actuelle)
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-green-800">✅ Caisse Ouverte</h3>
                <p class="text-green-600">Solde actuel: <strong>{{ number_format($caisse_actuelle->solde_actuel, 0, ',', ' ') }} FCFA</strong></p>
                <p class="text-green-600 text-sm">Ouverte le: {{ $caisse_actuelle->date_ouverture->format('d/m/Y H:i') }}</p>
            </div>
            <a href="{{ route('caisse.index') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Gérer la Caisse
            </a>
        </div>
    </div>
    @else
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-yellow-800">🔒 Caisse Fermée</h3>
                <p class="text-yellow-600">Veuillez ouvrir votre caisse pour commencer les transactions</p>
            </div>
            <a href="{{ route('caisse.index') }}" class="bg-[#0b5f37] text-white px-4 py-2 rounded hover:bg-[#0a4d2c]">
                Ouvrir la Caisse
            </a>
        </div>
    </div>
    @endif

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Produits -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-[#0b5f37]">
            <h3 class="text-lg font-semibold text-gray-700">Total Produits</h3>
            <p class="text-3xl font-bold text-[#0b5f37]">{{ App\Models\Product::count() }}</p>
        </div>

        <!-- Total Catégories (Gérant et Admin seulement) -->
        @if(Auth::user()->isAdmin() || Auth::user()->isGerant())
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-[#ee8f13]">
            <h3 class="text-lg font-semibold text-gray-700">Total Catégories</h3>
            <p class="text-3xl font-bold text-[#ee8f13]">{{ App\Models\Categorie::count() }}</p>
        </div>
        @endif

        <!-- Ventes du jour -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-[#cb6ce6]">
            <h3 class="text-lg font-semibold text-gray-700">
                @if(Auth::user()->isCaissier())
                    Mes Ventes Aujourd'hui
                @else
                    Ventes du Jour
                @endif
            </h3>
            @if(Auth::user()->isCaissier())
                @php
                    $mes_ventes_ajd = App\Models\Vente::where('user_id', Auth::id())->whereDate('created_at', today())->count();
                    $montant_mes_ventes = App\Models\Vente::where('user_id', Auth::id())->whereDate('created_at', today())->sum('montant');
                @endphp
                <p class="text-3xl font-bold text-[#cb6ce6]">{{ $mes_ventes_ajd }}</p>
                <p class="text-sm text-gray-500">{{ number_format($montant_mes_ventes, 0, ',', ' ') }} FCFA</p>
            @else
                @php
                    $ventes_ajd = App\Models\Vente::whereDate('created_at', today())->count();
                    $montant_ventes_ajd = App\Models\Vente::whereDate('created_at', today())->sum('montant');
                @endphp
                <p class="text-3xl font-bold text-[#cb6ce6]">{{ $ventes_ajd }}</p>
                <p class="text-sm text-gray-500">{{ number_format($montant_ventes_ajd, 0, ',', ' ') }} FCFA</p>
            @endif
        </div>

        <!-- Stock Total -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-[#8c52ff]">
            <h3 class="text-lg font-semibold text-gray-700">Stock Total</h3>
            <p class="text-3xl font-bold text-[#8c52ff]">{{ App\Models\Product::sum('quantite') }}</p>
            <p class="text-sm text-gray-500">unités en stock</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Actions Rapides selon le rôle -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-semibold text-[#0b5f37] mb-4">Actions Rapides</h3>
            <div class="space-y-3">
                <!-- Caissier, Gérant et Admin peuvent faire des ventes -->
                @if(Auth::user()->isCaissier() || Auth::user()->isGerant() || Auth::user()->isAdmin())
                    @if($caisse_actuelle || Auth::user()->isGerant() || Auth::user()->isAdmin())
                        <a href="{{ route('ventes.create') }}" class="block w-full bg-[#0b5f37] text-white py-2 px-4 rounded text-center hover:bg-[#0a4d2c] transition duration-200">
                            🛒 Nouvelle Vente
                        </a>
                    @else
                        <button class="block w-full bg-gray-400 text-white py-2 px-4 rounded text-center cursor-not-allowed opacity-50" 
                                onclick="alert('Veuillez ouvrir votre caisse d\\'abord')">
                            🛒 Nouvelle Vente
                        </button>
                    @endif
                    <a href="{{ route('caisse.index') }}" class="block w-full bg-[#ee8f13] text-white py-2 px-4 rounded text-center hover:bg-[#d67f11] transition duration-200">
                        🏦 Gestion de Caisse
                    </a>
                @endif

                <!-- Gérant et Admin peuvent gérer produits, catégories, mouvements -->
                @if(Auth::user()->isGerant() || Auth::user()->isAdmin())
                    <a href="{{ route('products.create') }}" class="block w-full bg-[#0b5f37] text-white py-2 px-4 rounded text-center hover:bg-[#0a4d2c] transition duration-200">
                        📦 Ajouter un Produit
                    </a>
                    <a href="{{ route('categories.create') }}" class="block w-full bg-[#ee8f13] text-white py-2 px-4 rounded text-center hover:bg-[#d67f11] transition duration-200">
                        📁 Nouvelle Catégorie
                    </a>
                    <a href="{{ route('mouvements.create') }}" class="block w-full bg-[#cb6ce6] text-white py-2 px-4 rounded text-center hover:bg-[#b85acf] transition duration-200">
                        📊 Mouvement de Stock
                    </a>
                @endif

                <!-- Voir les ventes -->
                <a href="{{ route('ventes.index') }}" class="block w-full bg-[#8c52ff] text-white py-2 px-4 rounded text-center hover:bg-[#7a41e6] transition duration-200">
                    📈 Voir Toutes les Ventes
                </a>
            </div>
        </div>

        <!-- Informations importantes -->
        <div class="space-y-6">
            <!-- Produits Faible Stock -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold text-[#0b5f37] mb-4">📦 Produits Faible Stock</h3>
                <div class="space-y-2">
                    @php
                        $produits_faible_stock = App\Models\Product::where('quantite', '<', 10)->get();
                    @endphp
                    @foreach($produits_faible_stock as $product)
                        <div class="flex justify-between items-center p-2 bg-red-50 rounded border border-red-200">
                            <div>
                                <span class="text-sm font-medium">{{ $product->designation }}</span>
                                <span class="text-xs text-gray-500 ml-2">{{ $product->categorie->nom }}</span>
                            </div>
                            <span class="text-sm font-bold text-red-600 bg-red-100 px-2 py-1 rounded">{{ $product->quantite }}</span>
                        </div>
                    @endforeach
                    @if($produits_faible_stock->count() == 0)
                        <p class="text-green-600 text-sm text-center py-2">✅ Aucun produit en faible stock</p>
                    @endif
                </div>
            </div>

            <!-- Informations utilisateur -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold text-[#0b5f37] mb-4">👤 Votre Activité</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Ventes aujourd'hui:</span>
                        <span class="font-semibold">
                            {{ App\Models\Vente::where('user_id', Auth::id())->whereDate('created_at', today())->count() }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Chiffre d'affaires aujourd'hui:</span>
                        <span class="font-semibold text-[#0b5f37]">
                            {{ number_format(App\Models\Vente::where('user_id', Auth::id())->whereDate('created_at', today())->sum('montant'), 0, ',', ' ') }} FCFA
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total ventes ce mois:</span>
                        <span class="font-semibold">
                            {{ App\Models\Vente::where('user_id', Auth::id())->whereMonth('created_at', now()->month)->count() }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Alertes importantes -->
            @if(!$caisse_actuelle && Auth::user()->isCaissier())
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                <div class="flex items-center">
                    <span class="text-orange-500 mr-2">⚠️</span>
                    <p class="text-orange-700 text-sm">
                        <strong>Action requise:</strong> Ouvrez votre caisse pour commencer les ventes.
                    </p>
                </div>
            </div>
            @endif

            @if($produits_faible_stock->count() > 0 && (Auth::user()->isGerant() || Auth::user()->isAdmin()))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <span class="text-red-500 mr-2">🔔</span>
                    <p class="text-red-700 text-sm">
                        <strong>Attention:</strong> {{ $produits_faible_stock->count() }} produit(s) en faible stock.
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Section pour Gérant/Admin - Vue d'ensemble -->
    @if(Auth::user()->isGerant() || Auth::user()->isAdmin())
    <div class="mt-8 bg-white rounded-lg shadow p-6">
        <h3 class="text-xl font-semibold text-[#0b5f37] mb-4">📊 Vue d'Ensemble</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <p class="text-2xl font-bold text-blue-600">{{ App\Models\User::where('fonction', 'Caissier')->count() }}</p>
                <p class="text-sm text-gray-600">Caissiers actifs</p>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <p class="text-2xl font-bold text-green-600">{{ App\Models\Caisse::where('statut', 'ouverte')->count() }}</p>
                <p class="text-sm text-gray-600">Caisses ouvertes</p>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <p class="text-2xl font-bold text-purple-600">{{ number_format(App\Models\Vente::whereDate('created_at', today())->sum('montant'), 0, ',', ' ') }} FCFA</p>
                <p class="text-sm text-gray-600">CA du jour</p>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection