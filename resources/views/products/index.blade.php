@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="px-3 sm:px-6">
        <!-- En-tête -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-[#0b5f37]">Gestion des Produits</h1>
                <p class="text-sm text-gray-600 mt-1">
                    <span id="product-count">{{ $products->count() }}</span> produit(s)
                    <button id="refresh-products" class="ml-2 text-[#0b5f37] hover:text-[#0a4d2c] text-sm">
                        🔄 Actualiser
                    </button>
                </p>
            </div>
            <a href="{{ route('products.create') }}" class="bg-[#0b5f37] text-white px-4 py-2 rounded text-sm hover:bg-[#0a4d2c] w-full sm:w-auto text-center">
                ➕ Nouveau Produit
            </a>
        </div>

        <!-- Barre de recherche -->
        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <div class="relative">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 relative">
                        <label for="product-search" class="block text-sm font-medium text-gray-700 mb-1">Rechercher un produit</label>
                        <input type="text" 
                               id="product-search" 
                               class="w-full border border-gray-300 rounded-md px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0b5f37] focus:border-[#0b5f37]"
                               placeholder="Tapez le nom, catégorie ou prix...">
                        <div class="absolute right-3 top-8">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-end">
                        <button type="button" 
                                id="clear-search" 
                                class="bg-gray-500 text-white px-4 py-2 rounded text-sm hover:bg-gray-600 hidden">
                            Effacer
                        </button>
                    </div>
                </div>
                
                <!-- Filtres rapides -->
                <div class="flex flex-wrap gap-2 mt-3">
                    <button type="button" class="filter-btn active bg-[#0b5f37] text-white px-3 py-1 rounded-full text-xs" data-filter="all">
                        Tous (<span id="count-all">{{ $products->count() }}</span>)
                    </button>
                    <button type="button" class="filter-btn bg-green-600 text-white px-3 py-1 rounded-full text-xs" data-filter="in-stock">
                        En stock (<span id="count-in-stock">{{ $products->where('quantite', '>', 0)->count() }}</span>)
                    </button>
                    <button type="button" class="filter-btn bg-yellow-600 text-white px-3 py-1 rounded-full text-xs" data-filter="low-stock">
                        Stock faible (<span id="count-low-stock">{{ $products->where('quantite', '<', 10)->where('quantite', '>', 0)->count() }}</span>)
                    </button>
                    <button type="button" class="filter-btn bg-red-600 text-white px-3 py-1 rounded-full text-xs" data-filter="out-of-stock">
                        Rupture (<span id="count-out-of-stock">{{ $products->where('quantite', 0)->count() }}</span>)
                    </button>
                </div>
            </div>
        </div>

        <!-- Version desktop -->
        <div class="hidden sm:block bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Désignation</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prix</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="products-table-body" class="bg-white divide-y divide-gray-200">
                        @foreach($products as $product)
                        <tr class="product-row hover:bg-gray-50" 
                            data-product-id="{{ $product->id }}"
                            data-designation="{{ strtolower($product->designation) }}"
                            data-categorie="{{ strtolower($product->categorie->nom) }}"
                            data-prix="{{ $product->prix }}"
                            data-quantite="{{ $product->quantite }}"
                            data-stock-status="{{ $product->quantite == 0 ? 'out-of-stock' : ($product->quantite < 10 ? 'low-stock' : 'in-stock') }}">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900 product-designation">{{ $product->designation }}</div>
                                @if($product->description)
                                <div class="text-sm text-gray-500">{{ Str::limit($product->description, 30) }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-900 product-category">{{ $product->categorie->nom }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    <span class="text-sm font-medium product-quantity {{ $product->quantite < 10 ? 'text-red-600' : 'text-gray-900' }}">
                                        {{ $product->quantite }}
                                    </span>
                                    @if($product->quantite == 0)
                                        <span class="ml-2 bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">Rupture</span>
                                    @elseif($product->quantite < 10)
                                        <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Faible</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 product-price">
                                {{ number_format($product->prix, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('products.edit', $product) }}" 
                                       class="text-[#ee8f13] hover:text-[#d67f11] text-sm flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Modifier
                                    </a>
                                    @if(auth()->user()->isAdmin() || auth()->user()->isGerant())
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900 text-sm flex items-center"
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit?')">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Supprimer
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Version mobile -->
        <div id="products-mobile-list" class="sm:hidden space-y-3">
            @forelse($products as $product)
            <div class="product-card bg-white rounded-lg shadow border border-gray-200 p-4"
                 data-product-id="{{ $product->id }}"
                 data-designation="{{ strtolower($product->designation) }}"
                 data-categorie="{{ strtolower($product->categorie->nom) }}"
                 data-prix="{{ $product->prix }}"
                 data-quantite="{{ $product->quantite }}"
                 data-stock-status="{{ $product->quantite == 0 ? 'out-of-stock' : ($product->quantite < 10 ? 'low-stock' : 'in-stock') }}">
                <!-- En-tête de la carte -->
                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-gray-900 product-designation">{{ $product->designation }}</h3>
                        <p class="text-xs text-gray-500 mt-1 product-category">{{ $product->categorie->nom }}</p>
                        @if($product->description)
                        <p class="text-xs text-gray-600 mt-1">{{ Str::limit($product->description, 50) }}</p>
                        @endif
                    </div>
                    <span class="text-sm font-bold text-[#0b5f37] product-price">
                        {{ number_format($product->prix, 0, ',', ' ') }} F
                    </span>
                </div>

                <!-- Informations stock -->
                <div class="flex justify-between items-center mb-3">
                    <div class="flex items-center">
                        <span class="text-sm font-medium product-quantity {{ $product->quantite < 10 ? 'text-red-600' : 'text-gray-900' }}">
                            Stock: {{ $product->quantite }} unités
                        </span>
                        @if($product->quantite == 0)
                            <span class="ml-2 bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">🔴 Rupture</span>
                        @elseif($product->quantite < 10)
                            <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">🟡 Faible</span>
                        @else
                            <span class="ml-2 bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">🟢 Normal</span>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3 pt-3 border-t border-gray-200">
                    <a href="{{ route('products.edit', $product) }}" 
                       class="text-[#ee8f13] hover:text-[#d67f11] text-xs flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Modifier
                    </a>
                    @if(auth()->user()->isAdmin() || auth()->user()->isGerant())
                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="text-red-600 hover:text-red-900 text-xs flex items-center"
                                onclick="return confirm('Supprimer ce produit ?')">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Supprimer
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6 text-center">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m8-8V4a1 1 0 00-1-1h-2a1 1 0 00-1 1v1M9 7h6"></path>
                </svg>
                <p class="text-sm font-medium text-gray-600">Aucun produit trouvé</p>
                <p class="text-xs text-gray-500 mt-1">Commencez par créer votre premier produit</p>
                <a href="{{ route('products.create') }}" class="inline-block mt-3 bg-[#0b5f37] text-white px-4 py-2 rounded text-sm hover:bg-[#0a4d2c]">
                    ➕ Créer un produit
                </a>
            </div>
            @endforelse
        </div>

        <!-- Message aucun résultat -->
        <div id="no-results" class="hidden bg-white rounded-lg shadow border border-gray-200 p-6 text-center">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <p class="text-sm font-medium text-gray-600">Aucun produit ne correspond à votre recherche</p>
            <p class="text-xs text-gray-500 mt-1">Essayez avec d'autres termes ou vérifiez l'orthographe</p>
            <button type="button" id="reset-search" class="inline-block mt-3 bg-gray-500 text-white px-4 py-2 rounded text-sm hover:bg-gray-600">
                🔄 Afficher tous les produits
            </button>
        </div>

        <!-- Statistiques produits -->
        @if($products->count() > 0)
        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-[#0b5f37] text-white p-3 rounded shadow">
                <div class="flex items-center">
                    <div class="text-lg mr-2">📦</div>
                    <div>
                        <h3 class="text-xs font-semibold opacity-90">Total Produits</h3>
                        <p class="text-sm font-bold" id="stats-total">{{ $products->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-[#ee8f13] text-white p-3 rounded shadow">
                <div class="flex items-center">
                    <div class="text-lg mr-2">💰</div>
                    <div>
                        <h3 class="text-xs font-semibold opacity-90">Valeur Stock</h3>
                        <p class="text-sm font-bold" id="stats-value">
                            {{ number_format($products->sum(function($product) { return $product->quantite * $product->prix; }), 0, ',', ' ') }} F
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-[#8c52ff] text-white p-3 rounded shadow">
                <div class="flex items-center">
                    <div class="text-lg mr-2">⚠️</div>
                    <div>
                        <h3 class="text-xs font-semibold opacity-90">Stock Faible</h3>
                        <p class="text-sm font-bold" id="stats-low">{{ $products->where('quantite', '<', 10)->where('quantite', '>', 0)->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-[#cb6ce6] text-white p-3 rounded shadow">
                <div class="flex items-center">
                    <div class="text-lg mr-2">🔴</div>
                    <div>
                        <h3 class="text-xs font-semibold opacity-90">Rupture</h3>
                        <p class="text-sm font-bold" id="stats-out">{{ $products->where('quantite', 0)->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertes stock -->
        @php
            $lowStockProducts = $products->where('quantite', '<', 10)->where('quantite', '>', 0);
            $outOfStockProducts = $products->where('quantite', 0);
        @endphp

        @if($lowStockProducts->count() > 0 || $outOfStockProducts->count() > 0)
        <div class="mt-4 space-y-3">
            @if($outOfStockProducts->count() > 0)
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Rupture de stock</h3>
                        <p class="text-sm text-red-700 mt-1">
                            <span id="alert-out-of-stock">{{ $outOfStockProducts->count() }}</span> produit(s) en rupture de stock
                        </p>
                    </div>
                </div>
            </div>
            @endif

            @if($lowStockProducts->count() > 0)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Stock faible</h3>
                        <p class="text-sm text-yellow-700 mt-1">
                            <span id="alert-low-stock">{{ $lowStockProducts->count() }}</span> produit(s) avec stock faible
                        </p>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('product-search');
    const clearSearchBtn = document.getElementById('clear-search');
    const resetSearchBtn = document.getElementById('reset-search');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const productRows = document.querySelectorAll('.product-row');
    const productCards = document.querySelectorAll('.product-card');
    const noResults = document.getElementById('no-results');
    const productCount = document.getElementById('product-count');
    const productsTableBody = document.getElementById('products-table-body');
    const productsMobileList = document.getElementById('products-mobile-list');
    const refreshBtn = document.getElementById('refresh-products');
    
    // Éléments de statistiques
    const statsTotal = document.getElementById('stats-total');
    const statsValue = document.getElementById('stats-value');
    const statsLow = document.getElementById('stats-low');
    const statsOut = document.getElementById('stats-out');
    const alertLowStock = document.getElementById('alert-low-stock');
    const alertOutOfStock = document.getElementById('alert-out-of-stock');

    let currentFilter = 'all';
    let currentSearch = '';

    // Fonction pour rafraîchir les données
    async function refreshProducts() {
        try {
            refreshBtn.disabled = true;
            refreshBtn.innerHTML = '⏳ Actualisation...';
            
            // CORRECTION: Utilisation de l'URL directe au lieu de la route nommée
            const response = await fetch('{{ route("products.refresh") }}', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                updateProductsDisplay(data.products);
                showNotification('✅ Données mises à jour', 'success');
            }
        } catch (error) {
            console.error('Erreur lors du rafraîchissement:', error);
            showNotification('❌ Erreur lors de la mise à jour', 'error');
        } finally {
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = '🔄 Actualiser';
        }
    }

    // Fonction pour mettre à jour l'affichage des produits
    function updateProductsDisplay(products) {
        // Mettre à jour les données des attributs
        productRows.forEach(row => {
            const productId = parseInt(row.getAttribute('data-product-id'));
            const product = products.find(p => p.id === productId);
            
            if (product) {
                // Mettre à jour les attributs
                row.setAttribute('data-quantite', product.quantite);
                row.setAttribute('data-stock-status', product.stock_status);
                
                // Mettre à jour l'affichage
                const quantityElement = row.querySelector('.product-quantity');
                if (quantityElement) {
                    quantityElement.textContent = product.quantite;
                    
                    // Mettre à jour les classes de couleur
                    quantityElement.className = 'text-sm font-medium product-quantity ' + 
                        (product.quantite < 10 ? 'text-red-600' : 'text-gray-900');
                }
            }
        });

        // Mettre à jour les cartes mobiles
        productCards.forEach(card => {
            const productId = parseInt(card.getAttribute('data-product-id'));
            const product = products.find(p => p.id === productId);
            
            if (product) {
                // Mettre à jour les attributs
                card.setAttribute('data-quantite', product.quantite);
                card.setAttribute('data-stock-status', product.stock_status);
                
                // Mettre à jour l'affichage
                const quantityElement = card.querySelector('.product-quantity');
                if (quantityElement) {
                    quantityElement.textContent = `Stock: ${product.quantite} unités`;
                    
                    // Mettre à jour les classes de couleur
                    quantityElement.className = 'text-sm font-medium product-quantity ' + 
                        (product.quantite < 10 ? 'text-red-600' : 'text-gray-900');
                }
            }
        });

        // Re-filtrer pour mettre à jour les compteurs
        filterProducts();
    }

    function filterProducts() {
        let visibleCount = 0;
        let totalValue = 0;
        let lowStockCount = 0;
        let outOfStockCount = 0;

        // Filtrer les lignes du tableau desktop
        productRows.forEach(row => {
            const designation = row.getAttribute('data-designation');
            const categorie = row.getAttribute('data-categorie');
            const prix = row.getAttribute('data-prix');
            const quantite = parseInt(row.getAttribute('data-quantite'));
            const stockStatus = row.getAttribute('data-stock-status');

            const matchesSearch = currentSearch === '' || 
                designation.includes(currentSearch.toLowerCase()) ||
                categorie.includes(currentSearch.toLowerCase()) ||
                prix.includes(currentSearch);

            const matchesFilter = currentFilter === 'all' || 
                (currentFilter === 'in-stock' && stockStatus === 'in-stock') ||
                (currentFilter === 'low-stock' && stockStatus === 'low-stock') ||
                (currentFilter === 'out-of-stock' && stockStatus === 'out-of-stock');

            const isVisible = matchesSearch && matchesFilter;

            if (isVisible) {
                row.style.display = '';
                visibleCount++;
                totalValue += quantite * parseFloat(prix);
                if (stockStatus === 'low-stock') lowStockCount++;
                if (stockStatus === 'out-of-stock') outOfStockCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Filtrer les cartes mobile
        productCards.forEach(card => {
            const designation = card.getAttribute('data-designation');
            const categorie = card.getAttribute('data-categorie');
            const prix = card.getAttribute('data-prix');
            const quantite = parseInt(card.getAttribute('data-quantite'));
            const stockStatus = card.getAttribute('data-stock-status');

            const matchesSearch = currentSearch === '' || 
                designation.includes(currentSearch.toLowerCase()) ||
                categorie.includes(currentSearch.toLowerCase()) ||
                prix.includes(currentSearch);

            const matchesFilter = currentFilter === 'all' || 
                (currentFilter === 'in-stock' && stockStatus === 'in-stock') ||
                (currentFilter === 'low-stock' && stockStatus === 'low-stock') ||
                (currentFilter === 'out-of-stock' && stockStatus === 'out-of-stock');

            const isVisible = matchesSearch && matchesFilter;

            if (isVisible) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });

        // Afficher/masquer le message "aucun résultat"
        if (visibleCount === 0) {
            noResults.classList.remove('hidden');
            productsTableBody.style.display = 'none';
            productsMobileList.style.display = 'none';
        } else {
            noResults.classList.add('hidden');
            productsTableBody.style.display = '';
            productsMobileList.style.display = 'block';
        }

        // Mettre à jour les compteurs
        productCount.textContent = visibleCount;
        
        // Mettre à jour les statistiques si elles existent
        if (statsTotal) statsTotal.textContent = visibleCount;
        if (statsValue) statsValue.textContent = formatCurrency(totalValue);
        if (statsLow) statsLow.textContent = lowStockCount;
        if (statsOut) statsOut.textContent = outOfStockCount;
        if (alertLowStock) alertLowStock.textContent = lowStockCount;
        if (alertOutOfStock) alertOutOfStock.textContent = outOfStockCount;

        // Afficher/masquer le bouton effacer
        if (currentSearch !== '') {
            clearSearchBtn.classList.remove('hidden');
        } else {
            clearSearchBtn.classList.add('hidden');
        }
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat().format(Math.round(amount)) + ' F';
    }

    function showNotification(message, type) {
        // Créer une notification simple
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg ${
            type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Supprimer après 3 secondes
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Événement de recherche
    searchInput.addEventListener('input', function() {
        currentSearch = this.value.toLowerCase();
        filterProducts();
    });

    // Bouton effacer la recherche
    clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        currentSearch = '';
        filterProducts();
        searchInput.focus();
    });

    // Bouton réinitialiser la recherche
    resetSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        currentSearch = '';
        currentFilter = 'all';
        
        // Réactiver le filtre "tous"
        filterBtns.forEach(btn => {
            if (btn.getAttribute('data-filter') === 'all') {
                btn.classList.add('active', 'bg-[#0b5f37]');
                btn.classList.remove('bg-gray-400');
            } else {
                btn.classList.remove('active', 'bg-[#0b5f37]');
                btn.classList.add('bg-gray-400');
            }
        });
        
        filterProducts();
        searchInput.focus();
    });

    // Filtres rapides
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            // Mettre à jour l'état actif des boutons
            filterBtns.forEach(b => {
                if (b === this) {
                    b.classList.add('active', 'bg-[#0b5f37]');
                    b.classList.remove('bg-gray-400');
                } else {
                    b.classList.remove('active', 'bg-[#0b5f37]');
                    b.classList.add('bg-gray-400');
                }
            });
            
            currentFilter = filter;
            filterProducts();
        });
    });

    // Bouton rafraîchir
    refreshBtn.addEventListener('click', refreshProducts);

    // Recherche avec la touche Entrée
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
        }
    });

    // Focus sur la recherche au chargement
    searchInput.focus();

    // Rafraîchissement automatique toutes les 30 secondes
    setInterval(refreshProducts, 30000);
});
</script>

<style>
/* Animation pour les cartes produit */
.bg-white {
    transition: all 0.2s ease;
}

.bg-white:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Style pour les filtres */
.filter-btn {
    transition: all 0.2s ease;
    cursor: pointer;
}

.filter-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.filter-btn.active {
    box-shadow: 0 2px 8px rgba(11, 95, 55, 0.3);
}

/* Style pour la barre de recherche */
#product-search:focus {
    box-shadow: 0 0 0 3px rgba(11, 95, 55, 0.1);
}

/* Amélioration de l'accessibilité */
@media (max-width: 640px) {
    .bg-white {
        margin: 0 -0.5rem;
    }
}

/* Style pour les indicateurs de stock */
.bg-red-100 { background-color: #fee2e2; }
.bg-yellow-100 { background-color: #fef3c7; }
.bg-green-100 { background-color: #dcfce7; }

/* Animation pour l'apparition des résultats */
.product-row, .product-card {
    transition: all 0.3s ease;
}

/* Style pour le bouton rafraîchir */
#refresh-products {
    transition: all 0.2s ease;
}

#refresh-products:hover:not(:disabled) {
    transform: scale(1.05);
}

#refresh-products:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
@endsection