@extends('layouts.app')

@section('content')
<div class="py-4 sm:py-6">
    <div class="max-w-7xl mx-auto px-2 sm:px-0">
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-[#0b5f37]">✏️ Modifier la Commande</h1>
            <p class="text-gray-600">Modifiez les détails de la commande #{{ $commande->numero_commande }}</p>
            
            <!-- Indicateur de limite de temps -->
            @if(!$commande->peut_etre_modifiee)
            <div class="mt-4 p-4 bg-red-100 border border-red-400 rounded-lg">
                <div class="flex items-center">
                    <span class="text-red-600 mr-2">⏰</span>
                    <div>
                        <p class="font-semibold text-red-800">Temps de modification écoulé</p>
                        <p class="text-red-700 text-sm">
                            Cette commande a été créée il y a plus de 24h et ne peut plus être modifiée.
                        </p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow border p-6">
            <form action="{{ route('commandes.update', $commande->id) }}" method="POST" id="commandeForm">
                @csrf
                @method('PUT')

                <!-- Sélection de la table -->
                <div class="mb-6">
                    <label for="table_id" class="block text-sm font-medium text-gray-700 mb-2">Table *</label>
                    <select name="table_id" id="table_id" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            {{ !$commande->peut_etre_modifiee ? 'disabled' : '' }}>
                        <option value="">Sélectionnez une table</option>
                        @foreach($tables as $table)
                            <option value="{{ $table->id }}" 
                                {{ $commande->table_id == $table->id ? 'selected' : '' }}>
                                Table {{ $table->numero }} - {{ $table->nom }} 
                                ({{ $table->user->prenom ?? 'Non affectée' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Produits -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Produits *</label>
                    <div id="produits-container" class="space-y-4">
                        @foreach($commande->produits as $index => $produitCommande)
                        <div class="produit-item flex gap-4 items-start p-4 border border-gray-200 rounded-lg">
                            <!-- Sélection du produit -->
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Produit</label>
                                <select name="produits[{{ $index }}][produit_id]" required
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 produit-select"
                                        {{ !$commande->peut_etre_modifiee ? 'disabled' : '' }}>
                                    <option value="">Sélectionnez un produit</option>
                                    @foreach($produits as $produit)
                                        <option value="{{ $produit->id }}" 
                                            data-prix="{{ $produit->prix }}"
                                            data-stock="{{ $produit->quantite }}"
                                            {{ $produitCommande->produit_id == $produit->id ? 'selected' : '' }}>
                                            {{ $produit->designation }} - {{ number_format($produit->prix, 0, ',', ' ') }} FCFA
                                            (Stock: {{ $produit->quantite }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Quantité -->
                            <div class="w-32">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Quantité</label>
                                <input type="number" 
                                       name="produits[{{ $index }}][quantite]" 
                                       value="{{ $produitCommande->quantite }}"
                                       min="1" 
                                       required
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 quantite-input"
                                       {{ !$commande->peut_etre_modifiee ? 'disabled' : '' }}>
                                <div class="text-xs text-gray-500 mt-1 stock-info"></div>
                            </div>

                            <!-- Prix total -->
                            <div class="w-32">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Total</label>
                                <div class="p-2 bg-gray-50 rounded text-sm font-semibold text-gray-700 prix-total">
                                    {{ number_format($produitCommande->prix_total, 0, ',', ' ') }} F
                                </div>
                            </div>

                            <!-- Bouton supprimer -->
                            @if($commande->peut_etre_modifiee)
                            <div class="pt-6">
                                <button type="button" class="supprimer-produit text-red-600 hover:text-red-800">
                                    🗑️
                                </button>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    @if($commande->peut_etre_modifiee)
                    <button type="button" id="ajouter-produit" class="mt-4 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-semibold">
                        ➕ Ajouter un produit
                    </button>
                    @endif
                </div>

                <!-- Notes et description -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes internes</label>
                        <textarea name="notes" id="notes" rows="3"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                {{ !$commande->peut_etre_modifiee ? 'disabled' : '' }}>{{ $commande->notes }}</textarea>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" id="description" rows="3"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                {{ !$commande->peut_etre_modifiee ? 'disabled' : '' }}>{{ $commande->description }}</textarea>
                    </div>
                </div>

                <!-- Total -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold text-gray-800">Total de la commande:</span>
                        <span id="montant-total" class="text-2xl font-bold text-green-600">
                            {{ number_format($commande->montant, 0, ',', ' ') }} FCFA
                        </span>
                    </div>
                </div>

                <!-- Boutons -->
                <div class="flex gap-4">
                    @if($commande->peut_etre_modifiee)
                    <button type="submit" class="px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 font-semibold">
                        💾 Enregistrer les modifications
                    </button>
                    @endif
                    
                    <a href="{{ route('commandes.index') }}" class="px-6 py-3 bg-gray-600 text-white rounded-md hover:bg-gray-700 font-semibold">
                        ↩️ Retour
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let produitIndex = {{ count($commande->produits) }};
    const peutModifier = {{ $commande->peut_etre_modifiee ? 'true' : 'false' }};

    // Fonction pour mettre à jour les informations de stock et prix
    function mettreAJourProduit(container) {
        const select = container.querySelector('.produit-select');
        const quantiteInput = container.querySelector('.quantite-input');
        const prixTotal = container.querySelector('.prix-total');
        const stockInfo = container.querySelector('.stock-info');
        
        if (select && quantiteInput) {
            const selectedOption = select.options[select.selectedIndex];
            const prix = parseFloat(selectedOption?.dataset.prix) || 0;
            const stock = parseInt(selectedOption?.dataset.stock) || 0;
            const quantite = parseInt(quantiteInput.value) || 1;
            
            // Mettre à jour le prix total
            const total = prix * quantite;
            if (prixTotal) {
                prixTotal.textContent = new Intl.NumberFormat('fr-FR').format(total) + ' F';
            }
            
            // Mettre à jour les informations de stock
            if (stockInfo) {
                if (selectedOption.value) {
                    stockInfo.textContent = `Stock: ${stock}`;
                    if (quantite > stock) {
                        stockInfo.className = 'text-xs text-red-600 mt-1 stock-info';
                        stockInfo.textContent += ' - Stock insuffisant!';
                    } else {
                        stockInfo.className = 'text-xs text-green-600 mt-1 stock-info';
                    }
                } else {
                    stockInfo.textContent = '';
                }
            }
            
            mettreAJourTotalGeneral();
        }
    }

    // Mettre à jour le total général
    function mettreAJourTotalGeneral() {
        let totalGeneral = 0;
        document.querySelectorAll('.produit-item').forEach(container => {
            const prixTotal = container.querySelector('.prix-total');
            if (prixTotal) {
                const totalText = prixTotal.textContent.replace(/[^\d,]/g, '').replace(',', '');
                totalGeneral += parseFloat(totalText) || 0;
            }
        });
        
        const montantTotal = document.getElementById('montant-total');
        if (montantTotal) {
            montantTotal.textContent = new Intl.NumberFormat('fr-FR').format(totalGeneral) + ' FCFA';
        }
    }

    // Ajouter un nouveau produit
    document.getElementById('ajouter-produit')?.addEventListener('click', function() {
        if (!peutModifier) return;
        
        const template = `
            <div class="produit-item flex gap-4 items-start p-4 border border-gray-200 rounded-lg">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Produit</label>
                    <select name="produits[${produitIndex}][produit_id]" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 produit-select">
                        <option value="">Sélectionnez un produit</option>
                        @foreach($produits as $produit)
                            <option value="{{ $produit->id }}" 
                                    data-prix="{{ $produit->prix }}"
                                    data-stock="{{ $produit->quantite }}">
                                {{ $produit->designation }} - {{ number_format($produit->prix, 0, ',', ' ') }} FCFA
                                (Stock: {{ $produit->quantite }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-32">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantité</label>
                    <input type="number" 
                           name="produits[${produitIndex}][quantite]" 
                           value="1" 
                           min="1" 
                           required
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 quantite-input">
                    <div class="text-xs text-gray-500 mt-1 stock-info"></div>
                </div>
                <div class="w-32">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Total</label>
                    <div class="p-2 bg-gray-50 rounded text-sm font-semibold text-gray-700 prix-total">
                        0 F
                    </div>
                </div>
                <div class="pt-6">
                    <button type="button" class="supprimer-produit text-red-600 hover:text-red-800">
                        🗑️
                    </button>
                </div>
            </div>
        `;
        
        document.getElementById('produits-container').insertAdjacentHTML('beforeend', template);
        produitIndex++;
        
        // Ajouter les événements au nouveau produit
        const nouveauProduit = document.querySelector('#produits-container .produit-item:last-child');
        ajouterEvenementsProduit(nouveauProduit);
    });

    // Supprimer un produit
    function ajouterEvenementsProduit(container) {
        const select = container.querySelector('.produit-select');
        const quantiteInput = container.querySelector('.quantite-input');
        const btnSupprimer = container.querySelector('.supprimer-produit');
        
        // Événement pour la sélection du produit
        select?.addEventListener('change', function() {
            mettreAJourProduit(container);
        });
        
        // Événement pour la modification de la quantité - CORRECTION ICI
        quantiteInput?.addEventListener('input', function(e) {
            // Permettre l'effacement complet
            if (e.target.value === '') {
                return;
            }
            
            // Forcer une valeur minimale de 1
            if (parseInt(e.target.value) < 1) {
                e.target.value = 1;
            }
            
            mettreAJourProduit(container);
        });
        
        // Événement pour le bouton supprimer
        btnSupprimer?.addEventListener('click', function() {
            if (document.querySelectorAll('.produit-item').length > 1) {
                container.remove();
                mettreAJourTotalGeneral();
            } else {
                alert('Une commande doit avoir au moins un produit.');
            }
        });
        
        // Initialiser les informations du produit
        mettreAJourProduit(container);
    }

    // Initialiser tous les produits existants
    document.querySelectorAll('.produit-item').forEach(ajouterEvenementsProduit);

    // Validation du formulaire
    document.getElementById('commandeForm')?.addEventListener('submit', function(e) {
        if (!peutModifier) {
            e.preventDefault();
            alert('Cette commande ne peut plus être modifiée (délai de 24h dépassé).');
            return;
        }
        
        const produits = document.querySelectorAll('.produit-item');
        let erreur = false;
        
        produits.forEach(container => {
            const select = container.querySelector('.produit-select');
            const quantiteInput = container.querySelector('.quantite-input');
            const stockInfo = container.querySelector('.stock-info');
            
            if (!select.value) {
                erreur = true;
                select.style.borderColor = 'red';
            } else {
                select.style.borderColor = '';
            }
            
            if (stockInfo?.classList.contains('text-red-600')) {
                erreur = true;
                alert('Certains produits ont un stock insuffisant.');
            }
        });
        
        if (erreur) {
            e.preventDefault();
            alert('Veuillez corriger les erreurs dans le formulaire.');
        }
    });
});
</script>
@endsection