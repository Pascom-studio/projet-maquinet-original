@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-[#0b5f37]">Audit Financier</h1>
        <div class="text-sm text-gray-600">
            Transactions de caisse - Admin seulement
        </div>
    </div>

    <!-- Lien de retour -->
    <div class="mb-4">
        <a href="{{ route('audit.index') }}" class="text-[#0b5f37] hover:text-[#0a4d2c] transition duration-150">
            ← Retour à l'audit général
        </a>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-[#0b5f37] mb-4">🔍 Filtres des Transactions</h3>
        <form method="GET" action="{{ route('audit.financier') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="date_debut" class="block text-sm font-medium text-gray-700">Date début</label>
                <input type="date" name="date_debut" id="date_debut" 
                       value="{{ request('date_debut') }}"
                       class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37]">
            </div>
            <div>
                <label for="date_fin" class="block text-sm font-medium text-gray-700">Date fin</label>
                <input type="date" name="date_fin" id="date_fin" 
                       value="{{ request('date_fin') }}"
                       class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37]">
            </div>
            
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                <select name="type" id="type" 
                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37]">
                    <option value="">Tous les types</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                            {{ ucfirst($type) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="user_id" class="block text-sm font-medium text-gray-700">Utilisateur</label>
                <select name="user_id" id="user_id" 
                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37]">
                    <option value="">Tous les utilisateurs</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->prenom }} {{ $user->nom }} ({{ $user->fonction }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="md:col-span-4 flex space-x-2">
                <button type="submit" class="bg-[#0b5f37] text-white px-4 py-2 rounded hover:bg-[#0a4d2c]">
                    Appliquer les filtres
                </button>
                <a href="{{ route('audit.financier') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Réinitialiser
                </a>
            </div>
        </form>
    </div>

    <!-- Statistiques financières -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="bg-red-100 p-3 rounded-lg mr-4">
                    <span class="text-red-600 text-2xl">📝</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-red-800">Dépenses</p>
                    <p class="text-2xl font-bold text-red-600">
                        {{ number_format($stats['total_depenses'], 0, ',', ' ') }} F
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="bg-green-100 p-3 rounded-lg mr-4">
                    <span class="text-green-600 text-2xl">💰</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-green-800">Approvisionnements</p>
                    <p class="text-2xl font-bold text-green-600">
                        {{ number_format($stats['total_approvisionnements'], 0, ',', ' ') }} F
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="bg-blue-100 p-3 rounded-lg mr-4">
                    <span class="text-blue-600 text-2xl">💸</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-blue-800">Retraits</p>
                    <p class="text-2xl font-bold text-blue-600">
                        {{ number_format($stats['total_retraits'], 0, ',', ' ') }} F
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="bg-purple-100 p-3 rounded-lg mr-4">
                    <span class="text-purple-600 text-2xl">📊</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-purple-800">Transactions</p>
                    <p class="text-2xl font-bold text-purple-600">
                        {{ $stats['total_transactions'] }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="bg-orange-100 p-3 rounded-lg mr-4">
                    <span class="text-orange-600 text-2xl">⚖️</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-orange-800">Solde Net</p>
                    <p class="text-2xl font-bold text-orange-600">
                        {{ number_format($stats['solde_net'], 0, ',', ' ') }} F
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des transactions -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">Historique des Transactions</h3>
                <span class="text-sm text-gray-500">
                    Page {{ $transactions->currentPage() }} sur {{ $transactions->lastPage() }}
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date/Heure</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Caisse</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transactions as $transaction)
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="font-medium">{{ $transaction->created_at->format('d/m/Y') }}</div>
                            <div class="text-xs text-gray-400">{{ $transaction->created_at->format('H:i:s') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $transaction->user->prenom }} {{ $transaction->user->nom }}
                            </div>
                            <div class="text-xs text-gray-500">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $transaction->user->fonction }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $typeColors = [
                                    'depense' => 'bg-red-100 text-red-800 border border-red-200',
                                    'approvisionnement' => 'bg-green-100 text-green-800 border border-green-200',
                                    'retrait' => 'bg-blue-100 text-blue-800 border border-blue-200'
                                ];
                                $color = $typeColors[$transaction->type] ?? 'bg-gray-100 text-gray-800 border border-gray-200';
                            @endphp
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                                {{ ucfirst($transaction->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold 
                            {{ $transaction->type === 'depense' || $transaction->type === 'retrait' ? 'text-red-600' : 'text-green-600' }}">
                            {{ number_format($transaction->montant, 0, ',', ' ') }} F
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="max-w-xs truncate" title="{{ $transaction->description }}">
                                {{ $transaction->description }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($transaction->caisse)
                                {{ $transaction->caisse->user->prenom }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center">
                            <div class="text-gray-400 mb-2">
                                <span class="text-4xl">💸</span>
                            </div>
                            <p class="text-gray-500 text-lg font-medium">Aucune transaction trouvée</p>
                            <p class="text-gray-400 text-sm mt-1">
                                @if(request()->anyFilled(['date_debut', 'date_fin', 'type', 'user_id']))
                                    Essayez de modifier vos critères de recherche
                                @else
                                    Les transactions apparaîtront ici au fur et à mesure des activités
                                @endif
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($transactions->hasPages())
        <div class="bg-white px-6 py-4 border-t">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="text-sm text-gray-700 mb-4 md:mb-0">
                    Affichage de <span class="font-medium">{{ $transactions->firstItem() }}</span> 
                    à <span class="font-medium">{{ $transactions->lastItem() }}</span> 
                    sur <span class="font-medium">{{ $transactions->total() }}</span> résultats
                </div>
                <div class="flex space-x-2">
                    {{ $transactions->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Actions rapides -->
    <div class="mt-6 flex flex-wrap gap-4 justify-center">
        <a href="{{ route('audit.financier', array_merge(request()->all(), ['type' => 'depense'])) }}" 
           class="bg-red-100 text-red-700 px-4 py-2 rounded-lg hover:bg-red-200 transition duration-150">
            📝 Voir seulement les dépenses
        </a>
        <a href="{{ route('audit.financier', array_merge(request()->all(), ['type' => 'approvisionnement'])) }}" 
           class="bg-green-100 text-green-700 px-4 py-2 rounded-lg hover:bg-green-200 transition duration-150">
            💰 Voir seulement les approvisionnements
        </a>
        <a href="{{ route('audit.financier', array_merge(request()->all(), ['type' => 'retrait'])) }}" 
           class="bg-blue-100 text-blue-700 px-4 py-2 rounded-lg hover:bg-blue-200 transition duration-150">
            💸 Voir seulement les retraits
        </a>
    </div>
</div>

<style>
    /* Styles pour améliorer l'affichage mobile */
    @media (max-width: 768px) {
        .max-w-xs {
            max-width: 200px;
        }
    }
</style>
@endsection