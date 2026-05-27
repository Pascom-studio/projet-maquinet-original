@extends('layouts.app')

@section('content')
@php
    $user = Auth::user();
    
    // CORRECTION : Utiliser des méthodes statiques ou des helpers
    $commissionStats = App\Http\Controllers\MobileMoneyController::getCommissionStatsForUserStatic($user);
    
    // Calcul des commissions globales POUR L'UTILISATEUR CONNECTÉ - UNIQUEMENT MOIS EN COURS
    $queryGlobal = App\Models\MobileMoneyTransaction::where('user_id', $user->id)
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year);
    $globalTotalCommissions = $queryGlobal->sum('commission');
    
    // CORRECTION : Commissions par opérateur POUR L'UTILISATEUR CONNECTÉ - UNIQUEMENT MOIS EN COURS
    $commissionOrange = App\Models\MobileMoneyTransaction::where('user_id', $user->id)
        ->where('nature', 'orange_money')
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->sum('commission');
        
    $commissionTelecel = App\Models\MobileMoneyTransaction::where('user_id', $user->id)
        ->where('nature', 'telecel_money')
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->sum('commission');
        
    $commissionMoov = App\Models\MobileMoneyTransaction::where('user_id', $user->id)
        ->where('nature', 'moov_money')
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->sum('commission');
        
    $commissionCoris = App\Models\MobileMoneyTransaction::where('user_id', $user->id)
        ->where('nature', 'coris_money')
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->sum('commission');
    
    // CORRECTION : Transactions par opérateur POUR L'UTILISATEUR CONNECTÉ - UNIQUEMENT MOIS EN COURS
    $transactionsOrange = App\Models\MobileMoneyTransaction::where('user_id', $user->id)
        ->where('nature', 'orange_money')
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->count();
        
    $transactionsTelecel = App\Models\MobileMoneyTransaction::where('user_id', $user->id)
        ->where('nature', 'telecel_money')
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->count();
        
    $transactionsMoov = App\Models\MobileMoneyTransaction::where('user_id', $user->id)
        ->where('nature', 'moov_money')
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->count();
        
    $transactionsCoris = App\Models\MobileMoneyTransaction::where('user_id', $user->id)
        ->where('nature', 'coris_money')
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->count();
    
    // Statistiques temporelles des commissions POUR L'UTILISATEUR CONNECTÉ
    $commissionAujourdhui = App\Models\MobileMoneyTransaction::where('user_id', $user->id)
        ->whereDate('created_at', today())
        ->sum('commission');
        
    $commissionMois = App\Models\MobileMoneyTransaction::where('user_id', $user->id)
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->sum('commission');
@endphp

<div class="py-4 sm:py-6">
    <!-- En-tête -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-purple-600 px-2 sm:px-0">💸 Historique des Commissions</h1>
            <p class="text-gray-600 px-2 sm:px-0">Vos commissions Mobile Money par mois et par opérateur - {{ $user->prenom }}</p>
        </div>
        
        <div class="mt-2 sm:mt-0 px-2 sm:px-0 flex space-x-2">
            <a href="{{ route('mobile-money.historique') }}" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 text-sm flex items-center">
                <span class="mr-2">📊</span>
                Historique Transactions
            </a>
            
            <!-- CORRECTION : Formulaire GET pour l'export des commissions -->
            <form action="{{ route('mobile-money.export-commission') }}" method="GET" class="inline">
                <input type="hidden" name="mois" value="{{ request('mois') }}">
                <input type="hidden" name="annee" value="{{ request('annee') }}">
                <input type="hidden" name="operateur" value="{{ request('operateur') }}">
                <input type="hidden" name="type_operation" value="{{ request('type_operation') }}">
                <input type="hidden" name="format" value="excel">
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 text-sm flex items-center">
                    <span class="mr-2">📄</span>
                    Exporter Commissions
                </button>
            </form>
        </div>
    </div>

    <!-- Cartes Statistiques COMMISSIONS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6 px-2 sm:px-0">
        

        <!-- Commission Aujourd'hui -->
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-gray-700 truncate">Aujourd'hui</h3>
                    <p class="text-2xl sm:text-3xl font-bold text-green-600">{{ number_format($commissionAujourdhui, 0, ',', ' ') }} F</p>
                </div>
                <div class="text-xl sm:text-2xl ml-2">🔄</div>
            </div>
            <p class="text-xs text-gray-500 mt-1">Commission ce jour</p>
        </div>

        <!-- Commission Mois -->
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-gray-700 truncate">Mois {{ now()->format('F') }}</h3>
                    <p class="text-2xl sm:text-3xl font-bold text-blue-600">{{ number_format($commissionMois, 0, ',', ' ') }} F</p>
                </div>
                <div class="text-xl sm:text-2xl ml-2">📅</div>
            </div>
            <p class="text-xs text-gray-500 mt-1">Commission mensuelle</p>
        </div>
        
        <!-- Transactions avec Commission -->
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-gray-700 truncate">Lignes</h3>
                    <p class="text-2xl sm:text-3xl font-bold text-purple-600">{{ number_format($commissionsMensuelles->total(), 0, ',', ' ') }}</p>
                </div>
                <div class="text-xl sm:text-2xl ml-2">📈</div>
            </div>
            <p class="text-xs text-gray-500 mt-1">Lignes de commission</p>
        </div>
    </div>

    <!-- Carte Détail des Commissions par Opérateur -->
    <div class="bg-white rounded-lg shadow p-4 mb-6 mx-2 sm:mx-0 border-l-4 border-purple-500">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">📱 VOS Commissions par Opérateur - {{ now()->format('F Y') }}</h3>
                <p class="text-sm text-gray-600 mt-1">Répartition de VOS commissions nettes par opérateur pour le mois en cours</p>
            </div>
            <div class="text-3xl">💰</div>
        </div>
        
        <!-- Détail des commissions par opérateur -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Orange Money -->
            <div class="bg-orange-50 rounded-lg p-4 border border-orange-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-orange-800 font-semibold">Orange Money</span>
                    <div class="w-8 h-8 rounded-full bg-orange-500 flex items-center justify-center text-white font-bold text-sm">
                        O
                    </div>
                </div>
                <div class="text-2xl font-bold text-orange-700">{{ number_format($commissionOrange, 0, ',', ' ') }} F</div>
                <div class="text-xs text-orange-600 mt-1">
                    {{ $transactionsOrange }} transactions
                </div>
            </div>
            
            <!-- Telecel Money -->
            <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-yellow-800 font-semibold">Telecel Money</span>
                    <div class="w-8 h-8 rounded-full bg-yellow-500 flex items-center justify-center text-white font-bold text-sm">
                        T
                    </div>
                </div>
                <div class="text-2xl font-bold text-yellow-700">{{ number_format($commissionTelecel, 0, ',', ' ') }} F</div>
                <div class="text-xs text-yellow-600 mt-1">
                    {{ $transactionsTelecel }} transactions
                </div>
            </div>
            
            <!-- Moov Money -->
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-blue-800 font-semibold">Moov Money</span>
                    <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-sm">
                        M
                    </div>
                </div>
                <div class="text-2xl font-bold text-blue-700">{{ number_format($commissionMoov, 0, ',', ' ') }} F</div>
                <div class="text-xs text-blue-600 mt-1">
                    {{ $transactionsMoov }} transactions
                </div>
            </div>
            
            <!-- Coris Money -->
            <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-purple-800 font-semibold">Coris Money</span>
                    <div class="w-8 h-8 rounded-full bg-purple-500 flex items-center justify-center text-white font-bold text-sm">
                        C
                    </div>
                </div>
                <div class="text-2xl font-bold text-purple-700">{{ number_format($commissionCoris, 0, ',', ' ') }} F</div>
                <div class="text-xs text-purple-600 mt-1">
                    {{ $transactionsCoris }} transactions
                </div>
            </div>
        </div>
        
        <!-- Note sur le reset mensuel -->
        <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
            <p class="text-sm text-blue-700 text-center">
                <span class="font-semibold">💡 Information :</span> 
                Les commissions sont remises à zéro automatiquement le 1er de chaque mois pour reprendre le cumul du mois suivant.
            </p>
        </div>
    </div>

    <!-- Filtres Spécialisés Commissions -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6 mx-2 sm:mx-0">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">🔍 Filtres Commissions</h3>
        
        <form method="GET" action="{{ route('mobile-money.historique-commission') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Période (Mois/Année) -->
            <div>
                <label for="mois" class="block text-sm font-medium text-gray-700 mb-1">Mois</label>
                <select id="mois" name="mois" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">Tous les mois</option>
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ request('mois') == $i ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            
            <div>
                <label for="annee" class="block text-sm font-medium text-gray-700 mb-1">Année</label>
                <select id="annee" name="annee" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">Toutes les années</option>
                    @for($i = date('Y'); $i >= 2020; $i--)
                        <option value="{{ $i }}" {{ request('annee') == $i ? 'selected' : '' }}>
                            {{ $i }}
                        </option>
                    @endfor
                </select>
            </div>
            
            <!-- Opérateur -->
            <div>
                <label for="operateur" class="block text-sm font-medium text-gray-700 mb-1">Opérateur</label>
                <select id="operateur" name="operateur" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">Tous les opérateurs</option>
                    <option value="orange_money" {{ request('operateur') == 'orange_money' ? 'selected' : '' }}>Orange Money</option>
                    <option value="telecel_money" {{ request('operateur') == 'telecel_money' ? 'selected' : '' }}>Telecel Money</option>
                    <option value="moov_money" {{ request('operateur') == 'moov_money' ? 'selected' : '' }}>Moov Money</option>
                    <option value="coris_money" {{ request('operateur') == 'coris_money' ? 'selected' : '' }}>Coris Money</option>
                </select>
            </div>
            
            <!-- Type d'opération -->
            <div>
                <label for="type_operation" class="block text-sm font-medium text-gray-700 mb-1">Type d'opération</label>
                <select id="type_operation" name="type_operation" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">Tous les types</option>
                    <option value="depot" {{ request('type_operation') == 'depot' ? 'selected' : '' }}>Dépôt</option>
                    <option value="retrait" {{ request('type_operation') == 'retrait' ? 'selected' : '' }}>Retrait</option>
                </select>
            </div>
            
            <!-- Boutons -->
            <div class="sm:col-span-2 lg:col-span-4 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 text-sm flex items-center justify-center">
                    <span class="mr-2">🔍</span>
                    Appliquer les filtres
                </button>
                
                <a href="{{ route('mobile-money.historique-commission') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm flex items-center justify-center">
                    <span class="mr-2">🔄</span>
                    Réinitialiser
                </a>
                
                <!-- CORRECTION : Bouton d'export avec les mêmes filtres -->
                <button type="button" onclick="exportCommissions()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 text-sm flex items-center justify-center">
                    <span class="mr-2">📄</span>
                    Exporter
                </button>
            </div>
        </form>
    </div>

    <!-- Tableau des commissions PAR MOIS ET PAR OPERATEUR -->
    <div class="bg-white rounded-lg shadow mx-2 sm:mx-0">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-lg sm:text-xl font-semibold text-gray-800">📊 VOS Commissions par Mois et par Opérateur</h3>
                <div class="mt-2 sm:mt-0 text-sm text-gray-600">
                    {{ $commissionsMensuelles->total() }} ligne(s) de commission
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Période</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Opérateur</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type d'opération</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre Transactions</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commission Totale</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($commissionsMensuelles as $commission)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $commission->periode)->format('F Y') }}
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center space-x-2">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-white font-bold text-xs
                                        {{ $commission->operateur == 'orange_money' ? 'bg-orange-500' : '' }}
                                        {{ $commission->operateur == 'telecel_money' ? 'bg-yellow-500' : '' }}
                                        {{ $commission->operateur == 'moov_money' ? 'bg-blue-500' : '' }}
                                        {{ $commission->operateur == 'coris_money' ? 'bg-purple-500' : '' }}">
                                        @if($commission->operateur == 'orange_money')
                                            O
                                        @elseif($commission->operateur == 'telecel_money')
                                            T
                                        @elseif($commission->operateur == 'moov_money')
                                            M
                                        @elseif($commission->operateur == 'coris_money')
                                            C
                                        @else
                                            {{ substr($commission->operateur, 0, 1) }}
                                        @endif
                                    </div>
                                    <span class="font-medium">
                                        @if($commission->operateur == 'orange_money')
                                            Orange Money
                                        @elseif($commission->operateur == 'telecel_money')
                                            Telecel Money
                                        @elseif($commission->operateur == 'moov_money')
                                            Moov Money
                                        @elseif($commission->operateur == 'coris_money')
                                            Coris Money
                                        @else
                                            {{ $commission->operateur }}
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($commission->type_operation == 'depot')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <span class="mr-1">⬆️</span> Dépôt
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <span class="mr-1">⬇️</span> Retrait
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    <span class="font-semibold">{{ number_format($commission->nombre_transactions, 0, ',', ' ') }}</span>
                                    <span class="ml-1 text-gray-500">trans.</span>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-bold text-purple-600">
                                {{ number_format($commission->commission_totale, 0, ',', ' ') }} F
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('mobile-money.historique', [
                                    'date_debut' => \Carbon\Carbon::createFromFormat('Y-m', $commission->periode)->startOfMonth()->format('Y-m-d'),
                                    'date_fin' => \Carbon\Carbon::createFromFormat('Y-m', $commission->periode)->endOfMonth()->format('Y-m-d'),
                                    'operateur' => $commission->operateur,
                                    'type_operation' => $commission->type_operation
                                ]) }}" 
                                   class="text-purple-600 hover:text-purple-700 font-medium flex items-center">
                                    <span class="mr-1">👁️</span>
                                    Voir détails
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 sm:px-6 py-4 text-center text-sm text-gray-500">
                                <div class="text-center py-8">
                                    <div class="text-gray-400 text-4xl mb-4">💸</div>
                                    <p class="text-gray-500">Aucune commission trouvée</p>
                                    <p class="text-gray-400 text-sm mt-2">Les commissions par mois et opérateur apparaîtront ici</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($commissionsMensuelles->hasPages())
        <div class="px-4 sm:px-6 py-4 bg-gray-50 border-t border-gray-200">
            {{ $commissionsMensuelles->links() }}
        </div>
        @endif
    </div>
</div>

<script>
// Fonction pour exporter les commissions avec les filtres actuels
function exportCommissions() {
    // Récupérer les valeurs des filtres actuels
    const mois = document.getElementById('mois')?.value || '';
    const annee = document.getElementById('annee')?.value || '';
    const operateur = document.getElementById('operateur')?.value || '';
    const typeOperation = document.getElementById('type_operation')?.value || '';
    
    // Construire l'URL d'export
    const url = new URL('{{ route("mobile-money.export-commission") }}');
    url.searchParams.append('mois', mois);
    url.searchParams.append('annee', annee);
    url.searchParams.append('operateur', operateur);
    url.searchParams.append('type_operation', typeOperation);
    url.searchParams.append('format', 'excel');
    
    // Rediriger vers l'URL d'export
    window.location.href = url.toString();
}

// Fonction pour exporter avec un format spécifique
function exportWithFormat(format) {
    const mois = document.getElementById('mois')?.value || '';
    const annee = document.getElementById('annee')?.value || '';
    const operateur = document.getElementById('operateur')?.value || '';
    const typeOperation = document.getElementById('type_operation')?.value || '';
    
    const url = new URL('{{ route("mobile-money.export-commission") }}');
    url.searchParams.append('mois', mois);
    url.searchParams.append('annee', annee);
    url.searchParams.append('operateur', operateur);
    url.searchParams.append('type_operation', typeOperation);
    url.searchParams.append('format', format);
    
    window.location.href = url.toString();
}
</script>

<style>
/* Styles pour les cartes de commissions */
.bg-orange-50 { background-color: #fff7ed; }
.bg-yellow-50 { background-color: #fefce8; }
.bg-blue-50 { background-color: #eff6ff; }
.bg-purple-50 { background-color: #faf5ff; }

/* Styles responsifs améliorés */
@media (max-width: 640px) {
    .grid-cols-1 {
        grid-template-columns: 1fr;
    }
    
    .text-2xl {
        font-size: 1.5rem;
        line-height: 2rem;
    }
    
    .text-3xl {
        font-size: 1.875rem;
        line-height: 2.25rem;
    }
}
</style>
@endsection