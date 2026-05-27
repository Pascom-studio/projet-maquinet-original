@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="max-w-3xl mx-auto px-3 sm:px-6">
        <!-- En-tête mobile -->
        <div class="sm:hidden mb-4">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Modifier Catégorie</h1>
                    <p class="text-sm text-gray-600 mt-1">{{ $category->nom }}</p>
                </div>
                <a href="{{ route('categories.index') }}" class="bg-gray-600 text-white p-2 rounded-lg hover:bg-gray-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
            </div>
            <div class="bg-white rounded-lg p-3 shadow border">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-xs text-gray-500">Créée le</p>
                        <p class="text-sm font-medium text-gray-900">{{ $category->created_at->format('d/m/Y') }}</p>
                    </div>
                    @if($category->products_count > 0)
                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                        {{ $category->products_count }} produit(s)
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- En-tête desktop -->
        <div class="hidden sm:block mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-[#0b5f37]">Modifier la Catégorie</h1>
                    <p class="text-gray-600 mt-1">{{ $category->nom }}</p>
                </div>
                <a href="{{ route('categories.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">
                    ← Retour à la liste
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <form action="{{ route('categories.update', $category) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Nom de la catégorie -->
                <div class="mb-4">
                    <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom de la catégorie *</label>
                    <input type="text" name="nom" id="nom" required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37]"
                        value="{{ old('nom', $category->nom )}}"
                        placeholder="Entrez le nom de la catégorie">
                    @error('nom')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description (optionnel)</label>
                    <textarea name="description" id="description" rows="4"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37]"
                        placeholder="Entrez une description pour cette catégorie">{{ old('description', $category->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Informations sur la catégorie -->
                <div class="mb-6 p-3 bg-gray-50 rounded border border-gray-200">
                    <h3 class="font-semibold text-gray-700 text-sm mb-2 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Informations de la catégorie
                    </h3>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <span class="text-gray-500">ID:</span>
                            <span class="font-medium text-gray-700 ml-1">{{ $category->id }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Créée le:</span>
                            <span class="font-medium text-gray-700 ml-1">{{ $category->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Modifiée le:</span>
                            <span class="font-medium text-gray-700 ml-1">{{ $category->updated_at->format('d/m/Y') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Produits:</span>
                            <span class="font-medium {{ $category->products_count > 0 ? 'text-blue-600' : 'text-gray-600' }} ml-1">
                                {{ $category->products_count }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="flex flex-col sm:flex-row justify-between space-y-3 sm:space-y-0">
                    <div class="flex space-x-2">
                        <a href="{{ route('categories.index') }}" 
                           class="flex-1 bg-gray-500 text-white px-4 py-2 rounded text-sm hover:bg-gray-600 text-center">
                            Annuler
                        </a>
                        @if($category->products_count === 0 && (auth()->user()->isAdmin() || auth()->user()->isGerant()))
                        <form action="{{ route('categories.destroy', $category) }}" method="POST" class="flex-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700 text-center"
                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')">
                                Supprimer
                            </button>
                        </form>
                        @endif
                    </div>
                    <button type="submit" 
                            class="flex-1 sm:flex-none bg-[#0b5f37] text-white px-4 py-2 rounded text-sm hover:bg-[#0a4d2c] font-semibold text-center">
                        Modifier la Catégorie
                    </button>
                </div>
            </form>
        </div>

        <!-- Avertissement si la catégorie contient des produits -->
        @if($category->products_count > 0)
        <div class="mt-4 bg-yellow-50 rounded p-4 border border-yellow-200">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <div>
                    <h4 class="font-semibold text-yellow-800 text-sm">Catégorie utilisée</h4>
                    <p class="text-yellow-700 text-xs mt-1">
                        Cette catégorie contient {{ $category->products_count }} produit(s). 
                        La suppression n'est pas possible tant que des produits y sont associés.
                    </p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
// Validation en temps réel
document.addEventListener('DOMContentLoaded', function() {
    const nomInput = document.getElementById('nom');
    const form = document.querySelector('form');
    
    nomInput.addEventListener('input', function() {
        if (this.value.trim().length < 2) {
            this.classList.add('border-red-300');
            this.classList.remove('border-green-300');
        } else {
            this.classList.remove('border-red-300');
            this.classList.add('border-green-300');
        }
    });
    
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
</style>
@endsection