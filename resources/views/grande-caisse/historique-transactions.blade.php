@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <!-- En-tête de la page -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Historique des Transactions</h1>
                <p class="text-gray-600 mt-2">
                    {{ $mobileCaissier->prenom }} {{ $mobileCaissier->nom }} - Vue en lecture seule
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('grande-caisse.compte-details', $mobileCaissier->id) }}" 
                   class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-all">
                    ← Retour au compte
                </a>
                <a href="{{ route('grande-caisse.dashboard') }}" 
                   class="bg-[var(--navbar-bg)] text-white px-4 py-2 rounded-lg hover:opacity-90 transition-all">
                    📊 Tableau de bord
                </a>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('grande-caisse.historique-transactions', $mobileCaissier->id) }}">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Date début -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
                    <input type="date" name="date_debut" value="{{ request('date_debut') }}" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[var(--navbar-bg)]">
                </div>
                
                <!-- Date fin -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                    <input type="date" name="date_fin" value="{{ request('date_fin') }}" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[var(--navbar-bg)]">
                </div>
                
                <!-- Nature -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Opérateur</label>
                    <select name="nature" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[var(--navbar-bg)]">
                        <option value="">Tous les opérateurs</option>
                        <option value="orange_money" {{ request('nature') == 'orange_money' ? 'selected' : '' }}>Orange Money</option>
                        <option value="telecel_money" {{ request('nature') == 'telecel_money' ? 'selected' : '' }}>Telecel Money</option>
                        <option value="moov_money" {{ request('nature') == 'moov_money' ? 'selected' : '' }}>Moov Money</option>
                        <option value="coris_money" {{ request('nature') == 'coris_money' ? 'selected' : '' }}>Coris Money</option>
                    </select>
                </div>
                
                <!-- Type opération -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type_operation" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[var(--navbar-bg)]">
                        <option value="">Tous les types</option>
                        <option value="depot" {{ request('type_operation') == 'depot' ? 'selected' : '' }}>Dépôt</option>
                        <option value="retrait" {{ request('type_operation') == 'retrait' ? 'selected' : '' }}>Retrait</option>
                    </select>
                </div>
            </div>
            
            <div class="flex justify-end mt-4 space-x-3">
                <button type="submit" class="bg-[var(--navbar-bg)] text-white px-6 py-2 rounded-md hover:opacity-90 transition-all">
                    🔍 Filtrer
                </button>
                <a href="{{ route('grande-caisse.historique-transactions', $mobileCaissier->id) }}" 
                   class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 transition-all">
                    🔄 Réinitialiser
                </a>
            </div>
        </form>
    </div>

    <!-- Statistiques -->
    @php
        $totalMontant = $transactions->sum('montant');
        $totalCommission = $transactions->sum('commission');
        $totalTransactions = $transactions->total();
        $transactionsActives = $transactions->where('statut', 'actif')->count();
        $transactionsEchouees = $transactions->where('statut', 'echoue')->count();
    @endphp
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Total Transactions</h3>
                    <p class="text-3xl font-bold text-[var(--navbar-bg)]">{{ $totalTransactions }}</p>
                </div>
                <div class="text-2xl">📊</div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Montant Total</h3>
                    <p class="text-3xl font-bold text-green-600">{{ number_format($totalMontant, 0, ',', ' ') }} F</p>
                </div>
                <div class="text-2xl">💰</div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Commissions Total</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ number_format($totalCommission, 0, ',', ' ') }} F</p>
                </div>
                <div class="text-2xl">💸</div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Transactions Actives</h3>
                    <p class="text-3xl font-bold text-green-600">{{ $transactionsActives }}</p>
                    <p class="text-sm text-gray-500">{{ $transactionsEchouees }} échouée(s)</p>
                </div>
                <div class="text-2xl">✅</div>
            </div>
        </div>
    </div>

    <!-- Tableau des transactions -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Liste des Transactions</h3>
            <p class="text-gray-600 text-sm">{{ $transactions->total() }} transactions trouvées</p>
        </div>

        @if($transactions->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date & Heure
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Client
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type & Opérateur
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Montant
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Commission
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Statut
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($transactions as $transaction)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div>{{ $transaction->created_at->format('d/m/Y') }}</div>
                                    <div class="text-gray-500">{{ $transaction->created_at->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $transaction->prenom }} {{ $transaction->nom }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $transaction->telephone }}</div>
                                    @if($transaction->cnib)
                                        <div class="text-xs text-gray-400">CNIB: {{ $transaction->cnib }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <!-- TYPE D'OPÉRATION -->
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $transaction->type_operation === 'depot' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                            @if($transaction->type_operation === 'depot')
                                                ⬆️ Dépôt
                                            @else
                                                ⬇️ Retrait
                                            @endif
                                        </span>
                                        <!-- OPÉRATEUR -->
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            @switch($transaction->nature)
                                                @case('orange_money') 🟠 Orange @break
                                                @case('telecel_money') 🔵 Telecel @break
                                                @case('moov_money') 🟢 Moov @break
                                                @case('coris_money') 🔴 Coris @break
                                            @endswitch
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        ID: {{ $transaction->id_transaction }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    {{ number_format($transaction->montant, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                    +{{ number_format($transaction->commission, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <!-- CORRECTION DÉFINITIVE : Utilisation du VRAI champ statut -->
                                    @php
                                        $statut = $transaction->statut; // Le VRAI champ de la base
                                    @endphp
                                    
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $statut === 'actif' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $statut === 'echoue' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $statut === 'annule' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $statut === 'en_attente' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ !in_array($statut, ['actif', 'echoue', 'annule', 'en_attente']) ? 'bg-gray-100 text-gray-800' : '' }}">
                                        
                                        @if($statut === 'actif')
                                            ✅ Actif
                                        @elseif($statut === 'echoue')
                                            ❌ Échoué
                                        @elseif($statut === 'annule')
                                            ❌ Annulé
                                        @elseif($statut === 'en_attente')
                                            ⏳ En attente
                                        @else
                                            {{ $statut }}
                                        @endif
                                    </span>
                                    
                                    <!-- Debug: Afficher le statut brut pour vérification -->
                                    <div class="text-xs text-gray-400 mt-1">
                                        DB: "{{ $statut }}"
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $transactions->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune transaction</h3>
                <p class="mt-1 text-sm text-gray-500">Aucune transaction trouvée avec les critères sélectionnés.</p>
            </div>
        @endif
    </div>
</div>

<style>
    .bg-\[var\(--navbar-bg\)\] {
        background-color: #{{ $themeColors['navbar_bg'] ?? '0b5f37' }};
    }
</style>

<script>
function corrigerStatuts() {
    if(confirm('Voulez-vous corriger automatiquement les statuts des transactions?')) {
        // Implémentez ici l'appel API pour corriger les statuts
        alert('Fonctionnalité de correction à implémenter');
    }
}
</script>
@endsection