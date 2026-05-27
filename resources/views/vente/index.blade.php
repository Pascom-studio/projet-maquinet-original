@extends('layouts.app')

@section('content')
<div class="py-4">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
        <h1 class="text-xl sm:text-2xl font-bold text-[#0b5f37]">Gestion des Ventes</h1>
        @php
            $caisse_ouverte = \App\Models\Caisse::where('user_id', Auth::id())->where('statut', 'ouverte')->exists();
        @endphp
        <a href="{{ route('ventes.create') }}" 
           class="bg-[#0b5f37] text-white px-3 py-2 rounded text-sm hover:bg-[#0a4d2c] w-full sm:w-auto text-center {{ !$caisse_ouverte && Auth::user()->isCaissier() ? 'opacity-50 cursor-not-allowed' : '' }}"
           @if(!$caisse_ouverte && Auth::user()->isCaissier()) onclick="event.preventDefault(); alert('Veuillez ouvrir votre caisse d\\'abord');" @endif>
            ➕ Nouvelle Vente
        </a>
    </div>

    <!-- Informations -->
    @if(Auth::user()->isCaissier())
    <div class="bg-blue-50 border border-blue-200 rounded p-3 mb-4 text-sm">
        <p class="text-blue-700">📊 <strong>Vos ventes personnelles</strong></p>
    </div>
    @else
    <div class="bg-purple-50 border border-purple-200 rounded p-3 mb-4 text-sm">
        <p class="text-purple-700">👁️ <strong>Toutes les ventes</strong> des utilisateurs</p>
    </div>
    @endif

    <!-- Filtrage mobile -->
    <div class="bg-white rounded shadow p-4 mb-4">
        <details class="group">
            <summary class="flex justify-between items-center cursor-pointer list-none">
                <span class="text-sm font-medium text-gray-700">🔍 Filtres</span>
                <span class="transform group-open:rotate-180 transition-transform text-sm">▼</span>
            </summary>
            <div class="mt-3 pt-3 border-t border-gray-200">
                <form method="GET" action="{{ route('ventes.index') }}" class="grid grid-cols-1 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Date début</label>
                        <input type="date" name="date_debut" value="{{ request('date_debut') }}"
                               class="w-full border border-gray-300 rounded px-2 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37]">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Date fin</label>
                        <input type="date" name="date_fin" value="{{ request('date_fin') }}"
                               class="w-full border border-gray-300 rounded px-2 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37]">
                    </div>
                    
                    @if(Auth::user()->isGerant() || Auth::user()->isAdmin())
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Caissier</label>
                        <select name="user_id" class="w-full border border-gray-300 rounded px-2 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37]">
                            <option value="">Tous</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->prenom }} {{ $user->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    
                    <div class="flex space-x-2">
                        <button type="submit" class="bg-[#0b5f37] text-white px-3 py-2 rounded text-sm hover:bg-[#0a4d2c] flex-1">
                            🔍 Appliquer
                        </button>
                        <a href="{{ route('ventes.index') }}" class="bg-gray-500 text-white px-3 py-2 rounded text-sm hover:bg-gray-600 flex items-center justify-center">
                            🔄
                        </a>
                    </div>
                </form>
            </div>
        </details>
    </div>

    <!-- Tableau compact -->
    <div class="bg-white rounded shadow overflow-hidden">
        <!-- Version desktop -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">N° Vente</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Produits</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qté</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Vendeur</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($ventes as $vente)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 whitespace-nowrap font-medium text-gray-900">
                            #{{ $vente->numero_vente }}
                        </td>
                        <td class="px-4 py-2">
                            <div class="max-w-xs">
                                @foreach($vente->lignesVente->take(2) as $ligne)
                                    <div class="flex justify-between text-xs">
                                        <span class="truncate">{{ Str::limit($ligne->product->designation, 20) }}</span>
                                        <span class="text-gray-500 ml-1">x{{ $ligne->quantite }}</span>
                                    </div>
                                @endforeach
                                @if($vente->lignesVente->count() > 2)
                                    <div class="text-gray-500 text-xs mt-0.5">
                                        +{{ $vente->lignesVente->count() - 2 }} autre(s)
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-gray-900">
                            {{ $vente->lignesVente->sum('quantite') }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap font-bold text-[#0b5f37]">
                            {{ number_format($vente->montant_total, 0, ',', ' ') }} F
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <div class="flex items-center">
                                <span class="text-xs">{{ $vente->user->prenom }}</span>
                                @if($vente->user_id === Auth::id())
                                    <span class="bg-green-100 text-green-800 text-xs px-1 py-0.5 rounded ml-1">Vous</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <div class="flex items-center space-x-1">
                                <a href="{{ route('ventes.show', $vente->id) }}" 
                                   class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50 text-xs"
                                   title="Voir détails">
                                    👁️
                                </a>
                                
                                <!-- Boutons Modifier et Supprimer conditionnels -->
                                @if(Auth::user()->isAdmin() || Auth::user()->isGerant() || $vente->user_id === Auth::id())
                                <a href="{{ route('ventes.edit', $vente->id) }}" 
                                   class="text-yellow-600 hover:text-yellow-900 p-1 rounded hover:bg-yellow-50 text-xs"
                                   title="Modifier">
                                    ✏️
                                </a>
                                
                                <form action="{{ route('ventes.destroy', $vente->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50 text-xs"
                                            title="Supprimer"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette vente ?')">
                                        🗑️
                                    </button>
                                </form>
                                @endif
                                
                                <a href="{{ route('ventes.receipt', $vente->id) }}" target="_blank" 
                                   class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50 text-xs"
                                   title="Imprimer">
                                    🖨️
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-sm font-medium text-gray-600">Aucune vente trouvée</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Version mobile -->
        <div class="sm:hidden divide-y divide-gray-200">
            @forelse($ventes as $vente)
            <div class="p-4">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h4 class="font-semibold text-gray-900">#{{ $vente->numero_vente }}</h4>
                        <p class="text-xs text-gray-500">{{ $vente->created_at->format('d/m H:i') }}</p>
                    </div>
                    <span class="font-bold text-[#0b5f37] text-sm">
                        {{ number_format($vente->montant_total, 0, ',', ' ') }} F
                    </span>
                </div>
                
                <div class="mb-2">
                    <span class="text-sm text-gray-600">
                        {{ $vente->user->prenom }}
                        @if($vente->user_id === Auth::id())
                            <span class="bg-green-100 text-green-800 text-xs px-1 py-0.5 rounded ml-1">Vous</span>
                        @endif
                    </span>
                </div>

                <div class="text-xs text-gray-600 mb-3">
                    <div class="font-medium mb-1">Produits :</div>
                    @foreach($vente->lignesVente->take(2) as $ligne)
                        <div>• {{ Str::limit($ligne->product->designation, 25) }} x{{ $ligne->quantite }}</div>
                    @endforeach
                    @if($vente->lignesVente->count() > 2)
                        <div class="text-gray-500 mt-1">+{{ $vente->lignesVente->count() - 2 }} autre(s)</div>
                    @endif
                    <div class="mt-1 font-medium">Total articles : {{ $vente->lignesVente->sum('quantite') }}</div>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('ventes.show', $vente->id) }}" 
                       class="text-blue-600 text-xs flex items-center">
                        👁️ Détails
                    </a>
                    
                    <!-- Boutons Modifier et Supprimer conditionnels pour mobile -->
                    @if(Auth::user()->isAdmin() || Auth::user()->isGerant() || $vente->user_id === Auth::id())
                    <a href="{{ route('ventes.edit', $vente->id) }}" 
                       class="text-yellow-600 text-xs flex items-center">
                        ✏️ Modifier
                    </a>
                    
                    <form action="{{ route('ventes.destroy', $vente->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="text-red-600 text-xs flex items-center"
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette vente ?')">
                            🗑️ Supprimer
                        </button>
                    </form>
                    @endif
                    
                    <a href="{{ route('ventes.receipt', $vente->id) }}" target="_blank"
                       class="text-green-600 text-xs flex items-center">
                        🖨️ Reçu
                    </a>
                </div>
            </div>
            @empty
            <div class="p-6 text-center text-gray-500">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-sm font-medium text-gray-600">Aucune vente trouvée</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Statistiques compactes -->
    @if($ventes->count() > 0)
    <div class="mt-4 grid grid-cols-2 gap-3">
        <div class="bg-[#8c52ff] text-white p-3 rounded shadow">
            <div class="flex items-center">
                <div class="text-lg mr-2">💰</div>
                <div>
                    <h3 class="text-xs font-semibold opacity-90">Total</h3>
                    <p class="text-sm font-bold">{{ number_format($ventes->sum('montant_total'), 0, ',', ' ') }} F</p>
                </div>
            </div>
        </div>
        
        <div class="bg-[#ee8f13] text-white p-3 rounded shadow">
            <div class="flex items-center">
                <div class="text-lg mr-2">📋</div>
                <div>
                    <h3 class="text-xs font-semibold opacity-90">Ventes</h3>
                    <p class="text-sm font-bold">{{ $ventes->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-[#0b5f37] text-white p-3 rounded shadow">
            <div class="flex items-center">
                <div class="text-lg mr-2">🛒</div>
                <div>
                    <h3 class="text-xs font-semibold opacity-90">Quantité</h3>
                    <p class="text-sm font-bold">
                        {{ $ventes->sum(function($vente) { return $vente->lignesVente->sum('quantite'); }) }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-[#cb6ce6] text-white p-3 rounded shadow">
            <div class="flex items-center">
                <div class="text-lg mr-2">📊</div>
                <div>
                    <h3 class="text-xs font-semibold opacity-90">Moyenne</h3>
                    <p class="text-sm font-bold">
                        {{ $ventes->count() > 0 ? number_format($ventes->sum('montant_total') / $ventes->count(), 0, ',', ' ') : 0 }} F
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions d'export -->
    <div class="mt-3 flex justify-end">
        <button onclick="window.print()" class="bg-gray-600 text-white px-3 py-2 rounded text-sm hover:bg-gray-700 w-full sm:w-auto text-center">
            🖨️ Imprimer le rapport
        </button>
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateDebut = document.querySelector('input[name="date_debut"]');
    const dateFin = document.querySelector('input[name="date_fin"]');
    
    if (dateDebut && dateFin) {
        dateDebut.addEventListener('change', function() {
            dateFin.min = this.value;
        });
        
        dateFin.addEventListener('change', function() {
            if (dateDebut.value && this.value < dateDebut.value) {
                this.value = dateDebut.value;
            }
        });
    }
});
</script>
@endsection