@extends('layouts.app')

@section('content')
<div class="py-4">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
        <h1 class="text-xl sm:text-2xl font-bold text-[#0b5f37]">📊 Historique des Ventes</h1>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            @if(Auth::user()->isAdmin() || Auth::user()->isGerant())
            <a href="{{ route('ventes.export.form') }}" 
               class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700 text-center flex items-center justify-center gap-2">
                📤 Exporter
            </a>
            @endif
            <a href="{{ route('ventes.index') }}" 
               class="bg-[#0b5f37] text-white px-4 py-2 rounded text-sm hover:bg-[#0a4d2c] text-center flex items-center justify-center gap-2">
                ← Retour aux Ventes
            </a>
        </div>
    </div>

    <!-- Filtres version mobile -->
    <div class="bg-white rounded-lg shadow p-4 mb-4">
        <details class="group">
            <summary class="flex justify-between items-center cursor-pointer list-none">
                <h3 class="text-base font-semibold text-[#0b5f37]">🔍 Filtres</h3>
                <span class="transform group-open:rotate-180 transition-transform">▼</span>
            </summary>
            <div class="mt-3 pt-3 border-t border-gray-200">
                <form action="{{ route('ventes.historique') }}" method="GET">
                    <div class="grid grid-cols-1 gap-3">
                        <!-- Date début -->
                        <div>
                            <label for="date_debut" class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
                            <input type="date" name="date_debut" id="date_debut" 
                                   value="{{ request('date_debut') }}"
                                   class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37]">
                        </div>

                        <!-- Date fin -->
                        <div>
                            <label for="date_fin" class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                            <input type="date" name="date_fin" id="date_fin" 
                                   value="{{ request('date_fin') }}"
                                   class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37]">
                        </div>

                        <!-- Filtre utilisateur -->
                        @if(Auth::user()->isAdmin() || Auth::user()->isGerant())
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">Utilisateur</label>
                            <select name="user_id" id="user_id" 
                                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37]">
                                <option value="">Tous les utilisateurs</option>
                                @foreach($users as $utilisateur)
                                    <option value="{{ $utilisateur->id }}" 
                                            {{ request('user_id') == $utilisateur->id ? 'selected' : '' }}>
                                        {{ $utilisateur->prenom }} {{ $utilisateur->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <!-- Filtre caisse -->
                        <div>
                            <label for="caisse_id" class="block text-sm font-medium text-gray-700 mb-1">Caisse</label>
                            <select name="caisse_id" id="caisse_id" 
                                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37]">
                                <option value="">Toutes les caisses</option>
                                @foreach($caisses as $caisse)
                                    <option value="{{ $caisse->id }}" 
                                            {{ request('caisse_id') == $caisse->id ? 'selected' : '' }}>
                                        {{ $caisse->user->prenom }} - 
                                        {{ $caisse->date_ouverture->format('d/m/Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 mt-4">
                        <a href="{{ route('ventes.historique') }}" 
                           class="bg-gray-500 text-white px-4 py-2 rounded text-sm hover:bg-gray-600 text-center">
                            🔄 Réinitialiser
                        </a>
                        <button type="submit" 
                                class="bg-[#0b5f37] text-white px-6 py-2 rounded hover:bg-[#0a4d2c] font-semibold text-sm text-center">
                            🔍 Appliquer
                        </button>
                    </div>
                </form>
            </div>
        </details>
    </div>

    <!-- Statistiques version mobile -->
    <div class="grid grid-cols-1 gap-3 mb-4">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
            <div class="flex items-center">
                <div class="bg-blue-100 p-2 rounded-full mr-3">
                    <span class="text-blue-600 text-lg">📈</span>
                </div>
                <div>
                    <p class="text-xs text-blue-600">Total Ventes</p>
                    <p class="text-lg font-bold text-blue-800">{{ $stats['total_ventes'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
            <div class="flex items-center">
                <div class="bg-green-100 p-2 rounded-full mr-3">
                    <span class="text-green-600 text-lg">💰</span>
                </div>
                <div>
                    <p class="text-xs text-green-600">Chiffre d'Affaires</p>
                    <p class="text-lg font-bold text-green-800">{{ number_format($stats['chiffre_affaires'], 0, ',', ' ') }} FCFA</p>
                </div>
            </div>
        </div>

        <div class="bg-purple-50 border border-purple-200 rounded-lg p-3">
            <div class="flex items-center">
                <div class="bg-purple-100 p-2 rounded-full mr-3">
                    <span class="text-purple-600 text-lg">📊</span>
                </div>
                <div>
                    <p class="text-xs text-purple-600">Vente Moyenne</p>
                    <p class="text-lg font-bold text-purple-800">{{ number_format($stats['vente_moyenne'], 0, ',', ' ') }} FCFA</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des ventes version mobile -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-3 border-b">
            <h3 class="text-base font-semibold text-[#0b5f37]">
                📋 Ventes ({{ $ventes->total() }})
            </h3>
        </div>
        
        <div class="overflow-x-auto">
            <!-- Version desktop -->
            <table class="min-w-full divide-y divide-gray-200 hidden sm:table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">N° Vente</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Utilisateur</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($ventes as $vente)
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $vente->numero_vente }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $vente->created_at->format('d/m H:i') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            <div>
                                {{ $vente->user->prenom }}
                                @if($vente->user->fonction !== 'caissier')
                                    <span class="bg-blue-100 text-blue-800 text-xs px-1 py-0.5 rounded ml-1">
                                        {{ substr($vente->user->fonction, 0, 3) }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-[#0b5f37]">
                            {{ number_format($vente->montant_total, 0, ',', ' ') }} F
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('ventes.show', $vente) }}" 
                                   class="text-blue-600 hover:text-blue-900 p-1" title="Voir détails">
                                    👁️
                                </a>
                                <a href="{{ route('ventes.receipt', $vente) }}" 
                                   class="text-green-600 hover:text-green-900 p-1" title="Imprimer" target="_blank">
                                    🖨️
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-sm text-gray-500">
                            Aucune vente trouvée.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Version mobile -->
            <div class="sm:hidden divide-y divide-gray-200">
                @forelse($ventes as $vente)
                <div class="p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h4 class="font-semibold text-gray-900">#{{ $vente->numero_vente }}</h4>
                            <p class="text-sm text-gray-500">{{ $vente->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <span class="font-bold text-[#0b5f37] text-sm">
                            {{ number_format($vente->montant_total, 0, ',', ' ') }} F
                        </span>
                    </div>
                    
                    <div class="mb-2">
                        <span class="text-sm text-gray-600">
                            {{ $vente->user->prenom }}
                            @if($vente->user->fonction !== 'caissier')
                                <span class="bg-blue-100 text-blue-800 text-xs px-1 py-0.5 rounded ml-1">
                                    {{ substr($vente->user->fonction, 0, 3) }}
                                </span>
                            @endif
                        </span>
                    </div>

                    <div class="text-xs text-gray-600 mb-3">
                        @foreach($vente->lignesVente->take(2) as $ligne)
                            <div>{{ $ligne->product->designation }} x{{ $ligne->quantite }}</div>
                        @endforeach
                        @if($vente->lignesVente->count() > 2)
                            <div class="text-gray-500">+{{ $vente->lignesVente->count() - 2 }} autre(s)</div>
                        @endif
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('ventes.show', $vente) }}" 
                           class="text-blue-600 text-sm flex items-center">
                            👁️ Détails
                        </a>
                        <a href="{{ route('ventes.receipt', $vente) }}" 
                           class="text-green-600 text-sm flex items-center" target="_blank">
                            🖨️ Reçu
                        </a>
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-gray-500">
                    Aucune vente trouvée.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Pagination -->
        @if($ventes->hasPages())
        <div class="px-4 py-3 border-t">
            <div class="flex justify-center">
                {{ $ventes->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<style>
/* Pagination responsive */
.pagination {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.25rem;
}

.pagination .page-item .page-link {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

@media (max-width: 640px) {
    .pagination .page-item .page-link {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}
</style>
@endsection