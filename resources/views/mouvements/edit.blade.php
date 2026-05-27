@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="max-w-3xl mx-auto px-3 sm:px-6">
        <!-- En-tête mobile -->
        <div class="sm:hidden mb-4">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Modifier Mouvement</h1>
                    <p class="text-sm text-gray-600 mt-1">{{ $mouvement->product->designation }}</p>
                </div>
                <a href="{{ route('mouvements.index') }}" class="bg-gray-600 text-white p-2 rounded-lg hover:bg-gray-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
            </div>
            <div class="bg-white rounded-lg p-3 shadow border">
                <div class="flex justify-between items-center">
                    <div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                            {{ $mouvement->type === 'entrée' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($mouvement->type) }}
                        </span>
                        <p class="text-xs text-gray-500 mt-1">{{ $mouvement->quantite }} unités</p>
                    </div>
                    <span class="text-sm font-bold text-[#0b5f37]">
                        {{ number_format($mouvement->prix, 0, ',', ' ') }} F
                    </span>
                </div>
            </div>
        </div>

        <!-- En-tête desktop -->
        <div class="hidden sm:block mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-[#0b5f37]">Modifier le Mouvement de Stock</h1>
                    <p class="text-gray-600 mt-1">{{ $mouvement->product->designation }}</p>
                </div>
                <a href="{{ route('mouvements.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">
                    ← Retour à la liste
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <!-- Début du formulaire de mise à jour -->
            <form action="{{ route('mouvements.update', $mouvement->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Informations actuelles -->
                <div class="mb-4 p-3 bg-gray-50 rounded border border-gray-200">
                    <h3 class="font-semibold text-gray-700 text-sm mb-2">Informations actuelles</h3>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-gray-500">Produit:</span>
                            <span class="font-medium text-gray-700">{{ $mouvement->product->designation }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Stock actuel:</span>
                            <span class="font-medium text-gray-700">{{ $mouvement->product->quantite }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Type actuel:</span>
                            <span class="font-medium {{ $mouvement->type === 'entrée' ? 'text-green-600' : 'text-red-600' }}">
                                {{ ucfirst($mouvement->type) }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-500">Date:</span>
                            <span class="font-medium text-gray-700">{{ $mouvement->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Type et Quantité -->
                <div class="grid grid-cols-1 gap-4 mb-4">
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type de mouvement *</label>
                        <select name="type" id="type" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37]">
                            <option value="entrée" {{ old('type', $mouvement->type) == 'entrée' ? 'selected' : '' }}>🟢 Entrée de stock</option>
                            <option value="sortie" {{ old('type', $mouvement->type) == 'sortie' ? 'selected' : '' }}>🔴 Sortie de stock</option>
                        </select>
                        @error('type')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="quantite" class="block text-sm font-medium text-gray-700 mb-1">Quantité *</label>
                        <input type="number" name="quantite" id="quantite" required min="1"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37]"
                            value="{{ old('quantite', $mouvement->quantite) }}"
                            placeholder="1">
                        @error('quantite')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Prix -->
                <div class="mb-6">
                    <label for="prix" class="block text-sm font-medium text-gray-700 mb-1">Prix unitaire (FCFA) *</label>
                    <div class="relative">
                        <input type="number" name="prix" id="prix" required min="0" step="0.01"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37] pr-10"
                            value="{{ old('prix', $mouvement->prix) }}"
                            placeholder="0.00">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-sm">F</span>
                        </div>
                    </div>
                    @error('prix')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Raison (optionnel) -->
                <div class="mb-6">
                    <label for="raison" class="block text-sm font-medium text-gray-700 mb-1">Raison (optionnel)</label>
                    <input type="text" name="raison" id="raison"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37]"
                        value="{{ old('raison', $mouvement->raison) }}"
                        placeholder="Motif du mouvement...">
                    @error('raison')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Impact sur le stock -->
                <div class="mb-6 p-3 bg-blue-50 rounded border border-blue-200">
                    <h4 class="font-semibold text-blue-800 text-sm mb-1">Impact sur le stock</h4>
                    <p class="text-xs text-blue-700" id="impact-info">
                        Calcul de l'impact en cours...
                    </p>
                </div>

                <!-- Boutons d'action (Mise à jour et Annuler) -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('mouvements.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded text-sm hover:bg-gray-600 text-center">
                        Annuler
                    </a>
                    <button type="submit" class="bg-[#0b5f37] text-white px-4 py-2 rounded text-sm hover:bg-[#0a4d2c] font-semibold text-center">
                        Mettre à Jour le Mouvement
                    </button>
                </div>
            </form>
            <!-- Fin du formulaire de mise à jour -->

            <!-- Formulaire de suppression ISOLÉ et clarement IDENTIFIÉ -->
            @if(auth()->user()->isAdmin() || auth()->user()->isGerant())
            <div class="mt-8 pt-4 border-t border-gray-200">
                <h3 class="text-sm font-semibold text-red-700 mb-3">Zone Dangereuse</h3>
                <form action="{{ route('mouvements.destroy', $mouvement->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="w-full sm:w-auto bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700 text-center"
                            onclick="return confirm('Êtes-vous sûr de vouloir SUPPRIMER DÉFINITIVEMENT ce mouvement ? Cette action est irréversible.')">
                        🔴 Supprimer Définitivement le Mouvement
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const quantiteInput = document.getElementById('quantite');
    const impactInfo = document.getElementById('impact-info');
    const stockActuel = {{ $mouvement->product->quantite }};
    const ancienType = '{{ $mouvement->type }}';
    const ancienneQuantite = {{ $mouvement->quantite }};

    function updateImpact() {
        const nouveauType = typeSelect.value;
        const nouvelleQuantite = parseInt(quantiteInput.value) || 0;

        // Calcul de l'impact
        let impact = stockActuel;
        
        // Annuler l'ancien mouvement
        if (ancienType === 'entrée') {
            impact -= ancienneQuantite;
        } else {
            impact += ancienneQuantite;
        }
        
        // Appliquer le nouveau mouvement
        if (nouveauType === 'entrée') {
            impact += nouvelleQuantite;
        } else {
            impact -= nouvelleQuantite;
        }

        if (impact < 0) {
            impactInfo.innerHTML = `<span class="text-red-600 font-semibold">🔴 Stock insuffisant! Stock final: ${impact} unités</span>`;
        } else if (impact < 10) {
            impactInfo.innerHTML = `<span class="text-yellow-600 font-semibold">🟡 Stock faible! Stock final: ${impact} unités</span>`;
        } else {
            impactInfo.innerHTML = `<span class="text-green-600 font-semibold">🟢 Stock final après modification: ${impact} unités</span>`;
        }
    }

    typeSelect.addEventListener('change', updateImpact);
    quantiteInput.addEventListener('input', updateImpact);
    
    // Formatage automatique du prix
    document.getElementById('prix')?.addEventListener('blur', function(e) {
        const value = parseFloat(e.target.value);
        if (!isNaN(value) && value >= 0) {
            e.target.value = value.toFixed(2);
        }
    });

    updateImpact();
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
</style>
@endsection
