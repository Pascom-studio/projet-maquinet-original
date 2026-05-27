@extends('layouts.app')

@section('content')
@php
    $user = Auth::user();
    
    // Calcul des statistiques RÉELLES depuis la collection paginée
    $allTransactions = $transactions->getCollection();
    $totalTransactions = $transactions->total();
    $transactionsAujourdhui = $allTransactions->where('created_at', '>=', today())->count();
    $totalDepots = $allTransactions->where('type_operation', 'depot')->sum('montant');
    $totalRetraits = $allTransactions->where('type_operation', 'retrait')->sum('montant');
    
    // Pour les statistiques globales (hors pagination)
    $queryGlobal = App\Models\MobileMoneyTransaction::where('user_id', $user->id);
    $globalTotalTransactions = $queryGlobal->count();
    $globalTodayTransactions = $queryGlobal->whereDate('created_at', today())->count();
@endphp

<div class="py-4 sm:py-6">
    <!-- En-tête -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[#8c52ff] px-2 sm:px-0">📊 Historique des Transactions</h1>
            <p class="text-gray-600 px-2 sm:px-0">Vos transactions Mobile Money - {{ $user->prenom }}</p>
        </div>
        
        <div class="mt-2 sm:mt-0 px-2 sm:px-0 flex space-x-2">
            <a href="{{ route('mobile-money.historique-commission') }}" class="bg-[#8c52ff] text-white px-4 py-2 rounded hover:bg-[#7a41e6] text-sm flex items-center">
                <span class="mr-2">💸</span>
                Historique Commissions
            </a>
            <a href="{{ route('mobile-money.create') }}" class="bg-[#25D366] text-white px-4 py-2 rounded hover:bg-[#1ea952] text-sm flex items-center">
                <span class="mr-2">➕</span>
                Nouvelle Transaction
            </a>
        </div>
    </div>

    <!-- Cartes Statistiques TRANSACTIONS SEULEMENT -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6 px-2 sm:px-0">
        <!-- Total Transactions -->
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-[#8c52ff]">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-gray-700 truncate">Total Transactions</h3>
                    <p class="text-2xl sm:text-3xl font-bold text-[#8c52ff]">{{ number_format($globalTotalTransactions, 0, ',', ' ') }}</p>
                </div>
                <div class="text-xl sm:text-2xl ml-2">📈</div>
            </div>
            <p class="text-xs text-gray-500 mt-1">Toutes périodes</p>
        </div>

        <!-- Transactions Aujourd'hui -->
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-[#25D366]">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-gray-700 truncate">Aujourd'hui</h3>
                    <p class="text-2xl sm:text-3xl font-bold text-[#25D366]">{{ number_format($globalTodayTransactions, 0, ',', ' ') }}</p>
                </div>
                <div class="text-xl sm:text-2xl ml-2">🔄</div>
            </div>
            <p class="text-xs text-gray-500 mt-1">Transactions ce jour</p>
        </div>

        <!-- Dépots Total -->
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-gray-700 truncate">Total Dépôts</h3>
                    <p class="text-2xl sm:text-3xl font-bold text-green-600">{{ number_format($allTransactions->where('type_operation', 'depot')->sum('montant'), 0, ',', ' ') }} F</p>
                </div>
                <div class="text-xl sm:text-2xl ml-2">💰</div>
            </div>
            <p class="text-xs text-gray-500 mt-1">Montant total des dépôts</p>
        </div>

        <!-- Retraits Total -->
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-gray-700 truncate">Total Retraits</h3>
                    <p class="text-2xl sm:text-3xl font-bold text-red-600">{{ number_format($allTransactions->where('type_operation', 'retrait')->sum('montant'), 0, ',', ' ') }} F</p>
                </div>
                <div class="text-xl sm:text-2xl ml-2">💳</div>
            </div>
            <p class="text-xs text-gray-500 mt-1">Montant total des retraits</p>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6 mx-2 sm:mx-0">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">🔍 Filtres</h3>
        
        <form method="GET" action="{{ route('mobile-money.historique') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Date début -->
            <div>
                <label for="date_debut" class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
                <input type="date" id="date_debut" name="date_debut" value="{{ request('date_debut') }}" 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#8c52ff] focus:border-[#8c52ff]">
            </div>
            
            <!-- Date fin -->
            <div>
                <label for="date_fin" class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                <input type="date" id="date_fin" name="date_fin" value="{{ request('date_fin') }}" 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#8c52ff] focus:border-[#8c52ff]">
            </div>
            
            <!-- Opérateur -->
            <div>
                <label for="nature" class="block text-sm font-medium text-gray-700 mb-1">Opérateur</label>
                <select id="nature" name="nature" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#8c52ff] focus:border-[#8c52ff]">
                    <option value="">Tous les opérateurs</option>
                    <option value="orange_money" {{ request('nature') == 'orange_money' ? 'selected' : '' }}>Orange Money</option>
                    <option value="telecel_money" {{ request('nature') == 'telecel_money' ? 'selected' : '' }}>Telecel Money</option>
                    <option value="moov_money" {{ request('nature') == 'moov_money' ? 'selected' : '' }}>Moov Money</option>
                    <option value="coris_money" {{ request('nature') == 'coris_money' ? 'selected' : '' }}>Coris Money</option>
                </select>
            </div>
            
            <!-- Type d'opération -->
            <div>
                <label for="type_operation" class="block text-sm font-medium text-gray-700 mb-1">Type d'opération</label>
                <select id="type_operation" name="type_operation" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#8c52ff] focus:border-[#8c52ff]">
                    <option value="">Tous les types</option>
                    <option value="depot" {{ request('type_operation') == 'depot' ? 'selected' : '' }}>Dépôt</option>
                    <option value="retrait" {{ request('type_operation') == 'retrait' ? 'selected' : '' }}>Retrait</option>
                </select>
            </div>
            
            <!-- Boutons -->
            <div class="sm:col-span-2 lg:col-span-4 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                <button type="submit" class="bg-[#8c52ff] text-white px-4 py-2 rounded hover:bg-[#7a41e6] text-sm flex items-center justify-center">
                    <span class="mr-2">🔍</span>
                    Appliquer les filtres
                </button>
                
                <a href="{{ route('mobile-money.historique') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm flex items-center justify-center">
                    <span class="mr-2">🔄</span>
                    Réinitialiser
                </a>
            </div>
        </form>

        <!-- CORRECTION : Formulaire d'export SÉPARÉ avec GET -->
        @if($transactions->count() > 0)
        <div class="mt-4 pt-4 border-t border-gray-200">
            <h4 class="text-md font-semibold text-gray-800 mb-2">📤 Exporter les données</h4>
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                <!-- CORRECTION : Utiliser des liens GET au lieu de formulaire POST -->
                <a href="{{ route('mobile-money.export') }}?date_debut={{ request('date_debut', now()->startOfMonth()->format('Y-m-d')) }}&date_fin={{ request('date_fin', now()->endOfMonth()->format('Y-m-d')) }}&nature={{ request('nature') }}&type_operation={{ request('type_operation') }}&format=excel" 
                   class="bg-[#25D366] text-white px-4 py-2 rounded hover:bg-[#1ea952] text-sm flex items-center justify-center">
                    <span class="mr-2">📊</span>
                    Exporter Excel
                </a>
                
                <a href="{{ route('mobile-money.export') }}?date_debut={{ request('date_debut', now()->startOfMonth()->format('Y-m-d')) }}&date_fin={{ request('date_fin', now()->endOfMonth()->format('Y-m-d')) }}&nature={{ request('nature') }}&type_operation={{ request('type_operation') }}&format=csv" 
                   class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm flex items-center justify-center">
                    <span class="mr-2">📄</span>
                    Exporter CSV
                </a>
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
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Transaction</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Opérateur</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commission</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Téléphone</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transactions as $transaction)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $transaction->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $transaction->id_transaction }}
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="font-medium">{{ $transaction->prenom }} {{ $transaction->nom }}</div>
                                @if($transaction->cnib)
                                    <div class="text-xs text-gray-500">CNIB: {{ $transaction->cnib }}</div>
                                @endif
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                    {{ $transaction->nature == 'orange_money' ? 'bg-orange-100 text-orange-800' : '' }}
                                    {{ $transaction->nature == 'telecel_money' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $transaction->nature == 'moov_money' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $transaction->nature == 'coris_money' ? 'bg-purple-100 text-purple-800' : '' }}">
                                    @if($transaction->nature == 'orange_money')
                                        Orange Money
                                    @elseif($transaction->nature == 'telecel_money')
                                        Telecel Money
                                    @elseif($transaction->nature == 'moov_money')
                                        Moov Money
                                    @elseif($transaction->nature == 'coris_money')
                                        Coris Money
                                    @else
                                        {{ $transaction->nature }}
                                    @endif
                                </span>
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
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
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ number_format($transaction->montant, 0, ',', ' ') }} F
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-bold text-[#8c52ff]">
                                {{ number_format($transaction->commission, 0, ',', ' ') }} F
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $transaction->telephone ?? 'N/A' }}
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
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
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('mobile-money.show', $transaction) }}" 
                                       class="text-blue-600 hover:text-blue-900"
                                       title="Voir détails">
                                        👁️
                                    </a>
                                    
                                    @if($transaction->peut_etre_modifie)
                                        <a href="{{ route('mobile-money.edit', $transaction) }}" 
                                           class="text-green-600 hover:text-green-900"
                                           title="Modifier">
                                            ✏️
                                        </a>
                                        
                                        <form action="{{ route('mobile-money.supprimer', $transaction) }}" 
                                              method="POST" 
                                              class="inline"
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement cette transaction ? Cette action est irréversible et déduira la commission du total.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900"
                                                    title="Supprimer définitivement">
                                                🗑️
                                            </button>
                                        </form>
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
@endsection