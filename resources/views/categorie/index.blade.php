@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="px-3 sm:px-6">
        <!-- En-tête -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-[#0b5f37]">Gestion des Catégories</h1>
                <p class="text-sm text-gray-600 mt-1"><span id="category-count">{{ $categories->count() }}</span> catégorie(s)</p>
            </div>
            <a href="{{ route('categories.create') }}" class="bg-[#0b5f37] text-white px-4 py-2 rounded text-sm hover:bg-[#0a4d2c] w-full sm:w-auto text-center">
                ➕ Nouvelle Catégorie
            </a>
        </div>

        <!-- Barre de recherche -->
        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <div class="relative">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 relative">
                        <label for="category-search" class="block text-sm font-medium text-gray-700 mb-1">Rechercher une catégorie</label>
                        <input type="text" 
                               id="category-search" 
                               class="w-full border border-gray-300 rounded-md px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0b5f37] focus:border-[#0b5f37]"
                               placeholder="Tapez le nom, description ou nombre de produits...">
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
                        Tous (<span id="count-all">{{ $categories->count() }}</span>)
                    </button>
                    <button type="button" class="filter-btn bg-green-600 text-white px-3 py-1 rounded-full text-xs" data-filter="with-products">
                        Avec produits (<span id="count-with-products">{{ $categories->where('products_count', '>', 0)->count() }}</span>)
                    </button>
                    <button type="button" class="filter-btn bg-blue-600 text-white px-3 py-1 rounded-full text-xs" data-filter="with-description">
                        Avec description (<span id="count-with-description">{{ $categories->where('description', '!=', null)->count() }}</span>)
                    </button>
                    <button type="button" class="filter-btn bg-gray-600 text-white px-3 py-1 rounded-full text-xs" data-filter="empty">
                        Vides (<span id="count-empty">{{ $categories->where('products_count', 0)->count() }}</span>)
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
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produits</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="categories-table-body" class="bg-white divide-y divide-gray-200">
                        @foreach($categories as $categorie)
                        <tr class="category-row hover:bg-gray-50" 
                            data-nom="{{ strtolower($categorie->nom) }}"
                            data-description="{{ $categorie->description ? strtolower($categorie->description) : '' }}"
                            data-products-count="{{ $categorie->products_count }}"
                            data-has-description="{{ $categorie->description ? 'yes' : 'no' }}"
                            data-is-empty="{{ $categorie->products_count === 0 ? 'yes' : 'no' }}">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">{{ $categorie->nom }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-500 max-w-xs">
                                    @if($categorie->description)
                                        {{ Str::limit($categorie->description, 50) }}
                                    @else
                                        <span class="text-gray-400">Aucune description</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                    {{ $categorie->products_count > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $categorie->products_count }} produit(s)
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $categorie->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('categories.edit', $categorie) }}" 
                                       class="text-[#ee8f13] hover:text-[#d67f11] text-sm flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Modifier
                                    </a>
                                    @if($categorie->products_count === 0)
                                    <form action="{{ route('categories.destroy', $categorie) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900 text-sm flex items-center"
                                                onclick="return confirm('Supprimer cette catégorie ?')">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Supprimer
                                        </button>
                                    </form>
                                    @else
                                    <span class="text-gray-400 text-xs" title="Impossible de supprimer - Catégorie utilisée">
                                        🔒
                                    </span>
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
        <div id="categories-mobile-list" class="sm:hidden space-y-3">
            @forelse($categories as $categorie)
            <div class="category-card bg-white rounded-lg shadow border border-gray-200 p-4"
                 data-nom="{{ strtolower($categorie->nom) }}"
                 data-description="{{ $categorie->description ? strtolower($categorie->description) : '' }}"
                 data-products-count="{{ $categorie->products_count }}"
                 data-has-description="{{ $categorie->description ? 'yes' : 'no' }}"
                 data-is-empty="{{ $categorie->products_count === 0 ? 'yes' : 'no' }}">
                <!-- En-tête de la carte -->
                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-gray-900">{{ $categorie->nom }}</h3>
                        @if($categorie->description)
                        <p class="text-xs text-gray-600 mt-1">{{ Str::limit($categorie->description, 60) }}</p>
                        @endif
                    </div>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                        {{ $categorie->products_count > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $categorie->products_count }}
                    </span>
                </div>

                <!-- Informations supplémentaires -->
                <div class="flex justify-between items-center text-xs text-gray-500 mb-3">
                    <span>Créée le {{ $categorie->created_at->format('d/m/Y') }}</span>
                    <span>{{ $categorie->products_count }} produit(s)</span>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3 pt-3 border-t border-gray-200">
                    <a href="{{ route('categories.edit', $categorie) }}" 
                       class="text-[#ee8f13] hover:text-[#d67f11] text-xs flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Modifier
                    </a>
                    @if($categorie->products_count === 0)
                    <form action="{{ route('categories.destroy', $categorie) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="text-red-600 hover:text-red-900 text-xs flex items-center"
                                onclick="return confirm('Supprimer cette catégorie ?')">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Supprimer
                        </button>
                    </form>
                    @else
                    <span class="text-gray-400 text-xs flex items-center" title="Catégorie utilisée - Impossible de supprimer">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Verrouillé
                    </span>
                    @endif
                </div>
            </div>
            @empty
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6 text-center">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <p class="text-sm font-medium text-gray-600">Aucune catégorie trouvée</p>
                <p class="text-xs text-gray-500 mt-1">Commencez par créer votre première catégorie</p>
                <a href="{{ route('categories.create') }}" class="inline-block mt-3 bg-[#0b5f37] text-white px-4 py-2 rounded text-sm hover:bg-[#0a4d2c]">
                    ➕ Créer une catégorie
                </a>
            </div>
            @endforelse
        </div>

        <!-- Message aucun résultat -->
        <div id="no-results" class="hidden bg-white rounded-lg shadow border border-gray-200 p-6 text-center">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <p class="text-sm font-medium text-gray-600">Aucune catégorie ne correspond à votre recherche</p>
            <p class="text-xs text-gray-500 mt-1">Essayez avec d'autres termes ou vérifiez l'orthographe</p>
            <button type="button" id="reset-search" class="inline-block mt-3 bg-gray-500 text-white px-4 py-2 rounded text-sm hover:bg-gray-600">
                🔄 Afficher toutes les catégories
            </button>
        </div>

        <!-- Statistiques -->
        @if($categories->count() > 0)
        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-[#0b5f37] text-white p-3 rounded shadow">
                <div class="flex items-center">
                    <div class="text-lg mr-2">🏷️</div>
                    <div>
                        <h3 class="text-xs font-semibold opacity-90">Total</h3>
                        <p class="text-sm font-bold" id="stats-total">{{ $categories->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-[#ee8f13] text-white p-3 rounded shadow">
                <div class="flex items-center">
                    <div class="text-lg mr-2">📦</div>
                    <div>
                        <h3 class="text-xs font-semibold opacity-90">Avec produits</h3>
                        <p class="text-sm font-bold" id="stats-with-products">{{ $categories->where('products_count', '>', 0)->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-[#8c52ff] text-white p-3 rounded shadow">
                <div class="flex items-center">
                    <div class="text-lg mr-2">📝</div>
                    <div>
                        <h3 class="text-xs font-semibold opacity-90">Avec description</h3>
                        <p class="text-sm font-bold" id="stats-with-description">{{ $categories->where('description', '!=', null)->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-[#cb6ce6] text-white p-3 rounded shadow">
                <div class="flex items-center">
                    <div class="text-lg mr-2">🆓</div>
                    <div>
                        <h3 class="text-xs font-semibold opacity-90">Vides</h3>
                        <p class="text-sm font-bold" id="stats-empty">{{ $categories->where('products_count', 0)->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conseils d'organisation -->
        <div class="mt-4 bg-blue-50 rounded p-4 border border-blue-200">
            <h4 class="font-semibold text-blue-800 mb-2 text-sm flex items-center">
                🎯 Conseils d'organisation
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-blue-700">
                <div class="flex items-start">
                    <span class="mr-2">•</span>
                    <span>Regroupez les catégories similaires</span>
                </div>
                <div class="flex items-start">
                    <span class="mr-2">•</span>
                    <span>Supprimez les catégories inutilisées</span>
                </div>
                <div class="flex items-start">
                    <span class="mr-2">•</span>
                    <span>Ajoutez des descriptions explicites</span>
                </div>
                <div class="flex items-start">
                    <span class="mr-2">•</span>
                    <span>Limitez le nombre de catégories principales</span>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('category-search');
    const clearSearchBtn = document.getElementById('clear-search');
    const resetSearchBtn = document.getElementById('reset-search');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const categoryRows = document.querySelectorAll('.category-row');
    const categoryCards = document.querySelectorAll('.category-card');
    const noResults = document.getElementById('no-results');
    const categoryCount = document.getElementById('category-count');
    const categoriesTableBody = document.getElementById('categories-table-body');
    const categoriesMobileList = document.getElementById('categories-mobile-list');
    
    // Éléments de statistiques
    const statsTotal = document.getElementById('stats-total');
    const statsWithProducts = document.getElementById('stats-with-products');
    const statsWithDescription = document.getElementById('stats-with-description');
    const statsEmpty = document.getElementById('stats-empty');

    let currentFilter = 'all';
    let currentSearch = '';

    function filterCategories() {
        let visibleCount = 0;
        let withProductsCount = 0;
        let withDescriptionCount = 0;
        let emptyCount = 0;

        // Filtrer les lignes du tableau desktop
        categoryRows.forEach(row => {
            const nom = row.getAttribute('data-nom');
            const description = row.getAttribute('data-description');
            const productsCount = parseInt(row.getAttribute('data-products-count'));
            const hasDescription = row.getAttribute('data-has-description');
            const isEmpty = row.getAttribute('data-is-empty');

            const matchesSearch = currentSearch === '' || 
                nom.includes(currentSearch.toLowerCase()) ||
                description.includes(currentSearch.toLowerCase()) ||
                productsCount.toString().includes(currentSearch);

            const matchesFilter = currentFilter === 'all' || 
                (currentFilter === 'with-products' && productsCount > 0) ||
                (currentFilter === 'with-description' && hasDescription === 'yes') ||
                (currentFilter === 'empty' && isEmpty === 'yes');

            const isVisible = matchesSearch && matchesFilter;

            if (isVisible) {
                row.style.display = '';
                visibleCount++;
                if (productsCount > 0) withProductsCount++;
                if (hasDescription === 'yes') withDescriptionCount++;
                if (isEmpty === 'yes') emptyCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Filtrer les cartes mobile
        categoryCards.forEach(card => {
            const nom = card.getAttribute('data-nom');
            const description = card.getAttribute('data-description');
            const productsCount = parseInt(card.getAttribute('data-products-count'));
            const hasDescription = card.getAttribute('data-has-description');
            const isEmpty = card.getAttribute('data-is-empty');

            const matchesSearch = currentSearch === '' || 
                nom.includes(currentSearch.toLowerCase()) ||
                description.includes(currentSearch.toLowerCase()) ||
                productsCount.toString().includes(currentSearch);

            const matchesFilter = currentFilter === 'all' || 
                (currentFilter === 'with-products' && productsCount > 0) ||
                (currentFilter === 'with-description' && hasDescription === 'yes') ||
                (currentFilter === 'empty' && isEmpty === 'yes');

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
            categoriesTableBody.style.display = 'none';
            categoriesMobileList.style.display = 'none';
        } else {
            noResults.classList.add('hidden');
            categoriesTableBody.style.display = '';
            categoriesMobileList.style.display = 'block';
        }

        // Mettre à jour les compteurs
        categoryCount.textContent = visibleCount;
        
        // Mettre à jour les statistiques si elles existent
        if (statsTotal) statsTotal.textContent = visibleCount;
        if (statsWithProducts) statsWithProducts.textContent = withProductsCount;
        if (statsWithDescription) statsWithDescription.textContent = withDescriptionCount;
        if (statsEmpty) statsEmpty.textContent = emptyCount;

        // Afficher/masquer le bouton effacer
        if (currentSearch !== '') {
            clearSearchBtn.classList.remove('hidden');
        } else {
            clearSearchBtn.classList.add('hidden');
        }
    }

    // Événement de recherche
    searchInput.addEventListener('input', function() {
        currentSearch = this.value.toLowerCase();
        filterCategories();
    });

    // Bouton effacer la recherche
    clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        currentSearch = '';
        filterCategories();
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
        
        filterCategories();
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
            filterCategories();
        });
    });

    // Recherche avec la touche Entrée
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
        }
    });

    // Focus sur la recherche au chargement
    searchInput.focus();
});
</script>

<style>
/* Animation pour les cartes */
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
#category-search:focus {
    box-shadow: 0 0 0 3px rgba(11, 95, 55, 0.1);
}

/* Amélioration de l'accessibilité */
@media (max-width: 640px) {
    .bg-white {
        margin: 0 -0.5rem;
    }
}

/* Animation pour l'apparition des résultats */
.category-row, .category-card {
    transition: all 0.3s ease;
}
</style>
@endsection