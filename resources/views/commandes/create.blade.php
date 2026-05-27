@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- En-tête -->
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-[#0b5f37]">Nouvelle Commande</h2>
                    <a href="{{ route('commandes.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">
                        ← Retour
                    </a>
                </div>

                <!-- Formulaire AVEC recherche de produits -->
                <form action="{{ route('commandes.store') }}" method="POST" id="commande-form">
                    @csrf
                    
                    <!-- Sélection de la table -->
                    <div class="mb-6">
                        <label for="table_id" class="block text-sm font-medium text-gray-700 mb-2">Table *</label>
                        <select name="table_id" id="table_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-[#0b5f37] focus:ring-[#0b5f37] @error('table_id') border-red-500 @enderror" required>
                            <option value="">Sélectionner une table</option>
                            @foreach ($tables as $table)
                                <option value="{{ $table->id }}" {{ old('table_id') == $table->id ? 'selected' : '' }}>
                                    {{ $table->nom_complet }} 
                                    @if($table->user)
                                        - Affectée à {{ $table->user->prenom }} {{ $table->user->name }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('table_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Produits avec recherche -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Produits</h3>
                            <div class="flex space-x-2">
                                <button type="button" id="mode-liste" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
                                    📋 Mode Liste
                                </button>
                                <button type="button" id="ajouter-produit" class="bg-[#0b5f37] text-white px-4 py-2 rounded text-sm hover:bg-[#0a4d2c]">
                                    + Ajouter un produit
                                </button>
                            </div>
                        </div>

                        <!-- Recherche de produits -->
                        <div class="mb-4 relative">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher un produit</label>
                            <input type="text" id="search-product" 
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-[#0b5f37] focus:ring-[#0b5f37]"
                                   placeholder="Tapez le nom d'un produit (ex: eau, coca, etc.)">
                            <div id="search-results" class="absolute left-0 right-0 mt-1 border border-gray-300 rounded-md bg-white shadow-lg max-h-60 overflow-y-auto z-50 hidden">
                                <!-- Les résultats de recherche apparaîtront ici -->
                            </div>
                        </div>

                        <!-- Liste de tous les produits (mode secours) -->
                        <div id="liste-produits" class="hidden mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sélectionner un produit</label>
                            <select id="select-produit" class="w-full border-gray-300 rounded-md shadow-sm focus:border-[#0b5f37] focus:ring-[#0b5f37]">
                                <option value="">Choisir un produit...</option>
                                @foreach($produits as $produit)
                                    <option value="{{ $produit->id }}" data-prix="{{ $produit->prix }}" data-stock="{{ $produit->quantite }}" data-designation="{{ $produit->designation }}">
                                        {{ $produit->designation }} - {{ number_format($produit->prix, 0, ',', ' ') }} FCFA (Stock: {{ $produit->quantite }})
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" id="ajouter-liste" class="mt-2 bg-[#0b5f37] text-white px-4 py-2 rounded text-sm hover:bg-[#0a4d2c] w-full">
                                Ajouter le produit sélectionné
                            </button>
                        </div>

                        <div id="produits-container">
                            <!-- Les produits ajoutés apparaîtront ici -->
                        </div>

                        <!-- Message si aucun produit -->
                        <div id="no-products" class="text-center py-8 text-gray-500 border-2 border-dashed border-gray-300 rounded-lg">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p class="mt-2">Aucun produit ajouté</p>
                            <p class="text-sm">Utilisez la recherche ou le mode liste pour ajouter des produits</p>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (optionnel)</label>
                        <textarea name="notes" id="notes" class="w-full border-gray-300 rounded-md shadow-sm focus:border-[#0b5f37] focus:ring-[#0b5f37]" rows="3" placeholder="Notes supplémentaires...">{{ old('notes') }}</textarea>
                    </div>
                    <!-- Description -->
       <div class="mb-6">
       <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description (optionnel)</label>
       <textarea name="description" id="description" class="w-full border-gray-300 rounded-md shadow-sm focus:border-[#0b5f37] focus:ring-[#0b5f37]" rows="4" placeholder="Description détaillée de la commande...">{{ old('description') }}</textarea>
      <p class="text-xs text-gray-500 mt-1">Maximum 1000 caractères</p>
     </div>

                    <!-- Résumé de la commande -->
                    <div class="mb-6 bg-gray-50 p-4 rounded-lg border">
                        <h4 class="font-medium text-gray-900 mb-3">Résumé de la commande</h4>
                        <div id="commande-summary">
                            <p class="text-gray-600">Aucun produit ajouté</p>
                        </div>
                        <div id="total-commande" class="mt-2 pt-2 border-t border-gray-200 hidden">
                            <p class="text-lg font-bold text-[#0b5f37]">Total: <span id="total-amount">0</span> FCFA</p>
                        </div>
                    </div>

                    <!-- Boutons -->
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('commandes.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 transition-colors">
                            Annuler
                        </a>
                        <button type="submit" id="submit-btn" class="bg-[#0b5f37] text-white px-6 py-2 rounded hover:bg-[#0a4d2c] transition-colors" disabled>
                            Créer la Commande
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let produitCount = 0;
    const produitsContainer = document.getElementById('produits-container');
    const noProducts = document.getElementById('no-products');
    const searchInput = document.getElementById('search-product');
    const searchResults = document.getElementById('search-results');
    const listeProduits = document.getElementById('liste-produits');
    const selectProduit = document.getElementById('select-produit');
    const commandeSummary = document.getElementById('commande-summary');
    const totalCommande = document.getElementById('total-commande');
    const totalAmount = document.getElementById('total-amount');
    const submitBtn = document.getElementById('submit-btn');
    const modeListeBtn = document.getElementById('mode-liste');
    const ajouterListeBtn = document.getElementById('ajouter-liste');
    const produitsAjoutes = new Set();

    // Basculer entre mode recherche et mode liste
    modeListeBtn.addEventListener('click', function() {
        const isListeVisible = !listeProduits.classList.contains('hidden');
        
        if (isListeVisible) {
            // Passer en mode recherche
            listeProduits.classList.add('hidden');
            modeListeBtn.textContent = '📋 Mode Liste';
            modeListeBtn.classList.remove('bg-blue-700');
            modeListeBtn.classList.add('bg-blue-600');
        } else {
            // Passer en mode liste
            listeProduits.classList.remove('hidden');
            modeListeBtn.textContent = '🔍 Mode Recherche';
            modeListeBtn.classList.remove('bg-blue-600');
            modeListeBtn.classList.add('bg-blue-700');
        }
    });

    // Ajouter un produit depuis la liste
    ajouterListeBtn.addEventListener('click', function() {
        const selectedOption = selectProduit.options[selectProduit.selectedIndex];
        
        if (!selectedOption.value) {
            alert('Veuillez sélectionner un produit');
            return;
        }

        const product = {
            id: selectedOption.value,
            designation: selectedOption.dataset.designation,
            prix: selectedOption.dataset.prix,
            quantite: selectedOption.dataset.stock
        };

        ajouterProduit(product);
        selectProduit.value = '';
    });

    // Fonction pour effectuer la recherche
    function effectuerRecherche(query) {
        searchResults.innerHTML = '<div class="p-3 text-gray-500">🔍 Recherche en cours...</div>';
        searchResults.classList.remove('hidden');

        // Essayer d'abord avec la route API
        fetch(`/products/search?q=${encodeURIComponent(query)}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Route non disponible');
            }
            return response.json();
        })
        .then(products => {
            afficherResultatsRecherche(products, query);
        })
        .catch(error => {
            console.log('Recherche API échouée, utilisation du filtrage local');
            // Fallback: filtrage local des produits
            const produitsDisponibles = {!! json_encode($produits) !!};
            const produitsFiltres = produitsDisponibles.filter(produit => 
                produit.designation.toLowerCase().includes(query.toLowerCase()) ||
                (produit.code_barre && produit.code_barre.toLowerCase().includes(query.toLowerCase()))
            );
            afficherResultatsRecherche(produitsFiltres, query);
        });
    }

    // Afficher les résultats de recherche
    function afficherResultatsRecherche(products, query) {
        searchResults.innerHTML = '';
        
        if (products.length === 0) {
            searchResults.innerHTML = `
                <div class="p-3 text-gray-500">
                    <div>❌ Aucun produit trouvé pour "${query}"</div>
                    <div class="text-xs mt-1">Essayez un autre terme</div>
                </div>
            `;
        } else {
            products.forEach(product => {
                const productElement = document.createElement('div');
                productElement.className = `p-3 border-b border-gray-200 hover:bg-gray-50 cursor-pointer ${
                    produitsAjoutes.has(product.id) ? 'bg-yellow-50' : ''
                }`;
                productElement.innerHTML = `
                    <div class="flex justify-between items-center">
                        <div class="flex-1">
                            <div class="font-medium text-gray-900">${product.designation}</div>
                            <div class="text-sm text-gray-600 mt-1">
                                <span class="font-semibold">${Number(product.prix).toLocaleString()} FCFA</span> 
                                • Stock: ${product.quantite}
                                ${product.code_barre ? `• Code: ${product.code_barre}` : ''}
                            </div>
                        </div>
                        ${produitsAjoutes.has(product.id) ? 
                            '<span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded ml-2">Déjà ajouté</span>' : 
                            '<button type="button" class="bg-[#0b5f37] text-white px-3 py-2 rounded text-sm hover:bg-[#0a4d2c] ml-2 whitespace-nowrap">➕ Ajouter</button>'
                        }
                    </div>
                `;
                
                if (!produitsAjoutes.has(product.id)) {
                    const addButton = productElement.querySelector('button');
                    addButton.addEventListener('click', function(e) {
                        e.stopPropagation();
                        ajouterProduit(product);
                        searchInput.value = '';
                        searchResults.classList.add('hidden');
                    });

                    // Clic sur toute la ligne
                    productElement.addEventListener('click', function(e) {
                        if (!e.target.closest('button')) {
                            ajouterProduit(product);
                            searchInput.value = '';
                            searchResults.classList.add('hidden');
                        }
                    });
                }
                
                searchResults.appendChild(productElement);
            });
        }
    }

    // Recherche de produits avec délai
    let timeoutId;
    searchInput.addEventListener('input', function(e) {
        const query = e.target.value.trim();
        
        clearTimeout(timeoutId);
        
        if (query.length < 2) {
            searchResults.classList.add('hidden');
            return;
        }

        timeoutId = setTimeout(() => {
            effectuerRecherche(query);
        }, 300);
    });

    // Cacher les résultats quand on clique ailleurs
    document.addEventListener('click', function(e) {
        if (!searchResults.contains(e.target) && e.target !== searchInput) {
            searchResults.classList.add('hidden');
        }
    });

    // Fonction pour ajouter un produit
    function ajouterProduit(product) {
        if (produitsAjoutes.has(product.id)) {
            alert('⚠️ Ce produit est déjà dans la commande');
            return;
        }

        if (product.quantite <= 0) {
            alert('❌ Ce produit est en rupture de stock');
            return;
        }

        produitCount++;
        produitsAjoutes.add(product.id);

        const produitDiv = document.createElement('div');
        produitDiv.className = 'produit-item grid grid-cols-1 md:grid-cols-12 gap-4 mb-4 p-4 border border-gray-200 rounded-lg bg-white shadow-sm';
        produitDiv.innerHTML = `
            <input type="hidden" name="produits[${produitCount}][produit_id]" value="${product.id}">
            
            <div class="md:col-span-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Produit</label>
                <div class="p-3 bg-gray-50 rounded border">
                    <div class="font-medium text-gray-900">${product.designation}</div>
                    <div class="text-sm text-gray-600 mt-1">
                        ${Number(product.prix).toLocaleString()} FCFA • Stock: ${product.quantite}
                    </div>
                </div>
            </div>
            
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantité *</label>
                <input type="number" name="produits[${produitCount}][quantite]" 
                       class="quantite-input w-full border-gray-300 rounded-md shadow-sm focus:border-[#0b5f37] focus:ring-[#0b5f37] px-3 py-2" 
                       min="1" max="${product.quantite}" value="1" required
                       data-prix="${product.prix}" data-stock="${product.quantite}">
                <div class="text-xs text-gray-500 mt-1">
                    Stock disponible: <span class="stock-display font-medium">${product.quantite}</span>
                </div>
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Prix unitaire</label>
                <div class="p-3 bg-gray-50 rounded border text-sm font-semibold text-gray-900">
                    ${Number(product.prix).toLocaleString()} FCFA
                </div>
            </div>
            
            <div class="md:col-span-2 flex items-end">
                <button type="button" class="supprimer-produit bg-red-600 text-white px-3 py-2 rounded text-sm hover:bg-red-700 w-full transition-colors">
                    🗑️ Supprimer
                </button>
            </div>
        `;

        produitsContainer.appendChild(produitDiv);
        noProducts.classList.add('hidden');

        // Événements pour le nouveau produit
        const quantiteInput = produitDiv.querySelector('.quantite-input');
        const supprimerBtn = produitDiv.querySelector('.supprimer-produit');

        quantiteInput.addEventListener('input', function() {
            const quantite = parseInt(this.value) || 0;
            const stock = parseInt(this.dataset.stock);
            
            // CORRECTION: Permettre de supprimer complètement la valeur
            if (this.value === '') {
                return; // Laisser l'utilisateur supprimer la valeur
            }
            
            if (quantite > stock) {
                this.value = stock;
                alert(`⚠️ Quantité limitée à ${stock} (stock disponible)`);
            }
            
            if (quantite < 1) {
                this.value = 1;
            }
            
            mettreAJourResume();
        });

        // CORRECTION: Gérer la perte de focus pour s'assurer qu'une valeur valide est définie
        quantiteInput.addEventListener('blur', function() {
            if (this.value === '' || parseInt(this.value) < 1) {
                this.value = 1;
                mettreAJourResume();
            }
        });

        supprimerBtn.addEventListener('click', function() {
            if (confirm('Voulez-vous vraiment supprimer ce produit de la commande ?')) {
                produitsAjoutes.delete(product.id);
                produitDiv.remove();
                verifierProduitsVides();
                mettreAJourResume();
            }
        });

        mettreAJourResume();
        updateSubmitButton();
    }

    // Mettre à jour le résumé de la commande
    function mettreAJourResume() {
        const produits = document.querySelectorAll('.produit-item');
        let total = 0;
        let html = '';

        if (produits.length === 0) {
            html = '<p class="text-gray-600">Aucun produit ajouté</p>';
        } else {
            produits.forEach((produit, index) => {
                const quantiteInput = produit.querySelector('.quantite-input');
                const quantite = parseInt(quantiteInput.value) || 0;
                const prix = parseFloat(quantiteInput.dataset.prix) || 0;
                const soustotal = quantite * prix;
                total += soustotal;

                const nomProduit = produit.querySelector('.font-medium').textContent;
                html += `
                    <div class="flex justify-between items-center py-2 ${index > 0 ? 'border-t border-gray-200' : ''}">
                        <div class="flex-1">
                            <span class="text-sm text-gray-900">${nomProduit}</span>
                            <span class="text-xs text-gray-500 ml-2">× ${quantite}</span>
                        </div>
                        <span class="text-sm font-semibold text-[#0b5f37]">${soustotal.toLocaleString()} FCFA</span>
                    </div>
                `;
            });
        }

        commandeSummary.innerHTML = html;
        
        if (produits.length > 0) {
            totalAmount.textContent = total.toLocaleString();
            totalCommande.classList.remove('hidden');
        } else {
            totalCommande.classList.add('hidden');
        }
    }

    // Vérifier si aucun produit
    function verifierProduitsVides() {
        if (produitsContainer.children.length === 0) {
            noProducts.classList.remove('hidden');
        } else {
            noProducts.classList.add('hidden');
        }
    }

    // Mettre à jour le bouton de soumission
    function updateSubmitButton() {
        const hasProducts = produitsContainer.children.length > 0;
        submitBtn.disabled = !hasProducts;
        
        if (hasProducts) {
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    // Ajouter un produit manuellement (focus sur la recherche)
    document.getElementById('ajouter-produit').addEventListener('click', function() {
        searchInput.focus();
    });

    // Initialisation
    updateSubmitButton();
    verifierProduitsVides();
});
</script>

<style>
.produit-item {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.quantite-input:invalid {
    border-color: #ef4444;
    background-color: #fef2f2;
}

#search-results {
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

#submit-btn:disabled {
    background-color: #9ca3af;
    cursor: not-allowed;
}
</style>
@endsection