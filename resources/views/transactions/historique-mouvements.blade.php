@extends('layouts.app')

@section('content')
<div class="py-4 sm:py-6">
    <div class="max-w-7xl mx-auto px-2 sm:px-0">
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-[#0b5f37]">📊 Historique des Mouvements</h1>
            <p class="text-gray-600">Consultez l'historique complet des approvisionnements, remboursements, avoirs et dépenses</p>
        </div>

        <!-- Cartes de statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <!-- Total Mouvements -->
            <div class="bg-white rounded-lg shadow border p-4 text-center">
                <div class="text-lg font-semibold text-gray-700 mb-2">Total Mouvements</div>
                <div class="text-2xl font-bold text-blue-600">{{ number_format($statistiques['total_mouvements'], 0, ',', ' ') }}</div>
            </div>

            <!-- Total Montant -->
            <div class="bg-white rounded-lg shadow border p-4 text-center">
                <div class="text-lg font-semibold text-gray-700 mb-2">Total Montant</div>
                <div class="text-2xl font-bold text-green-600">{{ number_format($statistiques['total_montant'], 0, ',', ' ') }} F</div>
            </div>

            <!-- Période -->
            <div class="bg-white rounded-lg shadow border p-4 text-center">
                <div class="text-lg font-semibold text-gray-700 mb-2">Période</div>
                <div class="text-lg font-bold text-purple-600">{{ $statistiques['periode'] }}</div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow border p-4 text-center">
                <div class="text-lg font-semibold text-gray-700 mb-2">Actions</div>
                <a href="{{ route('mobile-money.gestion') }}" class="inline-flex items-center px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">
                    📦 Gestion
                </a>
            </div>
        </div>

        <!-- Filtres -->
        <div class="bg-white rounded-lg shadow border p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">🔍 Filtres</h3>
            <form action="{{ route('mobile-money.historique-mouvements') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Type de mouvement -->
                <div>
                    <label for="type_mouvement" class="block text-sm font-medium text-gray-700 mb-1">Type de mouvement</label>
                    <select name="type_mouvement" id="type_mouvement" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Tous les types</option>
                        <option value="approvisionnement" {{ request('type_mouvement') == 'approvisionnement' ? 'selected' : '' }}>Approvisionnement</option>
                        <option value="remboursement" {{ request('type_mouvement') == 'remboursement' ? 'selected' : '' }}>Remboursement</option>
                        <option value="avoir" {{ request('type_mouvement') == 'avoir' ? 'selected' : '' }}>Avoir</option>
                        <option value="depense" {{ request('type_mouvement') == 'depense' ? 'selected' : '' }}>Dépense</option>
                    </select>
                </div>

                <!-- Opérateur -->
                <div>
                    <label for="operateur" class="block text-sm font-medium text-gray-700 mb-1">Opérateur</label>
                    <select name="operateur" id="operateur" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Tous les opérateurs</option>
                        <option value="orange_money" {{ request('operateur') == 'orange_money' ? 'selected' : '' }}>Orange Money</option>
                        <option value="telecel_money" {{ request('operateur') == 'telecel_money' ? 'selected' : '' }}>Telecel Money</option>
                        <option value="moov_money" {{ request('operateur') == 'moov_money' ? 'selected' : '' }}>Moov Money</option>
                        <option value="coris_money" {{ request('operateur') == 'coris_money' ? 'selected' : '' }}>Coris Money</option>
                    </select>
                </div>

                <!-- Date début -->
                <div>
                    <label for="date_debut" class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
                    <input type="date" name="date_debut" id="date_debut" value="{{ request('date_debut') }}"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Date fin -->
                <div>
                    <label for="date_fin" class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                    <input type="date" name="date_fin" id="date_fin" value="{{ request('date_fin') }}"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Boutons -->
                <div class="md:col-span-4 flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-semibold">
                        🔍 Appliquer les filtres
                    </button>
                    <a href="{{ route('mobile-money.historique-mouvements') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 font-semibold">
                        🗑️ Effacer
                    </a>
                </div>
            </form>
        </div>

        <!-- Statistiques détaillées -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Par type de mouvement -->
            <div class="bg-white rounded-lg shadow border p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">📈 Répartition par type</h3>
                <div class="space-y-3">
                    @php
                        $types = [
                            'approvisionnement' => ['label' => 'Approvisionnements', 'color' => 'green'],
                            'remboursement' => ['label' => 'Remboursements', 'color' => 'red'],
                            'avoir' => ['label' => 'Avoirs', 'color' => 'blue'],
                            'depense' => ['label' => 'Dépenses', 'color' => 'orange']
                        ];
                    @endphp
                    @foreach($types as $type => $data)
                        @php
                            $stats = $statistiques['par_type'][$type] ?? null;
                            $count = $stats->count ?? 0;
                            $total = $stats->total ?? 0;
                            $pourcentage = $statistiques['total_montant'] > 0 ? ($total / $statistiques['total_montant']) * 100 : 0;
                        @endphp
                        @if($count > 0)
                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-{{ $data['color'] }}-500 rounded-full mr-2"></span>
                                <span class="font-medium">{{ $data['label'] }}</span>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold">{{ number_format($total, 0, ',', ' ') }} F</div>
                                <div class="text-sm text-gray-500">{{ $count }} mouvements ({{ number_format($pourcentage, 1) }}%)</div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Par opérateur -->
            <div class="bg-white rounded-lg shadow border p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">🏦 Répartition par opérateur</h3>
                <div class="space-y-3">
                    @php
                        $operateurs = [
                            'orange_money' => 'Orange Money',
                            'telecel_money' => 'Telecel Money',
                            'moov_money' => 'Moov Money',
                            'coris_money' => 'Coris Money'
                        ];
                    @endphp
                    @foreach($operateurs as $code => $nom)
                        @php
                            $stats = $statistiques['par_operateur'][$code] ?? null;
                            $count = $stats->count ?? 0;
                            $total = $stats->total ?? 0;
                            $pourcentage = $statistiques['total_montant'] > 0 ? ($total / $statistiques['total_montant']) * 100 : 0;
                        @endphp
                        @if($count > 0)
                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                            <div class="font-medium">{{ $nom }}</div>
                            <div class="text-right">
                                <div class="font-semibold">{{ number_format($total, 0, ',', ' ') }} F</div>
                                <div class="text-sm text-gray-500">{{ $count }} mouvements ({{ number_format($pourcentage, 1) }}%)</div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Tableau des mouvements -->
        <div class="bg-white rounded-lg shadow border">
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">📋 Liste des Mouvements</h3>
                <span class="text-sm text-gray-500">{{ $mouvements->total() }} mouvements trouvés</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date/Heure</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Opérateur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Référence</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateur</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($mouvements as $mouvement)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div>{{ $mouvement->created_at->format('d/m/Y') }}</div>
                                <div class="text-gray-500">{{ $mouvement->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $mouvement->type_mouvement == 'approvisionnement' ? 'bg-green-100 text-green-800' : 
                                       ($mouvement->type_mouvement == 'remboursement' ? 'bg-red-100 text-red-800' :
                                       ($mouvement->type_mouvement == 'avoir' ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800')) }}">
                                    {{ $mouvement->type_mouvement_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $mouvement->operateur_nom }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold 
                                {{ $mouvement->est_positif ? 'text-green-600' : 'text-red-600' }}">
                                {{ $mouvement->signe_montant }}{{ $mouvement->montant_formate }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">
                                {{ $mouvement->reference }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $mouvement->notes ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $mouvement->user->name ?? 'N/A' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                Aucun mouvement trouvé pour les critères sélectionnés
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">
                {{ $mouvements->links() }}
            </div>
        </div>
    </div>
</div>
@endsection