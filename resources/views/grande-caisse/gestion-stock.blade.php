@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <!-- En-tête de la page -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Gestion du Stock</h1>
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

    <!-- Soldes par opérateur -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        @foreach($soldes as $operateur => $data)
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
                    
                    <!-- Détails -->
                    <div class="mt-4 text-xs text-gray-500 space-y-1">
                        <div class="flex justify-between">
                            <span>Retraits:</span>
                            <span class="font-medium">{{ number_format($data['retraits'], 0, ',', ' ') }} F</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Dépôts:</span>
                            <span class="font-medium">{{ number_format($data['depots'], 0, ',', ' ') }} F</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Approvisionnements:</span>
                            <span class="font-medium text-green-600">+{{ number_format($data['approvisionnements'], 0, ',', ' ') }} F</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Remboursements:</span>
                            <span class="font-medium text-red-600">-{{ number_format($data['remboursements'], 0, ',', ' ') }} F</span>
                        </div>
                        @if($data['depenses'] > 0)
                        <div class="flex justify-between">
                            <span>Dépenses:</span>
                            <span class="font-medium text-red-600">-{{ number_format($data['depenses'], 0, ',', ' ') }} F</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Liquidité et solde total -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">💧 Liquidité</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ number_format($liquidite, 0, ',', ' ') }} FCFA</p>
                    <p class="text-sm text-gray-500 mt-1">Fonds disponibles en caisse</p>
                </div>
                <div class="text-3xl">💰</div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">🏦 Patrimoine Total</h3>
                    <p class="text-3xl font-bold text-purple-600">{{ number_format($solde_total, 0, ',', ' ') }} FCFA</p>
                    <p class="text-sm text-gray-500 mt-1">Solde opérateurs + liquidité</p>
                </div>
                <div class="text-3xl">📊</div>
            </div>
        </div>
    </div>

    <!-- Mouvements de stock récents -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">📦 Historique des Mouvements de Stock</h3>
            <p class="text-gray-600 text-sm">{{ $mouvements->total() }} mouvements trouvés</p>
        </div>

        @if($mouvements->count() > 0)
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
                                Référence
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Montant
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Notes
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($mouvements as $mouvement)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div>{{ $mouvement->created_at->format('d/m/Y') }}</div>
                                    <div class="text-gray-500">{{ $mouvement->created_at->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $mouvement->reference }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold 
                                    {{ $mouvement->type_mouvement === 'approvisionnement' || $mouvement->type_mouvement === 'avoir' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $mouvement->type_mouvement === 'approvisionnement' || $mouvement->type_mouvement === 'avoir' ? '+' : '-' }}{{ number_format($mouvement->montant, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $mouvement->notes ? Str::limit($mouvement->notes, 30) : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $mouvements->links() }}
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

<style>
    .bg-\[var\(--navbar-bg\)\] {
        background-color: #{{ $themeColors['navbar_bg'] ?? '0b5f37' }};
    }
</style>
@endsection