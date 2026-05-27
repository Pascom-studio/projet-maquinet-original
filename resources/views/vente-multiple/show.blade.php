@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-[#0b5f37]">Détails de la Vente</h1>
            <div class="flex space-x-2">
                <a href="{{ route('ventes-multiples.receipt', $vente->id) }}" target="_blank" 
                   class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    🖨️ Imprimer le Reçu
                </a>
                <a href="{{ route('ventes-multiples.create') }}" 
                   class="bg-[#0b5f37] text-white px-4 py-2 rounded hover:bg-[#0a4d2c]">
                    ➕ Nouvelle Vente
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="p-6 border-b">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Numéro de Vente</h3>
                        <p class="text-lg font-semibold">{{ $vente->numero_vente }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Date</h3>
                        <p class="text-lg">{{ $vente->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Caissier</h3>
                        <p class="text-lg">{{ $vente->user->prenom }} {{ $vente->user->nom }}</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <h3 class="text-lg font-semibold text-[#0b5f37] mb-4">Produits vendus</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix Unitaire</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantité</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($vente->lignes as $ligne)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $ligne->product->designation }}</div>
                                    <div class="text-sm text-gray-500">{{ $ligne->product->categorie->nom }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($ligne->prix_unitaire, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $ligne->quantite }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-[#0b5f37]">
                                    {{ number_format($ligne->montant_ligne, 0, ',', ' ') }} FCFA
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right text-sm font-semibold text-gray-900">
                                    TOTAL:
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-lg font-bold text-[#0b5f37]">
                                    {{ number_format($vente->montant_total, 0, ',', ' ') }} FCFA
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <a href="{{ route('ventes-multiples.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                ← Retour aux Ventes
            </a>
        </div>
    </div>
</div>
@endsection