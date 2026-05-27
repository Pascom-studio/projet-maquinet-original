@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="max-w-3xl mx-auto px-3 sm:px-6">
        <!-- En-tête -->
        <div class="mb-4">
            <h1 class="text-xl sm:text-2xl font-bold text-[#0b5f37] mb-2">Nouvelle Catégorie</h1>
            <div class="bg-blue-50 border border-blue-200 rounded p-3 text-sm">
                <p class="text-blue-700">🏷️ Créez une nouvelle catégorie pour organiser vos produits</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                
                <!-- Nom de la catégorie -->
                <div class="mb-4">
                    <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom de la catégorie *</label>
                    <input type="text" name="nom" id="nom" required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37]"
                        value="{{ old('nom') }}"
                        placeholder="Ex: Boissons, Électronique, Vêtements...">
                    @error('nom')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="description" rows="4"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37]"
                        placeholder="Décrivez cette catégorie (optionnel)">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Boutons d'action -->
                <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('categories.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded text-sm hover:bg-gray-600 text-center">
                        Annuler
                    </a>
                    <button type="submit" class="bg-[#0b5f37] text-white px-4 py-2 rounded text-sm hover:bg-[#0a4d2c] font-semibold text-center">
                        Créer la Catégorie
                    </button>
                </div>
            </form>
        </div>

        <!-- Conseils -->
        <div class="mt-4 bg-green-50 rounded p-4 border border-green-200">
            <h4 class="font-semibold text-green-800 mb-2 text-sm flex items-center">
                💡 Conseils pour une bonne catégorisation
            </h4>
            <ul class="text-xs text-green-700 space-y-1">
                <li>• Choisissez un nom clair et descriptif</li>
                <li>• Utilisez des catégories spécifiques plutôt que générales</li>
                <li>• Pensez à la façon dont vos clients recherchent les produits</li>
                <li>• Une bonne description aide à mieux organiser votre inventaire</li>
            </ul>
        </div>
    </div>
</div>

<script>
// Validation en temps réel
document.addEventListener('DOMContentLoaded', function() {
    const nomInput = document.getElementById('nom');
    const form = document.querySelector('form');
    
    // Validation du nom
    nomInput.addEventListener('input', function() {
        if (this.value.trim().length < 2) {
            this.classList.add('border-red-300');
            this.classList.remove('border-green-300');
        } else {
            this.classList.remove('border-red-300');
            this.classList.add('border-green-300');
        }
    });
    
    // Empêcher la soumission si le nom est trop court
    form.addEventListener('submit', function(e) {
        if (nomInput.value.trim().length < 2) {
            e.preventDefault();
            alert('Le nom de la catégorie doit contenir au moins 2 caractères.');
            nomInput.focus();
        }
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
input:focus, textarea:focus {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

/* Indicateur visuel pour les champs valides */
.border-green-300 {
    border-color: #86efac;
}
</style>
@endsection