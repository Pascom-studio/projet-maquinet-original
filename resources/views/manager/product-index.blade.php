@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- En-tête -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-[#0b5f37]">Catalogue des Produits</h2>
                        <p class="text-gray-600">Consultation en lecture seule</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('manager.dashboard') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">
                            ← Retour Dashboard
                        </a>
                    </div>
                </div>

                <!-- Filtres et recherche -->
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <form action="{{ route('products.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="categorie_id" class="block text-xs font-medium text-gray-700 mb-1">Catégorie</label>
                            <select name="categorie_id" id="categorie_id" class="w-full text-sm border-gray-300 rounded-md focus:border-[#0b5f37] focus:ring-[#0b5f37]">
                                <option value="">Toutes les catégories</option>
                                @foreach($categories as $categorie)
                                    <option value="{{ $categorie->id }}" {{ request('categorie_id') == $categorie->id ? 'selected' : '' }}>
                                        {{ $categorie->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="search" class="block text-xs font-medium text-gray-700 mb-1">Recherche</label>
                            <input type="text" name="search" id="search" 
                                   class="w-full text-sm border-gray-300 rounded-md focus:border-[#0b5f37] focus:ring-[#0b5f37]"
                                   placeholder="Nom du produit..."
                                   value="{{ request('search') }}">
                        </div>
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="bg-[#0b5f37] text-white px-4 py-2 rounded hover:bg-[#0a4d2c] text-sm">
                                🔍 Filtrer
                            </button>
                            <a href="{{ route('products.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">
                                🗑️ Réinitialiser
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Liste des produits -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catégorie</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($products as $product)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if($product->image)
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->designation }}">
                                                </div>
                                            @else
                                                <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                                    <span class="text-gray-500 text-sm">📦</span>
                                                </div>
                                            @endif
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $product->designation }}</div>
                                                @if($product->description)
                                                    <div class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit($product->description, 50) }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $product->categorie->nom ?? 'Non catégorisé' }}
                                        </span>
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ number_format($product->prix, 0, ',', ' ') }} F
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $product->quantite > 10 ? 'bg-green-100 text-green-800' : ($product->quantite > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ $product->quantite }} unités
                                        </span>
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $product->statut === 'actif' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($product->statut) }}
                                        </span>
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('products.show', $product) }}" class="text-blue-600 hover:text-blue-900" title="Voir détails">
                                            👁️ Voir
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 sm:px-6 py-4 text-center text-sm text-gray-500">
                                        Aucun produit trouvé
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($products->hasPages())
                    <div class="mt-6">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection