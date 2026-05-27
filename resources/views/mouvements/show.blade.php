@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="max-w-3xl mx-auto px-3 sm:px-6">
        <!-- En-tête mobile -->
        <div class="sm:hidden mb-4">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Détails Mouvement</h1>
                    <p class="text-sm text-gray-600 mt-1">#{{ $mouvement->id }}</p>
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
                        <h3 class="text-sm font-semibold text-gray-900">{{ $mouvement->product->designation }}</h3>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                            {{ $mouvement->type === 'entrée' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $mouvement->type === 'entrée' ? '🟢 Entrée' : '🔴 Sortie' }}
                        </span>
                    </div>
                    <span class="text-sm font-bold text-[#0b5f37]">
                        {{ number_format($mouvement->prix * $mouvement->quantite, 0, ',', ' ') }} F
                    </span>
                </div>
            </div>
        </div>

        <!-- En-tête desktop -->
        <div class="hidden sm:flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-[#0b5f37]">Détails du Mouvement</h1>
                <p class="text-gray-600 mt-1">#{{ $mouvement->id }} - {{ $mouvement->product->designation }}</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('mouvements.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">
                    ← Retour
                </a>
                <a href="{{ route('mouvements.edit', $mouvement) }}" class="bg-[#ee8f13] text-white px-4 py-2 rounded hover:bg-[#d67f11] text-sm">
                    ✏️ Modifier
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <!-- Informations principales -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                    <h3 class="font-semibold text-gray-700 text-sm mb-2">Produit</h3>
                    <div class="flex items-center">
                        <div class="bg-[#0b5f37] text-white p-2 rounded-lg mr-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $mouvement->product->designation }}</p>
                            <p class="text-xs text-gray-500">{{ $mouvement->product->categorie->nom }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                    <h3 class="font-semibold text-gray-700 text-sm mb-2">Type de mouvement</h3>
                    <div class="flex items-center">
                        <div class="{{ $mouvement->type === 'entrée' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} p-2 rounded-lg mr-3">
                            @if($mouvement->type === 'entrée')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 capitalize">{{ $mouvement->type }}</p>
                            <p class="text-xs text-gray-500">
                                @if($mouvement->type === 'entrée')
                                    Ajout au stock
                                @else
                                    Retrait du stock
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Détails quantitatifs -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-blue-50 p-3 rounded-lg border border-blue-200">
                    <h3 class="font-semibold text-blue-700 text-sm mb-1">Quantité</h3>
                    <p class="text-lg font-bold text-blue-900">{{ $mouvement->quantite }} unités</p>
                </div>

                <div class="bg-green-50 p-3 rounded-lg border border-green-200">
                    <h3 class="font-semibold text-green-700 text-sm mb-1">Prix unitaire</h3>
                    <p class="text-lg font-bold text-green-900">{{ number_format($mouvement->prix, 0, ',', ' ') }} FCFA</p>
                </div>

                <div class="bg-purple-50 p-3 rounded-lg border border-purple-200">
                    <h3 class="font-semibold text-purple-700 text-sm mb-1">Valeur totale</h3>
                    <p class="text-lg font-bold text-purple-900">{{ number_format($mouvement->prix * $mouvement->quantite, 0, ',', ' ') }} FCFA</p>
                </div>
            </div>

            <!-- Informations complémentaires -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                    <h3 class="font-semibold text-gray-700 text-sm mb-2">Utilisateur</h3>
                    <div class="flex items-center">
                        <div class="bg-[#0b5f37] text-white p-2 rounded-full mr-3 text-xs font-bold">
                            {{ strtoupper(substr($mouvement->user->prenom, 0, 1)) }}{{ strtoupper(substr($mouvement->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $mouvement->user->prenom }} {{ $mouvement->user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $mouvement->user->fonction }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                    <h3 class="font-semibold text-gray-700 text-sm mb-2">Date et heure</h3>
                    <div class="flex items-center">
                        <div class="bg-gray-600 text-white p-2 rounded-lg mr-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $mouvement->created_at->format('d/m/Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $mouvement->created_at->format('H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions mobiles -->
            <div class="sm:hidden flex justify-center space-x-3 pt-4 border-t border-gray-200">
                <a href="{{ route('mouvements.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded text-sm hover:bg-gray-600 text-center flex-1">
                    ← Retour
                </a>
                <a href="{{ route('mouvements.edit', $mouvement) }}" class="bg-[#ee8f13] text-white px-4 py-2 rounded text-sm hover:bg-[#d67f11] text-center flex-1">
                    ✏️ Modifier
                </a>
            </div>
        </div>

        <!-- Informations sur le produit -->
        <div class="mt-4 bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-gray-700 mb-3 text-sm">État actuel du stock</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Stock actuel:</span>
                    <span class="font-medium text-gray-900 ml-2">{{ $mouvement->product->quantite }} unités</span>
                </div>
                <div>
                    <span class="text-gray-500">Statut:</span>
                    @php
                        $stockLevel = 'normal';
                        if ($mouvement->product->quantite == 0) {
                            $stockLevel = 'rupture';
                        } elseif ($mouvement->product->quantite < 10) {
                            $stockLevel = 'faible';
                        }
                    @endphp
                    <span class="font-medium ml-2 
                        @if($stockLevel === 'rupture') text-red-600
                        @elseif($stockLevel === 'faible') text-yellow-600
                        @else text-green-600 @endif">
                        @if($stockLevel === 'rupture') 🔴 Rupture
                        @elseif($stockLevel === 'faible') 🟡 Faible
                        @else 🟢 Normal
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Animation pour les cartes */
.bg-white {
    transition: all 0.2s ease;
}

.bg-white:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Amélioration de l'accessibilité */
@media (max-width: 640px) {
    .bg-white {
        margin: 0 -0.5rem;
    }
}
</style>
@endsection