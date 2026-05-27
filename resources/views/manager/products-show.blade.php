@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- En-tête -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-[#0b5f37]">Détail du Produit</h1>
                    <p class="text-gray-600 mt-2">Consultation des informations du produit</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('manager.products.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        ← Retour
                    </a>
                </div>
            </div>
        </div>

        <!-- Carte du produit -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Image et informations principales -->
                    <div class="lg:w-1/3">
                        <div class="bg-gray-100 rounded-lg h-64 flex items-center justify-center">
                            <span class="text-6xl text-gray-400">📦</span>
                        </div>
                        
                        <div class="mt-4 grid grid-cols-2 gap-4">
                            <div class="text-center p-3 bg-blue-50 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ $product->quantite }}</div>
                                <div class="text-sm text-blue-800">En stock</div>
                            </div>
                            <div class="text-center p-3 bg-green-50 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ number_format($product->prix, 0, ',', ' ') }}</div>
                                <div class="text-sm text-green-800">Prix (FCFA)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Détails du produit -->
                    <div class="lg:w-2/3">
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold text-gray-900">{{ $product->designation }}</h2>
                            <div class="mt-2 flex items-center space-x-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    {{ $product->categorie->nom ?? 'Non catégorisé' }}
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                    {{ $product->quantite > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $product->quantite > 0 ? 'Disponible' : 'Rupture de stock' }}
                                </span>
                            </div>
                        </div>

                        <!-- Informations détaillées -->
                        <div class="space-y-4">
                            @if($product->description)
                            <div>
                                <h3 class="text-sm font-medium text-gray-700 mb-2">Description</h3>
                                <p class="text-gray-600 bg-gray-50 p-3 rounded-lg">{{ $product->description }}</p>
                            </div>
                            @endif

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-700 mb-2">Prix de vente</h3>
                                    <p class="text-lg font-semibold text-[#0b5f37]">{{ number_format($product->prix, 0, ',', ' ') }} FCFA</p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-700 mb-2">Stock actuel</h3>
                                    <p class="text-lg font-semibold 
                                        {{ $product->quantite > 10 ? 'text-green-600' : ($product->quantite > 0 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ $product->quantite }} unités
                                    </p>
                                    @if($product->quantite <= $product->seuil_alerte)
                                        <p class="text-xs text-red-600 mt-1">⚠️ Stock faible</p>
                                    @endif
                                </div>
                            </div>

                            @if($product->seuil_alerte)
                            <div>
                                <h3 class="text-sm font-medium text-gray-700 mb-2">Seuil d'alerte</h3>
                                <p class="text-gray-600">{{ $product->seuil_alerte }} unités</p>
                            </div>
                            @endif

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-700 mb-2">Code produit</h3>
                                    <p class="text-gray-600 font-mono">{{ $product->code ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-700 mb-2">Unité</h3>
                                    <p class="text-gray-600">{{ $product->unite ?? 'Pièce' }}</p>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-gray-700 mb-2">Statut</h3>
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                        {{ $product->statut === 'actif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $product->statut === 'actif' ? 'Actif' : 'Inactif' }}
                                    </span>
                                    @if($product->est_populaire)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                            Populaire
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pied de page informatif -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between text-sm text-gray-500">
                    <div>
                        📦 Produit consultable en lecture seule
                    </div>
                    <div>
                        Dernière mise à jour: {{ $product->updated_at->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection