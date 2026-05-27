@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- En-tête -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Tableau de Bord Hôtesse</h1>
                    <p class="text-gray-600 mt-1">Bienvenue, {{ Auth::user()->prenom }} {{ Auth::user()->name }}</p>
                </div>
                <div class="flex items-center space-x-4 mt-3 sm:mt-0">
                    <div class="bg-green-100 text-green-800 px-3 py-2 rounded-lg text-sm">
                        <span class="font-semibold">Tables affectées :</span>
                        <span class="ml-1">{{ $tablesAffectees->count() }}</span>
                    </div>
                    <div class="bg-blue-100 text-blue-800 px-3 py-2 rounded-lg text-sm">
                        <span class="font-semibold">Commandes en cours :</span>
                        <span class="ml-1">{{ $commandesEnCours->count() }}</span>
                    </div>
                    @if($observationsNonLues->count() > 0)
                    <div class="bg-red-100 text-red-800 px-3 py-2 rounded-lg text-sm">
                        <span class="font-semibold">Nouvelles observations :</span>
                        <span class="ml-1">{{ $observationsNonLues->count() }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Alertes et notifications -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Grille principale -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Colonne 1: Tables affectées -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            📊 Mes Tables
                        </h3>
                    </div>
                    <div class="p-4">
                        @if($tablesAffectees->count() > 0)
                            <div class="space-y-3">
                                @foreach($tablesAffectees as $table)
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition duration-150">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">Table {{ $table->numero }}</h4>
                                            <p class="text-sm text-gray-600">{{ $table->nom }}</p>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Affectée
                                        </span>
                                    </div>
                                    
                                    <!-- Commandes pour cette table -->
                                    @php
                                        $commandesTable = $commandesEnCours->where('table_id', $table->id);
                                    @endphp
                                    
                                    @if($commandesTable->count() > 0)
                                        <div class="mt-3 pt-3 border-t border-gray-200">
                                            <p class="text-xs font-medium text-gray-700 mb-2">Commandes en cours :</p>
                                            <div class="space-y-2">
                                                @foreach($commandesTable as $commande)
                                                <div class="bg-yellow-50 border border-yellow-200 rounded p-2">
                                                    <div class="flex justify-between items-center">
                                                        <span class="text-sm font-medium text-yellow-800">
                                                            #{{ $commande->numero_commande }}
                                                        </span>
                                                        <span class="text-xs text-yellow-600">
                                                            {{ $commande->created_at->format('H:i') }}
                                                        </span>
                                                    </div>
                                                    <p class="text-xs text-yellow-700 mt-1">
                                                        {{ $commande->products->count() }} produit(s) - 
                                                        {{ number_format($commande->montant, 0, ',', ' ') }} FCFA
                                                    </p>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <div class="mt-2">
                                            <p class="text-xs text-gray-500">Aucune commande en cours</p>
                                        </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                <p class="text-gray-500 text-sm">Aucune table affectée</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Section Observations -->
                <div class="mt-6 bg-white rounded-lg shadow">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                📝 Observations du Manager
                                @if($observationsNonLues->count() > 0)
                                <span class="ml-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                                    {{ $observationsNonLues->count() }} nouvelle(s)
                                </span>
                                @endif
                            </h3>
                            <a href="{{ route('observations.mes-observations') }}" 
                               class="text-sm text-[#0b5f37] hover:text-[#0a4d2c] font-medium">
                                Voir tout
                            </a>
                        </div>
                    </div>
                    <div class="p-4">
                        @if($observationsRecentes->count() > 0)
                            <div class="space-y-4">
                                @foreach($observationsRecentes as $observation)
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition duration-150
                                    {{ !$observation->est_lu ? 'bg-blue-50 border-blue-200' : '' }}">
                                    <!-- En-tête de l'observation -->
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $observation->titre }}</h4>
                                            <p class="text-xs text-gray-500 mt-1">
                                                Par {{ $observation->manager->prenom }} {{ $observation->manager->name }}
                                                • {{ $observation->date_observation->format('d/m/Y H:i') }}
                                            </p>
                                        </div>
                                        <div class="flex flex-col items-end space-y-1">
                                            <!-- Badge type -->
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                                {{ $observation->type === 'positif' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $observation->type === 'negatif' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $observation->type === 'suggestion' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                                {{ ucfirst($observation->type) }}
                                            </span>
                                            <!-- Badge priorité -->
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                                {{ $observation->priorite === 'faible' ? 'bg-gray-100 text-gray-800' : '' }}
                                                {{ $observation->priorite === 'moyenne' ? 'bg-orange-100 text-orange-800' : '' }}
                                                {{ $observation->priorite === 'elevee' ? 'bg-red-100 text-red-800' : '' }}">
                                                {{ ucfirst($observation->priorite) }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Contenu -->
                                    <p class="text-sm text-gray-700 mb-3">{{ Str::limit($observation->contenu, 150) }}</p>

                                    <!-- Actions -->
                                    <div class="flex justify-between items-center pt-3 border-t border-gray-200">
                                        <span class="text-xs text-gray-500">
                                            @if(!$observation->est_lu)
                                                <span class="text-blue-600 font-medium">● Nouveau</span>
                                            @else
                                                <span class="text-green-600 font-medium">✓ Lu</span>
                                            @endif
                                        </span>
                                        <div class="flex space-x-2">
                                            @if(!$observation->est_lu)
                                            <form action="{{ route('observations.marquer-lu', $observation) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700 transition-colors">
                                                    ✓ OK
                                                </button>
                                            </form>
                                            @endif
                                            <a href="{{ route('observations.show', $observation) }}" 
                                               class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700 transition-colors">
                                                Détails
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-gray-500 text-sm">Aucune observation pour le moment</p>
                                <p class="text-gray-400 text-xs mt-1">Votre manager vous enverra des feedbacks ici</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Colonne 2: Commandes en cours -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            📋 Commandes en Cours
                        </h3>
                    </div>
                    <div class="p-4">
                        @if($commandesEnCours->count() > 0)
                            <div class="space-y-4">
                                @foreach($commandesEnCours as $commande)
                                <div class="border border-orange-200 rounded-lg p-4 bg-orange-50">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">Commande #{{ $commande->numero_commande }}</h4>
                                            <p class="text-sm text-gray-600">
                                                Table {{ $commande->table->numero }} - {{ $commande->table->nom }}
                                            </p>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            En cours
                                        </span>
                                    </div>

                                    <!-- Produits de la commande -->
                                    <div class="mb-3">
                                        <p class="text-xs font-medium text-gray-700 mb-2">Produits commandés :</p>
                                        <div class="space-y-1">
                                            @foreach($commande->products as $product)
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-600">{{ $product->designation }}</span>
                                                <span class="text-gray-900 font-medium">
                                                    x{{ $product->pivot->quantite }}
                                                </span>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Informations de la commande -->
                                    <div class="flex justify-between items-center pt-3 border-t border-orange-200">
                                        <div class="text-sm">
                                            <span class="text-gray-600">Total :</span>
                                            <span class="font-semibold text-gray-900 ml-1">
                                                {{ number_format($commande->montant, 0, ',', ' ') }} FCFA
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $commande->created_at->format('d/m/Y H:i') }}
                                        </div>
                                    </div>

                                    @if($commande->notes)
                                    <div class="mt-2 p-2 bg-white rounded border border-gray-200">
                                        <p class="text-xs text-gray-600">
                                            <span class="font-medium">Notes :</span> {{ $commande->notes }}
                                        </p>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                                <p class="text-gray-500 text-sm">Aucune commande en cours</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Colonne 3: Produits disponibles -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                🏷️ Produits Disponibles
                            </h3>
                            <div class="relative">
                                <input type="text" 
                                       id="product-search" 
                                       class="w-40 pl-8 pr-3 py-1 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500" 
                                       placeholder="Rechercher...">
                                <svg class="w-4 h-4 text-gray-400 absolute left-2 top-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        <!-- Filtres par catégorie -->
                        <div class="mb-4">
                            <div class="flex flex-wrap gap-1">
                                <button type="button" class="category-filter active bg-green-600 text-white px-3 py-1 rounded-full text-xs" data-category="all">
                                    Tous
                                </button>
                                @foreach($categories as $categorie)
                                <button type="button" class="category-filter bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-xs" data-category="{{ $categorie->id }}">
                                    {{ $categorie->nom }}
                                </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Liste des produits -->
                        <div id="products-list" class="space-y-3 max-h-96 overflow-y-auto">
                            @foreach($products as $product)
                            <div class="product-item border border-gray-200 rounded-lg p-3 hover:bg-gray-50 transition duration-150"
                                 data-category="{{ $product->categorie_id }}"
                                 data-name="{{ strtolower($product->designation) }}"
                                 data-stock="{{ $product->quantite }}">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900 text-sm">{{ $product->designation }}</h4>
                                        <p class="text-xs text-gray-500 mt-1">{{ $product->categorie->nom }}</p>
                                        <p class="text-xs text-gray-600 mt-1">{{ $product->description }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-green-600">
                                            {{ number_format($product->prix, 0, ',', ' ') }} FCFA
                                        </p>
                                        <p class="text-xs {{ $product->quantite > 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                                            @if($product->quantite > 0)
                                                Stock: {{ $product->quantite }}
                                            @else
                                                Rupture
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Message aucun résultat -->
                        <div id="no-products" class="hidden text-center py-8">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <p class="text-gray-500 text-sm">Aucun produit trouvé</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Commandes Soldées -->
        <div class="mt-6">
            <div class="bg-white rounded-lg shadow">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        📊 Historique des Commandes Soldées
                    </h3>
                </div>
                <div class="p-4">
                    @if($commandesSoldees->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Commande</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Table</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($commandesSoldees as $commande)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            #{{ $commande->numero_commande }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            Table {{ $commande->table->numero }}
                                        </td>
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                            {{ number_format($commande->montant, 0, ',', ' ') }} FCFA
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $commande->updated_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Soldée
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $commandesSoldees->links() }}
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-gray-500 text-sm">Aucune commande soldée</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Script de filtrage des produits
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('product-search');
    const categoryFilters = document.querySelectorAll('.category-filter');
    const productItems = document.querySelectorAll('.product-item');
    const noProducts = document.getElementById('no-products');
    const productsList = document.getElementById('products-list');

    let currentCategory = 'all';
    let currentSearch = '';

    function filterProducts() {
        let visibleCount = 0;

        productItems.forEach(item => {
            const category = item.getAttribute('data-category');
            const name = item.getAttribute('data-name');
            const stock = parseInt(item.getAttribute('data-stock'));

            const matchesCategory = currentCategory === 'all' || category === currentCategory;
            const matchesSearch = currentSearch === '' || name.includes(currentSearch.toLowerCase());

            const isVisible = matchesCategory && matchesSearch;

            if (isVisible) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        if (visibleCount === 0) {
            noProducts.classList.remove('hidden');
            productsList.classList.add('hidden');
        } else {
            noProducts.classList.add('hidden');
            productsList.classList.remove('hidden');
        }
    }

    searchInput.addEventListener('input', function() {
        currentSearch = this.value.toLowerCase();
        filterProducts();
    });

    categoryFilters.forEach(filter => {
        filter.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            
            categoryFilters.forEach(f => {
                if (f === this) {
                    f.classList.add('bg-green-600', 'text-white');
                    f.classList.remove('bg-gray-200', 'text-gray-700');
                } else {
                    f.classList.remove('bg-green-600', 'text-white');
                    f.classList.add('bg-gray-200', 'text-gray-700');
                }
            });
            
            currentCategory = category;
            filterProducts();
        });
    });

    searchInput.focus();
});
</script>
@endsection