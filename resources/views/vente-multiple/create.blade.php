@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold text-[#0b5f37] mb-6">Nouvelle Vente Multiple</h1>

        <div class="bg-white rounded-lg shadow p-6">
            <form id="vente-multiple-form" action="{{ route('ventes-multiples.store') }}" method="POST">
                @csrf
                
                <!-- Liste des produits ajoutés -->
                <div id="produits-list" class="mb-6 space-y-4">
                    <!-- Les produits seront ajoutés ici dynamiquement -->
                </div>

                <!-- Total -->
                <div class="bg-gray-50 p-4 rounded mb-6">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold">Total:</span>
                        <span id="total-montant" class="text-2xl font-bold text-[#0b5f37]">0 FCFA</span>
                    </div>
                </div>

                <!-- Sélection de produit -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold text-[#0b5f37] mb-4">Ajouter un produit</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label for="product_id" class="block text-sm font-medium text-gray-700">Produit</label>
                            <select id="product_id" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37]">
                                <option value="">Sélectionnez un produit</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" 
                                            data-prix="{{ $product->prix }}"
                                            data-stock="{{ $product->quantite }}"
                                            data-designation="{{ $product->designation }}">
                                        {{ $product->designation }} - {{ number_format($product->prix, 0, ',', ' ') }} FCFA (Stock: {{ $product->quantite }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="quantite" class="block text-sm font-medium text-gray-700">Quantité</label>
                            <input type="number" id="quantite" min="1" value="1"
                                   class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37]">
                        </div>
                        <div class="flex items-end">
                            <button type="button" id="ajouter-produit" 
                                    class="bg-[#0b5f37] text-white px-4 py-2 rounded hover:bg-[#0a4d2c] w-full">
                                ➕ Ajouter
                            </button>
                        </div>
                    </div>
                    <div id="product-info" class="text-sm text-gray-600 mb-4"></div>
                </div>

                <!-- Boutons d'action -->
                <div class="flex justify-end space-x-3 mt-6">
                    <a href="{{ route('ventes.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Annuler
                    </a>
                    <button type="submit" id="submit-btn" 
                            class="bg-[#0b5f37] text-white px-6 py-2 rounded hover:bg-[#0a4d2c] font-semibold opacity-50 cursor-not-allowed"
                            disabled>
                        💰 Enregistrer la Vente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Template pour un produit ajouté -->
<template id="produit-template">
    <div class="produit-item bg-gray-50 p-4 rounded border" data-product-id="">
        <div class="flex justify-between items-center">
            <div class="flex-1">
                <h4 class="font-semibold produit-designation"></h4>
                <div class="text-sm text-gray-600">
                    <span class="produit-prix"></span> FCFA x <span class="produit-quantite"></span> = 
                    <span class="font-semibold produit-montant"></span> FCFA
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button type="button" class="modifier-quantite bg-[#ee8f13] text-white px-2 py-1 rounded text-sm hover:bg-[#d67f11]">
                    ✏️
                </button>
                <button type="button" class="supprimer-produit bg-red-600 text-white px-2 py-1 rounded text-sm hover:bg-red-700">
                    ❌
                </button>
                <input type="hidden" name="products[][product_id]" value="">
                <input type="hidden" name="products[][quantite]" value="">
            </div>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const produitsList = document.getElementById('produits-list');
    const productSelect = document.getElementById('product_id');
    const quantiteInput = document.getElementById('quantite');
    const ajouterBtn = document.getElementById('ajouter-produit');
    const totalMontant = document.getElementById('total-montant');
    const submitBtn = document.getElementById('submit-btn');
    const productInfo = document.getElementById('product-info');
    const produitTemplate = document.getElementById('produit-template');
    
    let produits = [];
    let total = 0;

    function updateTotal() {
        total = produits.reduce((sum, produit) => sum + produit.montant, 0);
        totalMontant.textContent = new Intl.NumberFormat().format(total) + ' FCFA';
        
        // Activer/désactiver le bouton de soumission
        submitBtn.disabled = produits.length === 0;
        submitBtn.classList.toggle('opacity-50', produits.length === 0);
        submitBtn.classList.toggle('cursor-not-allowed', produits.length === 0);
    }

    function updateProductInfo() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        if (selectedOption.value) {
            const prix = selectedOption.dataset.prix;
            const stock = selectedOption.dataset.stock;
            const designation = selectedOption.dataset.designation;
            const quantite = parseInt(quantiteInput.value) || 1;
            const montant = prix * quantite;

            productInfo.innerHTML = `
                <strong>${designation}</strong> - Stock: ${stock} unités<br>
                Montant: ${new Intl.NumberFormat().format(montant)} FCFA
            `;
        } else {
            productInfo.innerHTML = '';
        }
    }

    function ajouterProduit() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        if (!selectedOption.value) {
            alert('Veuillez sélectionner un produit');
            return;
        }

        const productId = selectedOption.value;
        const prix = parseFloat(selectedOption.dataset.prix);
        const stock = parseInt(selectedOption.dataset.stock);
        const designation = selectedOption.dataset.designation;
        const quantite = parseInt(quantiteInput.value) || 1;

        // Vérifier si le produit est déjà dans la liste
        const existingIndex = produits.findIndex(p => p.productId === productId);
        if (existingIndex !== -1) {
            // Mettre à jour la quantité
            produits[existingIndex].quantite += quantite;
            produits[existingIndex].montant = produits[existingIndex].quantite * prix;
            mettreAJourAffichageProduit(existingIndex);
        } else {
            // Vérifier le stock
            if (quantite > stock) {
                alert(`Stock insuffisant pour ${designation}! Stock disponible: ${stock}`);
                return;
            }

            // Ajouter le produit
            const produit = {
                productId,
                designation,
                prix,
                quantite,
                montant: prix * quantite
            };
            produits.push(produit);
            afficherProduit(produit, produits.length - 1);
        }

        updateTotal();
        quantiteInput.value = 1;
        updateProductInfo();
    }

    function afficherProduit(produit, index) {
        const clone = produitTemplate.content.cloneNode(true);
        const item = clone.querySelector('.produit-item');
        
        item.setAttribute('data-product-id', produit.productId);
        item.setAttribute('data-index', index);
        
        item.querySelector('.produit-designation').textContent = produit.designation;
        item.querySelector('.produit-prix').textContent = new Intl.NumberFormat().format(produit.prix);
        item.querySelector('.produit-quantite').textContent = produit.quantite;
        item.querySelector('.produit-montant').textContent = new Intl.NumberFormat().format(produit.montant);
        
        item.querySelector('input[name="products[][product_id]"]').value = produit.productId;
        item.querySelector('input[name="products[][quantite]"]').value = produit.quantite;
        
        // Événement de suppression
        item.querySelector('.supprimer-produit').addEventListener('click', function() {
            produits.splice(index, 1);
            item.remove();
            reindexerProduits();
            updateTotal();
        });
        
        // Événement de modification
        item.querySelector('.modifier-quantite').addEventListener('click', function() {
            const nouvelleQuantite = prompt(`Modifier la quantité pour ${produit.designation}:`, produit.quantite);
            if (nouvelleQuantite && !isNaN(nouvelleQuantite) && nouvelleQuantite > 0) {
                produit.quantite = parseInt(nouvelleQuantite);
                produit.montant = produit.prix * produit.quantite;
                mettreAJourAffichageProduit(index);
                updateTotal();
            }
        });

        produitsList.appendChild(clone);
    }

    function mettreAJourAffichageProduit(index) {
        const produit = produits[index];
        const item = document.querySelector(`.produit-item[data-index="${index}"]`);
        
        if (item) {
            item.querySelector('.produit-quantite').textContent = produit.quantite;
            item.querySelector('.produit-montant').textContent = new Intl.NumberFormat().format(produit.montant);
            item.querySelector('input[name="products[][quantite]"]').value = produit.quantite;
        }
    }

    function reindexerProduits() {
        const items = produitsList.querySelectorAll('.produit-item');
        items.forEach((item, index) => {
            item.setAttribute('data-index', index);
        });
    }

    // Événements
    productSelect.addEventListener('change', updateProductInfo);
    quantiteInput.addEventListener('input', updateProductInfo);
    ajouterBtn.addEventListener('click', ajouterProduit);

    // Entrée pour ajouter un produit
    quantiteInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            ajouterProduit();
        }
    });

    // Initialisation
    updateProductInfo();
});
</script>
@endsection
