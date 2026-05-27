@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <!-- En-tête de la page -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Historique des Commissions</h1>
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
        <form method="GET" action="{{ route('grande-caisse.historique-commissions', $mobileCaissier->id) }}">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Mois -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mois</label>
                    <select name="mois" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[var(--navbar-bg)]">
                        <option value="">Tous les mois</option>
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ request('mois') == $i ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                
                <!-- Année -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Année</label>
                    <select name="annee" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[var(--navbar-bg)]">
                        <option value="">Toutes les années</option>
                        @for($i = date('Y'); $i >= 2020; $i--)
                            <option value="{{ $i }}" {{ request('annee') == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                
                <!-- Opérateur -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Opérateur</label>
                    <select name="operateur" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[var(--navbar-bg)]">
                        <option value="">Tous les opérateurs</option>
                        <option value="orange_money" {{ request('operateur') == 'orange_money' ? 'selected' : '' }}>Orange Money</option>
                        <option value="telecel_money" {{ request('operateur') == 'telecel_money' ? 'selected' : '' }}>Telecel Money</option>
                        <option value="moov_money" {{ request('operateur') == 'moov_money' ? 'selected' : '' }}>Moov Money</option>
                        <option value="coris_money" {{ request('operateur') == 'coris_money' ? 'selected' : '' }}>Coris Money</option>
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
                <a href="{{ route('grande-caisse.historique-commissions', $mobileCaissier->id) }}" 
                   class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 transition-all">
                    🔄 Réinitialiser
                </a>
            </div>
        </form>
    </div>

    <!-- Statistiques globales -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Commission Mois</h3>
                    <p class="text-3xl font-bold text-green-600">{{ number_format($commissionMois, 0, ',', ' ') }} F</p>
                </div>
                <div class="text-2xl">💰</div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Commission Aujourd'hui</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ number_format($commissionAujourdhui, 0, ',', ' ') }} F</p>
                </div>
                <div class="text-2xl">📅</div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Total Global</h3>
                    <p class="text-3xl font-bold text-purple-600">{{ number_format($globalTotalCommissions, 0, ',', ' ') }} F</p>
                </div>
                <div class="text-2xl">📊</div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Périodes</h3>
                    <p class="text-3xl font-bold text-orange-600">{{ $commissionsMensuelles->total() }}</p>
                </div>
                <div class="text-2xl">📈</div>
            </div>
        </div>
    </div>

    <!-- Commissions par opérateur -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">💸 Commissions du Mois par Opérateur</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @foreach($commissionStats as $operateur => $stats)
                <div class="text-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="text-lg font-semibold text-gray-700 mb-2">
                        {{ $stats['operateur_nom'] }}
                    </div>
                    <div class="text-2xl font-bold text-green-600 mb-2">
                        {{ number_format($stats['commission_nette'], 0, ',', ' ') }} F
                    </div>
                    <div class="text-sm text-gray-600 space-y-1">
                        <div>{{ $stats['transactions'] }} transactions</div>
                        <div class="text-xs text-gray-500">
                            Brut: {{ number_format($stats['commission_brute'], 0, ',', ' ') }} F
                        </div>
                        <div class="text-xs text-red-500">
                            Taxes: {{ number_format($stats['taxes'], 0, ',', ' ') }} F
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Tableau des commissions groupées -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Commissions Groupées par Mois et Opérateur</h3>
            <p class="text-gray-600 text-sm">{{ $commissionsMensuelles->total() }} périodes trouvées</p>
        </div>

        @if($commissionsMensuelles->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Période
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Opérateur
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Transactions
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Montant Total
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Commission
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Commission Moyenne
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($commissionsMensuelles as $commission)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $commission->periode)->translatedFormat('F Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        @switch($commission->operateur)
                                            @case('orange_money') 🟠 Orange @break
                                            @case('telecel_money') 🔵 Telecel @break
                                            @case('moov_money') 🟢 Moov @break
                                            @case('coris_money') 🔴 Coris @break
                                        @endswitch
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $commission->type_operation === 'depot' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $commission->type_operation === 'depot' ? 'Dépôt' : 'Retrait' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $commission->nombre_transactions }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    {{ number_format($commission->montant_total, 0, ',', ' ') }} F
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                    +{{ number_format($commission->commission_totale, 0, ',', ' ') }} F
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @php
                                        $moyenne = $commission->nombre_transactions > 0 
                                            ? $commission->commission_totale / $commission->nombre_transactions 
                                            : 0;
                                    @endphp
                                    {{ number_format($moyenne, 0, ',', ' ') }} F
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $commissionsMensuelles->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune commission</h3>
                <p class="mt-1 text-sm text-gray-500">Aucune commission trouvée avec les critères sélectionnés.</p>
            </div>
        @endif
    </div>
</div>

<style>
    .bg-\[var\(--navbar-bg\)\] {
        background-color: #{{ $themeColors['navbar_bg'] ?? '0b5f37' }};
    }
</style>
@endsection