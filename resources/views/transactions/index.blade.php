@extends('layouts.app')

@section('content')
<div class="py-4 sm:py-6">
    <div class="max-w-7xl mx-auto px-2 sm:px-0">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-[#0b5f37]">📱 Mobile Money</h1>
                <p class="text-gray-600">Gestion des transactions Mobile Money</p>
            </div>
            <div class="mt-4 sm:mt-0 flex flex-wrap gap-2">
                <!-- Nouvelle Transaction - Bouton principal réduit -->
                <a href="{{ route('mobile-money.create') }}" 
                   class="bg-[#0b5f37] text-white px-3 py-2 rounded hover:bg-[#0a4d2c] inline-flex items-center text-sm">
                    <span class="mr-1">➕</span>
                    <span class="hidden sm:inline">Nouvelle</span>
                    <span class="sm:hidden">Nouv.</span>
                </a>

                <!-- Export CSV - Bouton réduit -->
                <form action="{{ route('mobile-money.export') }}" method="GET" class="inline">
                    <input type="hidden" name="date_debut" value="{{ request('date_debut', now()->startOfMonth()->format('Y-m-d')) }}">
                    <input type="hidden" name="date_fin" value="{{ request('date_fin', now()->endOfMonth()->format('Y-m-d')) }}">
                    <input type="hidden" name="type_operation" value="{{ request('type_operation') }}">
                    <input type="hidden" name="nature" value="{{ request('nature') }}">
                    <input type="hidden" name="format" value="csv">
                    <button type="submit" class="bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700 inline-flex items-center text-sm">
                        <span class="mr-1">📥</span>
                        <span class="hidden sm:inline">CSV</span>
                        <span class="sm:hidden">CSV</span>
                    </button>
                </form>

                <!-- Export Excel - Bouton réduit -->
                <form action="{{ route('mobile-money.export') }}" method="GET" class="inline">
                    <input type="hidden" name="date_debut" value="{{ request('date_debut', now()->startOfMonth()->format('Y-m-d')) }}">
                    <input type="hidden" name="date_fin" value="{{ request('date_fin', now()->endOfMonth()->format('Y-m-d')) }}">
                    <input type="hidden" name="type_operation" value="{{ request('type_operation') }}">
                    <input type="hidden" name="nature" value="{{ request('nature') }}">
                    <input type="hidden" name="format" value="excel">
                    <button type="submit" class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700 inline-flex items-center text-sm">
                        <span class="mr-1">📊</span>
                        <span class="hidden sm:inline">Excel</span>
                        <span class="sm:hidden">XLS</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Cartes Statistiques Générales AVEC LIQUIDITÉ -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
            <div class="bg-white rounded-lg shadow p-3 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-semibold text-gray-700">Total Dépôts</h3>
                        <p class="text-lg font-bold text-green-600">{{ number_format($statistiques['total_depots'], 0, ',', ' ') }} F</p>
                    </div>
                    <div class="text-xl">📥</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-3 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-semibold text-gray-700">Total Retraits</h3>
                        <p class="text-lg font-bold text-red-600">{{ number_format($statistiques['total_retraits'], 0, ',', ' ') }} F</p>
                    </div>
                    <div class="text-xl">📤</div>
                </div>
            </div>

            <!-- NOUVELLE CARTE LIQUIDITÉ -->
            <div class="bg-white rounded-lg shadow p-3 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-semibold text-gray-700">💧 Liquidité</h3>
                        <p class="text-lg font-bold text-blue-600">{{ number_format($statistiques['liquidite'], 0, ',', ' ') }} F</p>
                    </div>
                    <div class="text-xl">💧</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-3 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-semibold text-gray-700">Solde Opérateurs</h3>
                        <p class="text-lg font-bold text-purple-600">{{ number_format($statistiques['solde_operateurs'], 0, ',', ' ') }} F</p>
                    </div>
                    <div class="text-xl">📱</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-3 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-semibold text-gray-700">Solde Net Total</h3>
                        <p class="text-lg font-bold text-orange-600">{{ number_format($statistiques['solde_net'], 0, ',', ' ') }} F</p>
                    </div>
                    <div class="text-xl">💰</div>
                </div>
            </div>
        </div>

        <!-- Cartes Soldes par Opérateur -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
            <!-- Orange Money -->
            <div class="bg-white rounded-lg shadow p-3 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-semibold text-gray-700">Solde Orange Money</h3>
                        <p class="text-lg font-bold text-orange-600">{{ number_format($statistiques['solde_orange_money'], 0, ',', ' ') }} F</p>
                    </div>
                    <div class="text-xl">🟠</div>
                </div>
            </div>

            <!-- Telecel Money -->
            <div class="bg-white rounded-lg shadow p-3 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-semibold text-gray-700">Solde Telecel Money</h3>
                        <p class="text-lg font-bold text-blue-600">{{ number_format($statistiques['solde_telecel_money'], 0, ',', ' ') }} F</p>
                    </div>
                    <div class="text-xl">🔵</div>
                </div>
            </div>

            <!-- Moov Money -->
            <div class="bg-white rounded-lg shadow p-3 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-semibold text-gray-700">Solde Moov Money</h3>
                        <p class="text-lg font-bold text-green-600">{{ number_format($statistiques['solde_moov_money'], 0, ',', ' ') }} F</p>
                    </div>
                    <div class="text-xl">🟢</div>
                </div>
            </div>

            <!-- Coris Money - Cercle Jaune -->
            <div class="bg-white rounded-lg shadow p-3 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-semibold text-gray-700">Solde Coris Money</h3>
                        <p class="text-lg font-bold text-purple-600">{{ number_format($statistiques['solde_coris_money'], 0, ',', ' ') }} F</p>
                    </div>
                    <div class="w-6 h-6 bg-yellow-500 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-xs">C</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type d'opération</label>
                    <select name="type_operation" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37] text-sm">
                        <option value="">Tous les types</option>
                        <option value="depot" {{ request('type_operation') == 'depot' ? 'selected' : '' }}>Dépôt</option>
                        <option value="retrait" {{ request('type_operation') == 'retrait' ? 'selected' : '' }}>Retrait</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nature</label>
                    <select name="nature" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37] text-sm">
                        <option value="">Toutes les natures</option>
                        <option value="orange_money" {{ request('nature') == 'orange_money' ? 'selected' : '' }}>Orange Money</option>
                        <option value="telecel_money" {{ request('nature') == 'telecel_money' ? 'selected' : '' }}>Telecel Money</option>
                        <option value="moov_money" {{ request('nature') == 'moov_money' ? 'selected' : '' }}>Moov Money</option>
                        <option value="coris_money" {{ request('nature') == 'coris_money' ? 'selected' : '' }}>Coris Money</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
                    <input type="date" name="date_debut" value="{{ request('date_debut') }}" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37] text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                    <input type="date" name="date_fin" value="{{ request('date_fin') }}" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37] text-sm">
                </div>

                <div class="md:col-span-4 flex space-x-2">
                    <button type="submit" class="bg-[#0b5f37] text-white px-4 py-2 rounded hover:bg-[#0a4d2c] text-sm">
                        🔍 Filtrer
                    </button>
                    <a href="{{ route('mobile-money.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">
                        🔄 Réinitialiser
                    </a>
                </div>
            </form>

            <!-- Boutons d'export supplémentaires -->
            @if($transactions->count() > 0)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <h4 class="text-sm font-semibold text-gray-800 mb-2">📤 Exporter les données filtrées</h4>
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                    <!-- Export Excel -->
                    <form action="{{ route('mobile-money.export') }}" method="GET" class="inline">
                        <input type="hidden" name="date_debut" value="{{ request('date_debut', now()->startOfMonth()->format('Y-m-d')) }}">
                        <input type="hidden" name="date_fin" value="{{ request('date_fin', now()->endOfMonth()->format('Y-m-d')) }}">
                        <input type="hidden" name="type_operation" value="{{ request('type_operation') }}">
                        <input type="hidden" name="nature" value="{{ request('nature') }}">
                        <input type="hidden" name="format" value="excel">
                        <button type="submit" class="bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700 text-sm flex items-center justify-center">
                            <span class="mr-1">📊</span>
                            Exporter Excel
                        </button>
                    </form>

                    <!-- Export CSV -->
                    <form action="{{ route('mobile-money.export') }}" method="GET" class="inline">
                        <input type="hidden" name="date_debut" value="{{ request('date_debut', now()->startOfMonth()->format('Y-m-d')) }}">
                        <input type="hidden" name="date_fin" value="{{ request('date_fin', now()->endOfMonth()->format('Y-m-d')) }}">
                        <input type="hidden" name="type_operation" value="{{ request('type_operation') }}">
                        <input type="hidden" name="nature" value="{{ request('nature') }}">
                        <input type="hidden" name="format" value="csv">
                        <button type="submit" class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700 text-sm flex items-center justify-center">
                            <span class="mr-1">📄</span>
                            Exporter CSV
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>

        <!-- Tableau des transactions COMPLET -->
        <div class="bg-white rounded-lg shadow mx-2 sm:mx-0">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-800">📋 Liste des Transactions</h3>
                    <div class="mt-2 sm:mt-0 text-sm text-gray-600">
                        {{ $transactions->total() }} transaction(s) trouvée(s)
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-3 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Transaction</th>
                            <th class="px-3 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                            <th class="px-3 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Opérateur</th>
                            <th class="px-3 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-3 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                            <th class="px-3 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commission</th>
                            <th class="px-3 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Téléphone</th>
                            <th class="px-3 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-3 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($transactions as $transaction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 sm:px-4 py-3 whitespace-nowrap text-xs text-gray-900">
                                    {{ $transaction->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-3 sm:px-4 py-3 whitespace-nowrap text-xs font-medium text-gray-900">
                                    {{ $transaction->id_transaction }}
                                </td>
                                <td class="px-3 sm:px-4 py-3 whitespace-nowrap text-xs text-gray-900">
                                    <div class="font-medium">{{ $transaction->prenom }} {{ $transaction->nom }}</div>
                                    @if($transaction->cnib)
                                        <div class="text-xs text-gray-500">CNIB: {{ $transaction->cnib }}</div>
                                    @endif
                                </td>
                                <td class="px-3 sm:px-4 py-3 whitespace-nowrap text-xs text-gray-900">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                        {{ $transaction->nature == 'orange_money' ? 'bg-orange-100 text-orange-800' : '' }}
                                        {{ $transaction->nature == 'telecel_money' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $transaction->nature == 'moov_money' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $transaction->nature == 'coris_money' ? 'bg-purple-100 text-purple-800' : '' }}">
                                        @if($transaction->nature == 'orange_money')
                                            Orange
                                        @elseif($transaction->nature == 'telecel_money')
                                            Telecel
                                        @elseif($transaction->nature == 'moov_money')
                                            Moov
                                        @elseif($transaction->nature == 'coris_money')
                                            Coris
                                        @else
                                            {{ $transaction->nature }}
                                        @endif
                                    </span>
                                </td>
                                <td class="px-3 sm:px-4 py-3 whitespace-nowrap text-xs text-gray-900">
                                    @if($transaction->type_operation == 'depot')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <span class="mr-1">⬆️</span> Dépôt
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <span class="mr-1">⬇️</span> Retrait
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 sm:px-4 py-3 whitespace-nowrap text-xs font-medium text-gray-900">
                                    {{ number_format($transaction->montant, 0, ',', ' ') }} F
                                </td>
                                <td class="px-3 sm:px-4 py-3 whitespace-nowrap text-xs font-bold text-[#8c52ff]">
                                    {{ number_format($transaction->commission, 0, ',', ' ') }} F
                                </td>
                                <td class="px-3 sm:px-4 py-3 whitespace-nowrap text-xs text-gray-900">
                                    {{ $transaction->telephone ?? 'N/A' }}
                                </td>
                                <td class="px-3 sm:px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $transaction->statut === 'actif' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $transaction->statut === 'annule' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $transaction->statut === 'en_attente' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                        @if($transaction->statut === 'actif')
                                            ✅ Actif
                                        @elseif($transaction->statut === 'annule')
                                            ❌ Annulé
                                        @elseif($transaction->statut === 'en_attente')
                                            ⏳ En attente
                                        @else
                                            {{ $transaction->statut }}
                                        @endif
                                    </span>
                                </td>
                                <td class="px-3 sm:px-4 py-3 whitespace-nowrap text-xs font-medium">
                                    <div class="flex space-x-1">
                                        <a href="{{ route('mobile-money.show', $transaction) }}" 
                                           class="text-blue-600 hover:text-blue-900 p-1"
                                           title="Voir détails">
                                            👁️
                                        </a>
                                        
                                        @if($transaction->peut_etre_modifie)
                                            <a href="{{ route('mobile-money.edit', $transaction) }}" 
                                               class="text-green-600 hover:text-green-900 p-1"
                                               title="Modifier">
                                                ✏️
                                            </a>
                                            
                                            <!-- CORRECTION : Utiliser supprimer au lieu de annuler -->
                                            <form action="{{ route('mobile-money.supprimer', $transaction) }}" 
                                                  method="POST" 
                                                  class="inline"
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement cette transaction ? Cette action est irréversible et déduira la commission du total.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="text-red-600 hover:text-red-900 p-1"
                                                        title="Supprimer définitivement">
                                                    🗑️
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-gray-400 cursor-not-allowed p-1" title="Transaction non modifiable après 24h">
                                                ⏰
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 sm:px-6 py-4 text-center text-sm text-gray-500">
                                    <div class="text-center py-8">
                                        <div class="text-gray-400 text-4xl mb-4">📊</div>
                                        <p class="text-gray-500">Aucune transaction trouvée</p>
                                        <p class="text-gray-400 text-sm mt-2">Les transactions apparaîtront ici après vos opérations</p>
                                        <a href="{{ route('mobile-money.create') }}" class="inline-block mt-4 bg-[#8c52ff] text-white px-4 py-2 rounded hover:bg-[#7a41e6] text-sm">
                                            ➕ Créer une transaction
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($transactions->hasPages())
            <div class="px-4 sm:px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $transactions->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Styles supplémentaires pour améliorer l'affichage mobile -->
<style>
    @media (max-width: 640px) {
        .container {
            padding: 0 8px;
        }
        
        /* Réduire l'espacement des cellules du tableau */
        table {
            font-size: 11px;
        }
        
        /* Masquer certaines colonnes sur mobile */
        .mobile-hidden {
            display: none;
        }
        
        /* Assurer que les boutons ne débordent pas */
        .flex-wrap {
            flex-wrap: wrap;
        }
        
        /* Réduire la taille des icônes */
        .text-xl {
            font-size: 18px;
        }
        
        .text-lg {
            font-size: 16px;
        }
    }
    
    /* Amélioration du responsive pour les très petits écrans */
    @media (max-width: 380px) {
        .grid-cols-2 {
            grid-template-columns: 1fr;
        }
        
        .flex-wrap {
            justify-content: center;
        }
        
        .flex-wrap > * {
            margin-bottom: 8px;
        }
    }
</style>
@endsection