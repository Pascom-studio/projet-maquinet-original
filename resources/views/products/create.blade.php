@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="max-w-3xl mx-auto px-3 sm:px-6">
        <!-- En-tête -->
        <div class="mb-4">
            <h1 class="text-xl sm:text-2xl font-bold text-[#0b5f37] mb-2">Nouveau Produit</h1>
            <div class="bg-blue-50 border border-blue-200 rounded p-3 text-sm">
                <p class="text-blue-700">📦 Remplissez les informations pour créer un nouveau produit</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <form action="{{ route('products.store') }}" method="POST">
                @csrf
                
                <!-- Désignation et Catégorie -->
                <div class="grid grid-cols-1 gap-4 mb-4">
                    <div>
                        <label for="designation" class="block text-sm font-medium text-gray-700 mb-1">Désignation *</label>
                        <input type="text" name="designation" id="designation" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37]"
                            value="{{ old('designation') }}"
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
                                <option value="{{ $categorie->id }}" {{ old('categorie_id') == $categorie->id ? 'selected' : '' }}>
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
                            value="{{ old('quantite', 0) }}"
                            placeholder="0">
                        @error('quantite')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="prix" class="block text-sm font-medium text-gray-700 mb-1">Prix (FCFA) *</label>
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

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37]"
                        placeholder="Description du produit (optionnel)">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Boutons d'action -->
                <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('products.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded text-sm hover:bg-gray-600 text-center">
                        Annuler
                    </a>
                    <button type="submit" class="bg-[#0b5f37] text-white px-4 py-2 rounded text-sm hover:bg-[#0a4d2c] font-semibold text-center">
                        Créer le Produit
                    </button>
                </div>
            </form>
        </div>

        <!-- Aide contextuelle -->
        <div class="mt-4 bg-green-50 rounded p-4 border border-green-200">
            <h4 class="font-semibold text-green-800 mb-2 text-sm flex items-center">
                💡 Informations importantes
            </h4>
            <ul class="text-xs text-green-700 space-y-1">
                <li>• Les champs marqués d'un * sont obligatoires</li>
                <li>• La quantité doit être un nombre positif ou zéro</li>
                <li>• Le prix doit être en Francs CFA</li>
                <li>• La description est optionnelle mais recommandée</li>
            </ul>
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

// Validation en temps réel
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const inputs = form.querySelectorAll('input[required], select[required]');
    
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                this.classList.remove('border-red-300');
                this.classList.add('border-green-300');
            } else {
                this.classList.remove('border-green-300');
                this.classList.add('border-red-300');
            }
        });
    });
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

/* Indicateur visuel pour les champs valides */
.border-green-300 {
    border-color: #86efac;
}
</style>
@endsection