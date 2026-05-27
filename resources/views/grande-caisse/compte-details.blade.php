@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <!-- En-tête de la page -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Détails du Compte Mobile Caissier</h1>
                <p class="text-gray-600 mt-2">Vue en lecture seule - Grande Caisse Mobile</p>
            </div>
            <a href="{{ route('grande-caisse.dashboard') }}" 
               class="bg-[var(--navbar-bg)] text-white px-4 py-2 rounded-lg hover:opacity-90 transition-all">
                ← Retour au tableau de bord
            </a>
        </div>
    </div>

    @if($mobileCaissier)
        <!-- Cartes d'informations principales -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Informations du caissier -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-16 h-16 bg-[var(--navbar-bg)] rounded-full flex items-center justify-center text-white text-xl font-bold">
                        {{ strtoupper(substr($mobileCaissier->prenom, 0, 1)) }}{{ strtoupper(substr($mobileCaissier->nom, 0, 1)) }}
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">{{ $mobileCaissier->prenom }} {{ $mobileCaissier->nom }}</h2>
                        <span class="inline-block bg-[#8c52ff] text-white px-2 py-1 rounded text-xs mt-1">
                            {{ $mobileCaissier->fonction }}
                        </span>
                    </div>
                </div>
                <div class="space-y-2 text-sm text-gray-600">
                    <p><strong class="text-gray-800">Email:</strong> {{ $mobileCaissier->email }}</p>
                    <p><strong class="text-gray-800">Téléphone:</strong> {{ $mobileCaissier->telephone ?? 'Non renseigné' }}</p>
                    <p><strong class="text-gray-800">Date d'inscription:</strong> {{ $mobileCaissier->created_at->format('d/m/Y') }}</p>
                    <p><strong class="text-gray-800">Commercial:</strong> {{ $mobileCaissier->commercial->prenom ?? 'Non affecté' }} {{ $mobileCaissier->commercial->nom ?? '' }}</p>
                </div>
            </div>

            <!-- Statistiques aujourd'hui -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Aujourd'hui</h3>
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-gray-900">{{ $transactionsAujourdhui }}</div>
                    <p class="text-gray-600 text-sm mt-2">Transactions réalisées</p>
                </div>
            </div>

            <!-- Commissions du mois -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Ce Mois</h3>
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-gray-900">{{ number_format($commissionsMois, 0, ',', ' ') }} FCFA</div>
                    <p class="text-gray-600 text-sm mt-2">Commissions générées</p>
                </div>
            </div>

            <!-- Liquidité -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Liquidité</h3>
                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-gray-900">{{ number_format($liquidite, 0, ',', ' ') }} FCFA</div>
                    <p class="text-gray-600 text-sm mt-2">Fonds disponibles</p>
                </div>
            </div>
        </div>

        <!-- Section Soldes par Opérateur -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            @foreach($soldesOperateurs as $operateur => $data)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $data['nom'] }}</h3>
                        <div class="w-10 h-10 rounded-full flex items-center justify-center 
                            @switch($operateur)
                                @case('orange_money') bg-orange-100 text-orange-600 @break
                                @case('telecel_money') bg-blue-100 text-blue-600 @break
                                @case('moov_money') bg-green-100 text-green-600 @break
                                @case('coris_money') bg-red-100 text-red-600 @break
                                @default bg-gray-100 text-gray-600
                            @endswitch">
                            @switch($operateur)
                                @case('orange_money') 🟠 @break
                                @case('telecel_money') 🔵 @break
                                @case('moov_money') 🟢 @break
                                @case('coris_money') 🔴 @break
                                @default 💰
                            @endswitch
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold 
                            {{ $data['solde'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($data['solde'], 0, ',', ' ') }} FCFA
                        </div>
                        <p class="text-gray-600 text-sm mt-2">Solde actuel</p>
                        <div class="text-xs text-gray-500 mt-2">
                            {{ $data['transactions'] ?? 0 }} transactions
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Section Historique des Commissions -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold text-gray-900">📈 Historique des Commissions (6 derniers mois)</h3>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                @foreach($historiqueCommissions as $mois => $commission)
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <div class="text-sm font-medium text-gray-600 mb-2">
                            {{ \Carbon\Carbon::createFromFormat('Y-m', $mois)->translatedFormat('M Y') }}
                        </div>
                        <div class="text-lg font-bold text-green-600">
                            {{ number_format($commission, 0, ',', ' ') }} F
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Grille des sections détaillées -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Transactions Récentes CORRIGÉES -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">💳 Transactions Récentes</h3>
                    <p class="text-gray-600 text-sm">Les 10 dernières transactions</p>
                </div>

                @if($transactionsRecentes->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date & Heure
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type
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
                                @foreach($transactionsRecentes as $transaction)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $transaction->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $transaction->type ?? 'Transaction' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                            {{ number_format($transaction->montant, 0, ',', ' ') }} FCFA
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                            +{{ number_format($transaction->commission, 0, ',', ' ') }} FCFA
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <!-- CORRECTION : Utiliser les VRAIS statuts -->
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $transaction->statut === 'actif' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $transaction->statut === 'echoue' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $transaction->statut === 'annule' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $transaction->statut === 'en_attente' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ !in_array($transaction->statut, ['actif', 'echoue', 'annule', 'en_attente']) ? 'bg-gray-100 text-gray-800' : '' }}">
                                                
                                                @if($transaction->statut === 'actif')
                                                    ✅ Actif
                                                @elseif($transaction->statut === 'echoue')
                                                    ❌ Échoué
                                                @elseif($transaction->statut === 'annule')
                                                    ❌ Annulé
                                                @elseif($transaction->statut === 'en_attente')
                                                    ⏳ En attente
                                                @else
                                                    {{ $transaction->statut }}
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune transaction</h3>
                        <p class="mt-1 text-sm text-gray-500">Aucune transaction n'a été effectuée par ce caissier.</p>
                    </div>
                @endif
            </div>

            <!-- Mouvements de Stock -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">📦 Mouvements de Stock Récents</h3>
                    <p class="text-gray-600 text-sm">Les 10 derniers mouvements</p>
                </div>

                @if($mouvementsStock->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Opérateur
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Montant
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($mouvementsStock as $mouvement)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $mouvement->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $mouvement->type_mouvement === 'approvisionnement' ? 'bg-green-100 text-green-800' : 
                                                   ($mouvement->type_mouvement === 'avoir' ? 'bg-blue-100 text-blue-800' :
                                                   ($mouvement->type_mouvement === 'depense' ? 'bg-red-100 text-red-800' : 'bg-red-100 text-red-800')) }}">
                                                @if($mouvement->type_mouvement === 'approvisionnement')
                                                    Approvisionnement
                                                @elseif($mouvement->type_mouvement === 'avoir')
                                                    Avoir
                                                @elseif($mouvement->type_mouvement === 'depense')
                                                    Dépense
                                                @else
                                                    Remboursement
                                                @endif
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @switch($mouvement->operateur)
                                                @case('orange_money') Orange Money @break
                                                @case('telecel_money') Telecel Money @break
                                                @case('moov_money') Moov Money @break
                                                @case('coris_money') Coris Money @break
                                                @default {{ ucfirst(str_replace('_', ' ', $mouvement->operateur)) }}
                                            @endswitch
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold 
                                            {{ $mouvement->type_mouvement === 'approvisionnement' || $mouvement->type_mouvement === 'avoir' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $mouvement->type_mouvement === 'approvisionnement' || $mouvement->type_mouvement === 'avoir' ? '+' : '-' }}{{ number_format($mouvement->montant, 0, ',', ' ') }} FCFA
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun mouvement de stock</h3>
                        <p class="mt-1 text-sm text-gray-500">Aucun mouvement de stock n'a été effectué.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Statistiques Commissions par Opérateur -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold text-gray-900">💸 Commissions du Mois par Opérateur</h3>
            </div>
            
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

        <!-- Liens vers les pages détaillées -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="{{ route('grande-caisse.historique-transactions', $mobileCaissier->id) }}" 
               class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Historique Complet</h3>
                        <p class="text-gray-600 text-sm">Voir toutes les transactions</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('grande-caisse.historique-commissions', $mobileCaissier->id) }}" 
               class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Détails Commissions</h3>
                        <p class="text-gray-600 text-sm">Analyse détaillée des commissions</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('grande-caisse.gestion-stock', $mobileCaissier->id) }}" 
               class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Gestion du Stock</h3>
                        <p class="text-gray-600 text-sm">Voir la gestion des fonds</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Résumé des Performances -->
        <div class="bg-white rounded-lg shadow-md p-6 mt-8">
            <h3 class="text-xl font-semibold text-gray-900 mb-6">📊 Résumé des Performances</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Transactions -->
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600 mb-2">
                        {{ $mobileCaissier->transactions_count ?? 0 }}
                    </div>
                    <div class="text-sm font-medium text-blue-800">Total Transactions</div>
                </div>

                <!-- Commission Moyenne -->
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <div class="text-2xl font-bold text-green-600 mb-2">
                        @php
                            $commissionMoyenne = $mobileCaissier->transactions_count > 0 
                                ? $commissionsMois / $mobileCaissier->transactions_count 
                                : 0;
                        @endphp
                        {{ number_format($commissionMoyenne, 0, ',', ' ') }} F
                    </div>
                    <div class="text-sm font-medium text-green-800">Commission Moyenne</div>
                </div>

                <!-- Solde Total Opérateurs -->
                <div class="text-center p-4 bg-orange-50 rounded-lg">
                    <div class="text-2xl font-bold text-orange-600 mb-2">
                        @php
                            $soldeTotalOperateurs = collect($soldesOperateurs)->sum('solde');
                        @endphp
                        {{ number_format($soldeTotalOperateurs, 0, ',', ' ') }} F
                    </div>
                    <div class="text-sm font-medium text-orange-800">Solde Opérateurs</div>
                </div>

                <!-- Patrimoine Total -->
                <div class="text-center p-4 bg-purple-50 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600 mb-2">
                        @php
                            $patrimoineTotal = $soldeTotalOperateurs + $liquidite;
                        @endphp
                        {{ number_format($patrimoineTotal, 0, ',', ' ') }} F
                    </div>
                    <div class="text-sm font-medium text-purple-800">Patrimoine Total</div>
                </div>
            </div>
        </div>

    @else
        <!-- Message si le caissier n'est pas trouvé -->
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">Caissier non trouvé</h3>
            <p class="mt-2 text-gray-600">Le mobile caissier que vous recherchez n'existe pas ou n'a pas été trouvé.</p>
            <div class="mt-6">
                <a href="{{ route('grande-caisse.dashboard') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-[var(--navbar-bg)] hover:opacity-90 transition-all">
                    ← Retour au tableau de bord
                </a>
            </div>
        </div>
    @endif
</div>

<style>
    .bg-\[var\(--navbar-bg\)\] {
        background-color: #{{ $themeColors['navbar_bg'] ?? '0b5f37' }};
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animation pour les cartes statistiques
        const cards = document.querySelectorAll('.bg-white');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
</script>
@endsection