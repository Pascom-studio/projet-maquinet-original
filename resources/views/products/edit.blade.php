@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="max-w-3xl mx-auto px-3 sm:px-6">
        <!-- En-tête mobile -->
        <div class="sm:hidden mb-4">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Modifier Produit</h1>
                    <p class="text-sm text-gray-600 mt-1">{{ $product->designation }}</p>
                </div>
                <a href="{{ route('products.index') }}" class="bg-gray-600 text-white p-2 rounded-lg hover:bg-gray-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
            </div>
            <div class="bg-white rounded-lg p-3 shadow border">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Catégorie: {{ $product->categorie->nom }}</p>
                        <p class="text-xs text-gray-500">Stock: {{ $product->quantite }} unités</p>
                    </div>
                    <span class="text-sm font-bold text-[#0b5f37]">
                        {{ number_format($product->prix, 0, ',', ' ') }} F
                    </span>
                </div>
            </div>
        </div>

        <!-- En-tête desktop -->
        <div class="hidden sm:block mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-[#0b5f37]">Modifier le Produit</h1>
                    <p class="text-gray-600 mt-1">{{ $product->designation }}</p>
                </div>
                <a href="{{ route('products.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">
                    ← Retour à la liste
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <!-- FORMULAIRE DE MODIFICATION -->
            <form action="{{ route('products.update', $product) }}" method="POST" id="updateForm">
                @csrf
                @method('PUT')
                
                <!-- Désignation et Catégorie -->
                <div class="grid grid-cols-1 gap-4 mb-4">
                    <div>
                        <label for="designation" class="block text-sm font-medium text-gray-700 mb-1">Désignation *</label>
                        <input type="text" name="designation" id="designation" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37]"
                            value="{{ old('designation', $product->designation) }}"
                            placeholder="Nom du produit">
                        @error('designation')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="categorie_id" class="block text-sm font-medium text-gray-700 mb-1">Catégorie *</label>
                        <select name="categorie_id" id="categorie_id" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37]">
                            <option value="">Sélectionnez une catégorie</option>
                            @foreach($categories as $categorie)
                                <option value="{{ $categorie->id }}" {{ old('categorie_id', $product->categorie_id) == $categorie->id ? 'selected' : '' }}>
                                    {{ $categorie->nom }}
                                </option>
                            @endforeach
                        </select>
                        @error('categorie_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Quantité et Prix -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="quantite" class="block text-sm font-medium text-gray-700 mb-1">Quantité *</label>
                        <input type="number" name="quantite" id="quantite" required min="0"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37]"
                            value="{{ old('quantite', $product->quantite) }}"
                            placeholder="0">
                        @error('quantite')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        
                        <!-- Indicateur de stock -->
                        <div class="mt-2">
                            @php
                                $stockLevel = 'normal';
                                if ($product->quantite == 0) {
                                    $stockLevel = 'rupture';
                                } elseif ($product->quantite < 10) {
                                    $stockLevel = 'faible';
                                }
                            @endphp
                            <span id="stock-indicator" class="inline-flex items-center px-2 py-1 rounded text-xs font-medium 
                                @if($stockLevel === 'rupture') bg-red-100 text-red-800
                                @elseif($stockLevel === 'faible') bg-yellow-100 text-yellow-800
                                @else bg-green-100 text-green-800 @endif">
                                @if($stockLevel === 'rupture')
                                    🔴 Rupture de stock
                                @elseif($stockLevel === 'faible')
                                    🟡 Stock faible
                                @else
                                    🟢 Stock normal
                                @endif
                            </span>
                        </div>
                    </div>

                    <div>
                        <label for="prix" class="block text-sm font-medium text-gray-700 mb-1">Prix (FCFA) *</label>
                        <div class="relative">
                            <input type="number" name="prix" id="prix" required min="0" step="0.01"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37] pr-10"
                                value="{{ old('prix', $product->prix) }}"
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

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37]"
                        placeholder="Description du produit (optionnel)">{{ old('description', $product->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Informations produit -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
                    <h4 class="font-semibold text-gray-700 mb-3 text-sm flex items-center">
                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Informations du produit
                    </h4>
                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-gray-500">ID:</span>
                            <span class="font-medium text-gray-700">{{ $product->id }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Créé le:</span>
                            <span class="font-medium text-gray-700">{{ $product->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Modifié le:</span>
                            <span class="font-medium text-gray-700">{{ $product->updated_at->format('d/m/Y') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Statut:</span>
                            <span class="font-medium {{ $product->quantite > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $product->quantite > 0 ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- BOUTONS D'ACTION - CORRIGÉ -->
                <div class="flex flex-col sm:flex-row justify-between space-y-3 sm:space-y-0">
                    <!-- Colonne de gauche : Annuler -->
                    <div class="flex space-x-2">
                        <a href="{{ route('products.index') }}" class="flex-1 bg-gray-500 text-white px-4 py-2 rounded text-sm hover:bg-gray-600 text-center">
                            Annuler
                        </a>
                    </div>
                    
                    <!-- Colonne de droite : Modifier -->
                    <button type="submit" 
                            class="flex-1 sm:flex-none bg-[#0b5f37] text-white px-4 py-2 rounded text-sm hover:bg-[#0a4d2c] font-semibold text-center">
                        Modifier le Produit
                    </button>
                </div>
            </form>

            <!-- FORMULAIRE DE SUPPRESSION - SÉPARÉ -->
            @if(auth()->user()->isAdmin() || auth()->user()->isGerant())
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <h4 class="font-semibold text-red-800 mb-2 text-sm flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Zone de suppression
                    </h4>
                    <p class="text-red-700 text-xs mb-3">Cette action est irréversible. Le produit sera définitivement supprimé.</p>
                    <form action="{{ route('products.destroy', $product) }}" method="POST" id="deleteForm">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700 text-center"
                                onclick="return confirmDelete()">
                            Supprimer définitivement ce produit
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
// Formatage automatique du prix
document.getElementById('prix')?.addEventListener('blur', function(e) {
    const value = parseFloat(e.target.value);
    if (!isNaN(value) && value >= 0) {
        e.target.value = value.toFixed(2);
    }
});

// Mise à jour en temps réel de l'indicateur de stock
document.getElementById('quantite')?.addEventListener('input', function(e) {
    const quantity = parseInt(e.target.value) || 0;
    const stockIndicator = document.getElementById('stock-indicator');
    
    if (stockIndicator) {
        if (quantity === 0) {
            stockIndicator.className = 'inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800';
            stockIndicator.textContent = '🔴 Rupture de stock';
        } else if (quantity < 10) {
            stockIndicator.className = 'inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800';
            stockIndicator.textContent = '🟡 Stock faible';
        } else {
            stockIndicator.className = 'inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800';
            stockIndicator.textContent = '🟢 Stock normal';
        }
    }
});

// Confirmation de suppression
function confirmDelete() {
    const productName = "{{ $product->designation }}";
    return confirm(`Êtes-vous ABSOLUMENT sûr de vouloir supprimer le produit "${productName}" ?\n\nCette action est irréversible !`);
}

// Protection contre la double soumission
document.getElementById('updateForm')?.addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Modification en cours...';
});

document.getElementById('deleteForm')?.addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Suppression en cours...';
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
input:focus, select:focus, textarea:focus {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}
</style>
@endsection