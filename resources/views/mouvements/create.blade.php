@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="max-w-3xl mx-auto px-3 sm:px-6">
        <!-- En-tête -->
        <div class="mb-4">
            <h1 class="text-xl sm:text-2xl font-bold text-[#0b5f37] mb-2">Nouveau Mouvement de Stock</h1>
            <div class="bg-blue-50 border border-blue-200 rounded p-3 text-sm">
                <p class="text-blue-700">📦 Enregistrez une entrée ou sortie de stock</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <form action="{{ route('mouvements.store') }}" method="POST">
                @csrf
                
                <!-- Produit et Type -->
                <div class="grid grid-cols-1 gap-4 mb-4">
                    <div>
                        <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">Produit *</label>
                        <select name="product_id" id="product_id" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37]">
                            <option value="">Sélectionnez un produit</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" 
                                    data-stock="{{ $product->quantite }}"
                                    {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->designation }} (Stock: {{ $product->quantite }})
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type de mouvement *</label>
                        <select name="type" id="type" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37]">
                            <option value="entrée" {{ old('type') == 'entrée' ? 'selected' : '' }}>🟢 Entrée de stock</option>
                            <option value="sortie" {{ old('type') == 'sortie' ? 'selected' : '' }}>🔴 Sortie de stock</option>
                        </select>
                        @error('type')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Quantité et Prix -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="quantite" class="block text-sm font-medium text-gray-700 mb-1">Quantité *</label>
                        <input type="number" name="quantite" id="quantite" required min="1"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37]"
                            value="{{ old('quantite', 1) }}"
                            placeholder="1">
                        @error('quantite')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <div id="stock-info" class="mt-2 text-xs text-gray-600"></div>
                    </div>

                    <div>
                        <label for="prix" class="block text-sm font-medium text-gray-700 mb-1">Prix unitaire (FCFA) *</label>
                        <div class="relative">
                            <input type="number" name="prix" id="prix" required min="0" step="0.01"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37] pr-10"
                                value="{{ old('prix') }}"
                                placeholder="0.00">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 text-sm">F</span>
                            </div>
                        </div>
                        @error('prix')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Information dynamique -->
                <div class="mb-6 p-3 bg-yellow-50 rounded border border-yellow-200">
                    <h4 class="font-semibold text-yellow-800 text-sm mb-1">Information</h4>
                    <p class="text-xs text-yellow-700" id="mouvement-info">
                        Sélectionnez un produit et un type de mouvement pour voir les détails.
                    </p>
                </div>

                <!-- Résumé du mouvement -->
                <div class="mb-6 p-3 bg-gray-50 rounded border border-gray-200 hidden" id="resume-mouvement">
                    <h4 class="font-semibold text-gray-800 text-sm mb-2">Résumé du mouvement</h4>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-gray-600">Stock actuel:</span>
                            <span id="resume-stock-actuel" class="font-medium text-gray-900 ml-1">-</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Stock final:</span>
                            <span id="resume-stock-final" class="font-medium text-gray-900 ml-1">-</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-gray-600">Valeur totale:</span>
                            <span id="resume-valeur-totale" class="font-medium text-[#0b5f37] ml-1">- FCFA</span>
                        </div>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('mouvements.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded text-sm hover:bg-gray-600 text-center">
                        Annuler
                    </a>
                    <button type="submit" class="bg-[#0b5f37] text-white px-4 py-2 rounded text-sm hover:bg-[#0a4d2c] font-semibold text-center">
                        Enregistrer le Mouvement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_id');
    const typeSelect = document.getElementById('type');
    const quantiteInput = document.getElementById('quantite');
    const prixInput = document.getElementById('prix');
    const stockInfo = document.getElementById('stock-info');
    const mouvementInfo = document.getElementById('mouvement-info');
    const resumeMouvement = document.getElementById('resume-mouvement');
    const resumeStockActuel = document.getElementById('resume-stock-actuel');
    const resumeStockFinal = document.getElementById('resume-stock-final');
    const resumeValeurTotale = document.getElementById('resume-valeur-totale');

    function updateInfos() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const stock = selectedOption.dataset.stock;
        const type = typeSelect.value;
        const quantite = parseInt(quantiteInput.value) || 0;
        const prix = parseFloat(prixInput.value) || 0;

        if (stock && productSelect.value) {
            stockInfo.textContent = `Stock actuel: ${stock} unités`;
            resumeMouvement.classList.remove('hidden');
            resumeStockActuel.textContent = `${stock} unités`;

            let nouveauStock = parseInt(stock);
            if (type === 'entrée') {
                nouveauStock += quantite;
                mouvementInfo.innerHTML = `<span class="text-green-600">🟢 Ajout de ${quantite} unités au stock</span>`;
            } else {
                nouveauStock -= quantite;
                if (quantite > parseInt(stock)) {
                    mouvementInfo.innerHTML = `<span class="text-red-600 font-semibold">🔴 Stock insuffisant! Disponible: ${stock} unités</span>`;
                } else {
                    mouvementInfo.innerHTML = `<span class="text-red-600">🔴 Retrait de ${quantite} unités du stock</span>`;
                }
            }

            resumeStockFinal.textContent = `${nouveauStock} unités`;
            resumeValeurTotale.textContent = `${(quantite * prix).toLocaleString('fr-FR')} FCFA`;
            
            // Style du stock final
            if (nouveauStock < 0) {
                resumeStockFinal.className = 'font-medium text-red-600 ml-1';
            } else if (nouveauStock < 10) {
                resumeStockFinal.className = 'font-medium text-yellow-600 ml-1';
            } else {
                resumeStockFinal.className = 'font-medium text-green-600 ml-1';
            }
        } else {
            stockInfo.textContent = '';
            mouvementInfo.textContent = 'Sélectionnez un produit et un type de mouvement pour voir les détails.';
            resumeMouvement.classList.add('hidden');
        }
    }

    productSelect.addEventListener('change', updateInfos);
    typeSelect.addEventListener('change', updateInfos);
    quantiteInput.addEventListener('input', updateInfos);
    prixInput.addEventListener('input', updateInfos);
    
    // Formatage automatique du prix
    prixInput.addEventListener('blur', function(e) {
        const value = parseFloat(e.target.value);
        if (!isNaN(value) && value >= 0) {
            e.target.value = value.toFixed(2);
        }
    });

    updateInfos();
});
</script>

<style>
/* Améliorations pour mobile */
@media (max-width: 640px) {
    .bg-white {
        margin: 0 -0.75rem;
        border-radius: 0;
    }
}

/* Animation pour les champs */
input:focus, select:focus {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}
</style>
@endsection