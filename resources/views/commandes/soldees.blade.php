@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- En-tête -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-[#0b5f37]">Commandes Soldées</h2>
                        <p class="text-gray-600">Historique des commandes soldées</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('commandes.index') }}" class="bg-[#0b5f37] text-white px-4 py-2 rounded hover:bg-[#0a4d2c] text-sm">
                            Commandes en Cours
                        </a>
                        <!-- BOUTON EXPORT CORRECTEMENT PLACÉ -->
                        @if (Auth::user()->isAdmin() || Auth::user()->isGerant())
                        <a href="{{ route('commandes.export') }}" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 text-sm flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Exporter
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Filtres -->
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <form action="{{ route('commandes.soldees') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        @if (Auth::user()->isAdmin() || Auth::user()->isGerant())
                        <div>
                            <label for="hotesse_id" class="block text-xs font-medium text-gray-700 mb-1">Hôtesse</label>
                            <select name="hotesse_id" id="hotesse_id" class="w-full text-sm border-gray-300 rounded-md focus:border-[#0b5f37] focus:ring-[#0b5f37]">
                                <option value="">Toutes les hôtesses</option>
                                @foreach ($hotesses as $hotesse)
                                    <option value="{{ $hotesse->id }}" {{ request('hotesse_id') == $hotesse->id ? 'selected' : '' }}>
                                        {{ $hotesse->prenom }} {{ $hotesse->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="user_id" class="block text-xs font-medium text-gray-700 mb-1">Caissier</label>
                            <select name="user_id" id="user_id" class="w-full text-sm border-gray-300 rounded-md focus:border-[#0b5f37] focus:ring-[#0b5f37]">
                                <option value="">Tous les caissiers</option>
                                @foreach ($caissiers as $caissier)
                                    <option value="{{ $caissier->id }}" {{ request('user_id') == $caissier->id ? 'selected' : '' }}>
                                        {{ $caissier->prenom }} {{ $caissier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label for="date_debut" class="block text-xs font-medium text-gray-700 mb-1">Date début</label>
                                <input type="date" name="date_debut" id="date_debut" class="w-full text-sm border-gray-300 rounded-md focus:border-[#0b5f37] focus:ring-[#0b5f37]" value="{{ request('date_debut') }}">
                            </div>
                            <div>
                                <label for="date_fin" class="block text-xs font-medium text-gray-700 mb-1">Date fin</label>
                                <input type="date" name="date_fin" id="date_fin" class="w-full text-sm border-gray-300 rounded-md focus:border-[#0b5f37] focus:ring-[#0b5f37]" value="{{ request('date_fin') }}">
                            </div>
                        </div>
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="bg-[#0b5f37] text-white px-4 py-2 rounded hover:bg-[#0a4d2c] text-sm">
                                Filtrer
                            </button>
                            <a href="{{ route('commandes.soldees') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">
                                Réinitialiser
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Statistiques -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-sm font-semibold text-blue-800">Total Commandes Soldées</h3>
                                <p class="text-xl font-bold text-blue-600">{{ $stats['total_commandes'] }}</p>
                            </div>
                            <div class="text-xl">📋</div>
                        </div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-sm font-semibold text-green-800">Chiffre d'Affaires</h3>
                                <p class="text-xl font-bold text-green-600">{{ number_format($stats['chiffre_affaires'], 0, ',', ' ') }} FCFA</p>
                            </div>
                            <div class="text-xl">💰</div>
                        </div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-sm font-semibold text-purple-800">Commande Moyenne</h3>
                                <p class="text-xl font-bold text-purple-600">{{ number_format($stats['commande_moyenne'], 0, ',', ' ') }} FCFA</p>
                            </div>
                            <div class="text-xl">📊</div>
                        </div>
                    </div>
                </div>

                <!-- Liste des commandes soldées -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Commande</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Table</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hôtesse</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produits</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Caissier</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Soldée</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($commandes as $commande)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $commande->numero_commande ?? 'CMD-' . $commande->id }}</div>
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if ($commande->table)
                                            Table {{ $commande->table->numero }} - {{ $commande->table->nom }}
                                        @else
                                            <span class="text-gray-400">Table inconnue</span>
                                        @endif
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if ($commande->hotesse)
                                            {{ $commande->hotesse->prenom }} {{ $commande->hotesse->name }}
                                        @else
                                            <span class="text-gray-400">Hôtesse inconnue</span>
                                        @endif
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $commande->produits->count() }} produit(s)
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ number_format($commande->montant, 0, ',', ' ') }} FCFA
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if ($commande->user)
                                            {{ $commande->user->prenom }} {{ $commande->user->name }}
                                        @else
                                            <span class="text-gray-400">Caissier inconnu</span>
                                        @endif
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $commande->updated_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('commandes.show', $commande) }}" class="text-blue-600 hover:text-blue-900" title="Voir détails">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <!-- Lien vers le reçu -->
                                            <a href="{{ route('commandes.receipt', $commande->id) }}" 
                                               target="_blank"
                                               class="text-green-600 hover:text-green-900" 
                                               title="Imprimer le reçu">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 sm:px-6 py-4 text-center text-sm text-gray-500">
                                        Aucune commande soldée trouvée
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if ($commandes->hasPages())
                    <div class="mt-6">
                        {{ $commandes->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection