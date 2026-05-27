@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="max-w-4xl mx-auto px-3 sm:px-6">
        <!-- Debug temporaire -->
        @php
            \Log::info('Vente object in show view:', [
                'vente_id' => $vente->id ?? 'NULL',
                'vente_exists' => $vente->exists ?? 'NULL',
                'vente_user_id' => $vente->user_id ?? 'NULL'
            ]);
        @endphp

        <!-- En-tête mobile -->
        <div class="sm:hidden mb-4">
            <div class="flex justify-between items-start mb-3">
                <h1 class="text-xl font-bold text-[#0b5f37]">Détails Vente</h1>
                <span class="text-lg font-bold text-[#0b5f37]">
                    {{ number_format($vente->montant_total, 0, ',', ' ') }} F
                </span>
            </div>
            <div class="bg-white rounded-lg p-3 shadow">
                <p class="text-sm font-medium">#{{ $vente->numero_vente }}</p>
                <p class="text-xs text-gray-600">{{ ($vente->created_at ?? now())->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <!-- En-tête desktop -->
        <div class="hidden sm:flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-[#0b5f37]">Détails de la Vente</h1>
            <div class="flex space-x-2">
                <!-- Protection contre les IDs null -->
                @if($vente->id)
                <a href="{{ route('ventes.receipt', $vente->id) }}" target="_blank" 
                   class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
                    🖨️ Imprimer
                </a>
                @if($vente->peutEtreModifiee() && $vente->user_id === Auth::id())
                <a href="{{ route('ventes.edit', $vente->id) }}" 
                   class="bg-[#ee8f13] text-white px-4 py-2 rounded hover:bg-[#d67f11] text-sm">
                   ✏️ Modifier
                </a>
                @endif
                @else
                <button class="bg-gray-400 text-white px-4 py-2 rounded text-sm cursor-not-allowed" disabled>
                    🖨️ Imprimer
                </button>
                @endif
                <a href="{{ route('ventes.index') }}" 
                   class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">
                    ← Retour
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <!-- En-tête de la carte (desktop) -->
            <div class="hidden sm:block px-6 py-4 bg-gray-50 border-b">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">Vente #{{ $vente->numero_vente }}</h2>
                        <p class="text-gray-600">Date: {{ ($vente->created_at ?? now())->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-[#0b5f37]">
                            {{ number_format($vente->montant_total, 0, ',', ' ') }} FCFA
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ $vente->lignesVente->count() }} produit(s)
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-6">
                <!-- Actions mobiles -->
                <div class="sm:hidden mb-4 pb-4 border-b border-gray-200">
                    <div class="flex space-x-2">
                        <!-- Protection contre les IDs null -->
                        @if($vente->id)
                        <a href="{{ route('ventes.receipt', $vente->id) }}" target="_blank" 
                           class="flex-1 bg-blue-600 text-white px-3 py-2 rounded text-sm hover:bg-blue-700 text-center">
                            🖨️ Reçu
                        </a>
                        @if($vente->peutEtreModifiee() && $vente->user_id === Auth::id())
                        <a href="{{ route('ventes.edit', $vente->id) }}" 
                           class="flex-1 bg-[#ee8f13] text-white px-3 py-2 rounded text-sm hover:bg-[#d67f11] text-center">
                           ✏️ Modifier
                        </a>
                        @endif
                        @else
                        <button class="flex-1 bg-gray-400 text-white px-3 py-2 rounded text-sm cursor-not-allowed" disabled>
                            🖨️ Reçu
                        </button>
                        @endif
                        <a href="{{ route('ventes.index') }}" 
                           class="flex-1 bg-gray-500 text-white px-3 py-2 rounded text-sm hover:bg-gray-600 text-center">
                            ← Retour
                        </a>
                    </div>
                </div>

                <!-- Informations de base -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-6">
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <h3 class="font-semibold text-gray-700 mb-2 text-sm">Informations du vendeur</h3>
                        <p class="text-gray-600 text-sm">{{ $vente->user->prenom }} {{ $vente->user->nom }}</p>
                        <p class="text-gray-500 text-xs">{{ $vente->user->fonction }}</p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <h3 class="font-semibold text-gray-700 mb-2 text-sm">Informations de caisse</h3>
                        <p class="text-gray-600 text-sm">
                            @if($vente->caisse)
                                Caisse #{{ $vente->caisse->id }} 
                                <span class="text-xs px-2 py-1 rounded ml-1 
                                    {{ $vente->caisse->statut === 'ouverte' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $vente->caisse->statut }}
                                </span>
                            @else
                                Caisse non spécifiée
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Liste des produits -->
                <h3 class="font-semibold text-gray-700 mb-3 text-sm sm:text-base">Produits vendus</h3>
                
                <!-- Version mobile des produits -->
                <div class="sm:hidden space-y-3">
                    @foreach($vente->lignesVente as $ligne)
                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900 text-sm">{{ $ligne->product->designation }}</h4>
                                <p class="text-gray-500 text-xs">{{ $ligne->product->categorie->nom ?? 'Sans catégorie' }}</p>
                            </div>
                            <span class="font-bold text-[#0b5f37] text-sm">
                                {{ number_format($ligne->montant_total, 0, ',', ' ') }} F
                            </span>
                        </div>
                        <div class="flex justify-between text-xs text-gray-600">
                            <span>Prix unitaire: {{ number_format($ligne->prix_unitaire, 0, ',', ' ') }} F</span>
                            <span>Quantité: {{ $ligne->quantite }}</span>
                        </div>
                    </div>
                    @endforeach
                    
                    <!-- Total mobile -->
                    <div class="bg-[#0b5f37] text-white rounded-lg p-3 mt-3">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold">TOTAL</span>
                            <span class="text-lg font-bold">{{ number_format($vente->montant_total, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>
                </div>

                <!-- Version desktop des produits -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prix Unitaire</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($vente->lignesVente as $ligne)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $ligne->product->designation }}</div>
                                    <div class="text-sm text-gray-500">{{ $ligne->product->categorie->nom ?? 'Sans catégorie' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ number_format($ligne->prix_unitaire, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ $ligne->quantite }}
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-[#0b5f37]">
                                    {{ number_format($ligne->montant_total, 0, ',', ' ') }} FCFA
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-sm font-semibold text-right">Total:</td>
                                <td class="px-4 py-3 text-sm font-bold text-[#0b5f37]">
                                    {{ number_format($vente->montant_total, 0, ',', ' ') }} FCFA
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Notes -->
                @if($vente->notes)
                <div class="mt-4 p-3 bg-blue-50 rounded border border-blue-200">
                    <h4 class="font-semibold text-blue-800 mb-1 text-sm">Notes:</h4>
                    <p class="text-blue-700 text-sm">{{ $vente->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Actions rapides mobiles -->
        <div class="sm:hidden fixed bottom-4 left-4 right-4 bg-white rounded-lg shadow-lg p-3 border">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-600">Total</p>
                    <p class="font-bold text-[#0b5f37]">{{ number_format($vente->montant_total, 0, ',', ' ') }} F</p>
                </div>
                <div class="flex space-x-2">
                    <!-- Protection contre les IDs null -->
                    @if($vente->id)
                    <a href="{{ route('ventes.receipt', $vente->id) }}" target="_blank" 
                       class="bg-blue-600 text-white px-3 py-2 rounded text-sm">
                        🖨️
                    </a>
                    @else
                    <button class="bg-gray-400 text-white px-3 py-2 rounded text-sm cursor-not-allowed" disabled>
                        🖨️
                    </button>
                    @endif
                    <a href="{{ route('ventes.index') }}" 
                       class="bg-gray-500 text-white px-3 py-2 rounded text-sm">
                        ←
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Ajustements pour le fixed bottom sur mobile */
@media (max-width: 640px) {
    body {
        padding-bottom: 80px;
    }
}

/* Animation pour les cartes produits */
.bg-gray-50 {
    transition: all 0.2s ease;
}

.bg-gray-50:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
</style>

<script>
// Gestion du scroll pour la barre fixe mobile
window.addEventListener('scroll', function() {
    const fixedElement = document.querySelector('.fixed.bottom-4');
    if (fixedElement) {
        if (window.scrollY > 100) {
            fixedElement.classList.add('shadow-xl');
        } else {
            fixedElement.classList.remove('shadow-xl');
        }
    }
});
</script>
@endsection