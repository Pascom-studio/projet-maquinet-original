@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- En-tête -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-[#0b5f37]">Détails de la Commande</h2>
                        <p class="text-gray-600">Numéro: {{ $commande->numero_commande }}</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('commandes.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">
                            ← Retour
                        </a>
                        @if ($commande->peutEtreModifieePar(Auth::user()))
                            <a href="{{ route('commandes.edit', $commande) }}" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 text-sm">
                                Modifier
                            </a>
                        @endif
                        @if ($commande->statut !== 'soldée' && (Auth::user()->isAdmin() || Auth::user()->isGerant() || $commande->user_id === Auth::id()))
                            <a href="{{ route('commandes.solder.form', $commande) }}" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 text-sm">
                                Solder
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Informations générales -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-3 text-[#0b5f37]">Informations Générales</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="font-medium">Numéro:</span>
                                <span>{{ $commande->numero_commande }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Statut:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $commande->statut === 'en cours' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $commande->statut === 'soldée' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $commande->statut === 'annulée' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ ucfirst($commande->statut) }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Date création:</span>
                                <span>{{ $commande->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Dernière modification:</span>
                                <span>{{ $commande->updated_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-3 text-[#0b5f37]">Personnel</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="font-medium">Table:</span>
                                <span>
                                    @if ($commande->table)
                                        {{ $commande->table->nom_complet ?? $commande->table->nom }}
                                    @else
                                        <span class="text-red-500">Table supprimée</span>
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Hôtesse:</span>
                                <span>
                                    @if ($commande->hotesse)
                                        {{ $commande->hotesse->prenom }} {{ $commande->hotesse->nom }}
                                    @else
                                        <span class="text-red-500">Hôtesse supprimée</span>
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Caissier:</span>
                                <span>
                                    @if ($commande->user)
                                        {{ $commande->user->prenom }} {{ $commande->user->nom }}
                                    @else
                                        <span class="text-red-500">Caissier supprimé</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Produits de la commande -->
                <div class="bg-white border border-gray-200 rounded-lg mb-6">
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-[#0b5f37]">Produits Commandés</h3>
                    </div>
                    <div class="p-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix Unitaire</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantité</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($commande->produits as $produit)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                                @if ($produit->produit)
                                                    {{ $produit->produit->designation }}
                                                @else
                                                    <span class="text-red-500">Produit supprimé</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ number_format($produit->prix_unitaire, 0, ',', ' ') }} FCFA
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $produit->quantite }}
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ number_format($produit->prix_total, 0, ',', ' ') }} FCFA
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">
                                                Aucun produit dans cette commande
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="3" class="px-4 py-4 text-right text-sm font-medium text-gray-900">
                                            Total:
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-lg font-bold text-[#0b5f37]">
                                            {{ number_format($commande->montant, 0, ',', ' ') }} FCFA
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                @if ($commande->notes)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg mb-6">
                    <div class="bg-yellow-100 px-4 py-3 border-b border-yellow-200">
                        <h3 class="text-lg font-semibold text-yellow-800">Notes</h3>
                    </div>
                    <div class="p-4">
                        <p class="text-sm text-yellow-700">{{ $commande->notes }}</p>
                    </div>
                </div>
                @endif

                <!-- Description -->
@if ($commande->description)
<div class="bg-blue-50 border border-blue-200 rounded-lg mb-6">
    <div class="bg-blue-100 px-4 py-3 border-b border-blue-200">
        <h3 class="text-lg font-semibold text-blue-800">Description</h3>
    </div>
    <div class="p-4">
        <p class="text-sm text-blue-700 whitespace-pre-line">{{ $commande->description }}</p>
    </div>
</div>
@endif

                <!-- Historique des actions -->
                @if($commande->actions && $commande->actions->count() > 0)
                <div class="bg-white border border-gray-200 rounded-lg">
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-[#0b5f37]">Historique des Actions</h3>
                    </div>
                    <div class="p-4">
                        <div class="space-y-3">
                            @foreach($commande->actions->sortByDesc('date_action') as $action)
                                <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <span class="text-blue-600 text-sm font-medium">
                                            {{ substr($action->user->prenom, 0, 1) }}{{ substr($action->user->nom, 0, 1) }}
                                        </span>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $action->user->prenom }} {{ $action->user->nom }}
                                                </p>
                                                <p class="text-sm text-gray-600 capitalize">
                                                    {{ $action->action }}
                                                </p>
                                            </div>
                                            <span class="text-xs text-gray-500">
                                                {{ $action->date_action->format('d/m/Y H:i') }}
                                            </span>
                                        </div>
                                        @if($action->details)
                                            <div class="mt-1 text-xs text-gray-500">
                                                @php
                                                    $details = json_decode($action->details, true);
                                                @endphp
                                                @if(is_array($details))
                                                    @foreach($details as $key => $value)
                                                        @if(!is_array($value))
                                                            <span class="inline-block bg-gray-200 rounded px-2 py-1 mr-1 mb-1">
                                                                {{ $key }}: {{ $value }}
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Actions -->
                <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                    <a href="{{ route('commandes.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 transition duration-150 ease-in-out">
                        Retour à la liste
                    </a>
                    @if ($commande->peutEtreModifieePar(Auth::user()))
                        <a href="{{ route('commandes.edit', $commande) }}" class="bg-yellow-500 text-white px-6 py-2 rounded hover:bg-yellow-600 transition duration-150 ease-in-out">
                            Modifier
                        </a>
                    @endif
                    @if ($commande->statut !== 'soldée' && (Auth::user()->isAdmin() || Auth::user()->isGerant() || $commande->user_id === Auth::id()))
                        <a href="{{ route('commandes.solder.form', $commande) }}" class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600 transition duration-150 ease-in-out">
                            Solder la Commande
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection