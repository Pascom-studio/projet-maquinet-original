@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-[#0b5f37]">Journal d'Audit</h1>
        <div class="text-sm text-gray-600">
            Traçabilité complète du système
        </div>
    </div>

    <!-- Filtrage -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-[#0b5f37] mb-4">🔍 Filtres d'Audit</h3>
        <form method="GET" action="{{ route('audit.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
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
                <label for="action" class="block text-sm font-medium text-gray-700">Action</label>
                <select name="action" id="action" 
                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37]">
                    <option value="">Toutes les actions</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                            {{ ucfirst($action) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="table_name" class="block text-sm font-medium text-gray-700">Table</label>
                <select name="table_name" id="table_name" 
                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37]">
                    <option value="">Toutes les tables</option>
                    @foreach($tables as $table)
                        <option value="{{ $table }}" {{ request('table_name') == $table ? 'selected' : '' }}>
                            {{ $table }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-end space-x-2">
                <button type="submit" class="bg-[#0b5f37] text-white px-4 py-2 rounded hover:bg-[#0a4d2c] w-full">
                    Filtrer
                </button>
                <a href="{{ route('audit.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Statistiques d'audit -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        @php
            // Base query avec scope visibleTo et filtres appliqués
            $baseQuery = \App\Models\Audit::visibleTo(Auth::user());
            
            if (request('date_debut')) {
                $baseQuery->whereDate('created_at', '>=', request('date_debut'));
            }
            if (request('date_fin')) {
                $baseQuery->whereDate('created_at', '<=', request('date_fin'));
            }
            if (request('action')) {
                $baseQuery->where('action', request('action'));
            }
            if (request('table_name')) {
                $baseQuery->where('table_name', request('table_name'));
            }
        @endphp

        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="bg-green-100 p-3 rounded-lg mr-4">
                    <span class="text-green-600 text-2xl">➕</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-green-800">Créations</p>
                    <p class="text-2xl font-bold text-green-600">
                        {{ (clone $baseQuery)->where('action', 'created')->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="bg-blue-100 p-3 rounded-lg mr-4">
                    <span class="text-blue-600 text-2xl">✏️</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-blue-800">Modifications</p>
                    <p class="text-2xl font-bold text-blue-600">
                        {{ (clone $baseQuery)->where('action', 'updated')->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="bg-red-100 p-3 rounded-lg mr-4">
                    <span class="text-red-600 text-2xl">❌</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-red-800">Suppressions</p>
                    <p class="text-2xl font-bold text-red-600">
                        {{ (clone $baseQuery)->where('action', 'deleted')->count() }}
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
                    <p class="text-sm font-medium text-purple-800">Total</p>
                    <p class="text-2xl font-bold text-purple-600">
                        {{ $baseQuery->count() }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations sur le scope -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <div class="mr-3">
                <span class="text-blue-600 text-xl">ℹ️</span>
            </div>
            <div>
                <p class="text-sm font-medium text-blue-800">
                    @if(Auth::user()->isSuperAdmin())
                        Affichage de tous les audits du système
                    @elseif(Auth::user()->isAdmin())
                        Affichage des audits de votre organisation (vous et vos utilisateurs)
                    @elseif(Auth::user()->isGerant())
                        Affichage des audits de votre établissement
                    @endif
                </p>
                <p class="text-xs text-blue-600 mt-1">
                    {{ $audits->total() }} résultat(s) trouvé(s)
                </p>
            </div>
        </div>
    </div>

    <!-- Tableau des audits -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">Historique des Audits</h3>
                <span class="text-sm text-gray-500">
                    Page {{ $audits->currentPage() }} sur {{ $audits->lastPage() }}
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date/Heure</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Table</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($audits as $audit)
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="font-medium">{{ $audit->created_at->format('d/m/Y') }}</div>
                            <div class="text-xs text-gray-400">{{ $audit->created_at->format('H:i:s') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $audit->user->prenom }} {{ $audit->user->nom }}
                            </div>
                            <div class="text-xs text-gray-500">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $audit->user->fonction }}
                                </span>
                            </div>
                            @if($audit->user->admin_id && Auth::user()->isSuperAdmin())
                                <div class="text-xs text-gray-400 mt-1">
                                    Admin: {{ $audit->user->admin->prenom ?? 'N/A' }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $actionColors = [
                                    'created' => 'bg-green-100 text-green-800 border border-green-200',
                                    'updated' => 'bg-blue-100 text-blue-800 border border-blue-200',
                                    'deleted' => 'bg-red-100 text-red-800 border border-red-200',
                                    'restored' => 'bg-yellow-100 text-yellow-800 border border-yellow-200'
                                ];
                                $color = $actionColors[$audit->action] ?? 'bg-gray-100 text-gray-800 border border-gray-200';
                            @endphp
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                                {{ $audit->action_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">
                            {{ $audit->table_name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="max-w-xs truncate" title="{{ $audit->description }}">
                                {{ $audit->description }}
                            </div>
                            @if($audit->record_id)
                                <div class="text-xs text-gray-500 mt-1">
                                    ID: <span class="font-mono bg-gray-100 px-1 rounded">{{ $audit->record_id }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('audit.show', $audit->id) }}" 
                                   class="text-[#0b5f37] hover:text-[#0a4d2c] transition duration-150"
                                   title="Voir les détails complets">
                                    <div class="flex items-center space-x-1">
                                        <span>👁️</span>
                                        <span class="hidden md:inline">Détails</span>
                                    </div>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center">
                            <div class="text-gray-400 mb-2">
                                <span class="text-4xl">📊</span>
                            </div>
                            <p class="text-gray-500 text-lg font-medium">Aucun enregistrement d'audit trouvé</p>
                            <p class="text-gray-400 text-sm mt-1">
                                @if(request()->anyFilled(['date_debut', 'date_fin', 'action', 'table_name']))
                                    Essayez de modifier vos critères de recherche
                                @else
                                    Les audits apparaîtront ici au fur et à mesure des activités
                                @endif
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- En-tête du tableau pour mobile -->
        <div class="px-6 py-3 bg-gray-50 border-t md:hidden">
            <div class="text-sm text-gray-500 text-center">
                {{ $audits->count() }} audit(s) sur cette page
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($audits->hasPages())
    <div class="mt-6 bg-white rounded-lg shadow px-6 py-4">
        <div class="flex flex-col md:flex-row items-center justify-between">
            <div class="text-sm text-gray-700 mb-4 md:mb-0">
                Affichage de <span class="font-medium">{{ $audits->firstItem() }}</span> 
                à <span class="font-medium">{{ $audits->lastItem() }}</span> 
                sur <span class="font-medium">{{ $audits->total() }}</span> résultats
            </div>
            <div class="flex space-x-2">
                {{ $audits->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
    @endif

    <!-- Actions rapides -->
    <div class="mt-6 flex flex-wrap gap-4 justify-center">
        <a href="{{ route('audit.index', array_merge(request()->all(), ['action' => 'created'])) }}" 
           class="bg-green-100 text-green-700 px-4 py-2 rounded-lg hover:bg-green-200 transition duration-150">
            ➕ Voir seulement les créations
        </a>
        <a href="{{ route('audit.index', array_merge(request()->all(), ['action' => 'updated'])) }}" 
           class="bg-blue-100 text-blue-700 px-4 py-2 rounded-lg hover:bg-blue-200 transition duration-150">
            ✏️ Voir seulement les modifications
        </a>
        <a href="{{ route('audit.index', array_merge(request()->all(), ['action' => 'deleted'])) }}" 
           class="bg-red-100 text-red-700 px-4 py-2 rounded-lg hover:bg-red-200 transition duration-150">
            ❌ Voir seulement les suppressions
        </a>
    </div>

    <!-- NOUVEAU : Lien vers l'audit financier pour l'admin -->
    @if(Auth::user()->isAdmin())
    <div class="mt-8 bg-gradient-to-r from-green-50 to-blue-50 border border-green-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="bg-green-100 p-3 rounded-lg mr-4">
                    <span class="text-green-600 text-2xl">💰</span>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-green-800">Audit Financier Avancé</h3>
                    <p class="text-sm text-green-600 mt-1">
                        Accédez aux transactions détaillées de caisse (dépenses, approvisionnements, retraits)
                    </p>
                </div>
            </div>
            <a href="{{ route('audit.financier') }}" 
               class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition duration-150 font-semibold">
                📊 Voir l'audit financier
            </a>
        </div>
    </div>
    @endif
</div>

<style>
    /* Styles pour améliorer l'affichage mobile */
    @media (max-width: 768px) {
        .max-w-xs {
            max-width: 200px;
        }
    }
    
    /* Animation de hover sur les lignes */
    tr {
        transition: all 0.2s ease-in-out;
    }
</style>
@endsection