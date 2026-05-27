@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- En-tête -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-[#0b5f37]">Commandes en Cours</h2>
                        <p class="text-gray-600">Gérez les commandes des clients</p>
                    </div>
                    <div class="flex space-x-3">
                        @auth
                            @if (Auth::user()->isCaissier())
                                <a href="{{ route('commandes.create') }}" class="bg-[#0b5f37] text-white px-4 py-2 rounded hover:bg-[#0a4d2c] text-sm">
                                    + Nouvelle Commande
                                </a>
                            @endif
                        @endauth
                        <a href="{{ route('commandes.soldees') }}" class="bg-[#ee8f13] text-white px-4 py-2 rounded hover:bg-[#d67f11] text-sm">
                            Commandes Soldées
                        </a>
                        <!-- BOUTON EXPORT CORRECTEMENT PLACÉ -->
                        @if (Auth::user()->isAdmin() || Auth::user()->isGerant())
                        <a href="{{ route('commandes.export') }}" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 text-sm flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Exporter
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Filtres -->
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <form action="{{ route('commandes.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="statut" class="block text-xs font-medium text-gray-700 mb-1">Statut</label>
                            <select name="statut" id="statut" class="w-full text-sm border-gray-300 rounded-md focus:border-[#0b5f37] focus:ring-[#0b5f37]">
                                <option value="">Tous les statuts</option>
                                <option value="en cours" {{ request('statut') == 'en cours' ? 'selected' : '' }}>En cours</option>
                                <option value="soldée" {{ request('statut') == 'soldée' ? 'selected' : '' }}>Soldée</option>
                                <option value="annulée" {{ request('statut') == 'annulée' ? 'selected' : '' }}>Annulée</option>
                            </select>
                        </div>

                        @if (Auth::user()->isAdmin() || Auth::user()->isGerant())
                        <div>
                            <label for="hotesse_id" class="block text-xs font-medium text-gray-700 mb-1">Hôtesse</label>
                            <select name="hotesse_id" id="hotesse_id" class="w-full text-sm border-gray-300 rounded-md focus:border-[#0b5f37] focus:ring-[#0b5f37]">
                                <option value="">Toutes les hôtesses</option>
                                @foreach ($hotesses as $hotesse)
                                    <option value="{{ $hotesse->id }}" {{ request('hotesse_id') == $hotesse->id ? 'selected' : '' }}>
                                        {{ $hotesse->prenom }} {{ $hotesse->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="user_id" class="block text-xs font-medium text-gray-700 mb-1">Caissier</label>
                            <select name="user_id" id="user_id" class="w-full text-sm border-gray-300 rounded-md focus:border-[#0b5f37] focus:ring-[#0b5f37]">
                                <option value="">Tous les caissiers</option>
                                @foreach ($caissiers as $caissier)
                                    <option value="{{ $caissier->id }}" {{ request('user_id') == $caissier->id ? 'selected' : '' }}>
                                        {{ $caissier->prenom }} {{ $caissier->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label for="date_debut" class="block text-xs font-medium text-gray-700 mb-1">Date début</label>
                                <input type="date" name="date_debut" id="date_debut" class="w-full text-sm border-gray-300 rounded-md focus:border-[#0b5f37] focus:ring-[#0b5f37]" value="{{ request('date_debut') }}">
                            </div>
                            <div>
                                <label for="date_fin" class="block text-xs font-medium text-gray-700 mb-1">Date fin</label>
                                <input type="date" name="date_fin" id="date_fin" class="w-full text-sm border-gray-300 rounded-md focus:border-[#0b5f37] focus:ring-[#0b5f37]" value="{{ request('date_fin') }}">
                            </div>
                        </div>
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="bg-[#0b5f37] text-white px-4 py-2 rounded hover:bg-[#0a4d2c] text-sm">
                                Filtrer
                            </button>
                            <a href="{{ route('commandes.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">
                                Réinitialiser
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Statistiques -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-sm font-semibold text-blue-800">Total Commandes</h3>
                                <p class="text-xl font-bold text-blue-600">{{ $stats['total_commandes'] }}</p>
                            </div>
                            <div class="text-xl">📋</div>
                        </div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-sm font-semibold text-green-800">Chiffre d'Affaires</h3>
                                <p class="text-xl font-bold text-green-600">{{ number_format($stats['chiffre_affaires'], 0, ',', ' ') }} FCFA</p>
                            </div>
                            <div class="text-xl">💰</div>
                        </div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-sm font-semibold text-purple-800">Commande Moyenne</h3>
                                <p class="text-xl font-bold text-purple-600">{{ number_format($stats['commande_moyenne'], 0, ',', ' ') }} FCFA</p>
                            </div>
                            <div class="text-xl">📊</div>
                        </div>
                    </div>
                </div>

                <!-- Liste des commandes -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Commande</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Table</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hôtesse</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produits</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Caissier</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($commandes as $commande)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $commande->numero_commande }}</div>
                                        @if ($commande->notes)
                                            <div class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit($commande->notes, 30) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if ($commande->table)
                                            {{ $commande->table->nom_complet ?? $commande->table->nom }}
                                        @else
                                            <span class="text-red-500">Table supprimée</span>
                                        @endif
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if ($commande->hotesse)
                                            {{ $commande->hotesse->prenom }} {{ $commande->hotesse->nom }}
                                        @else
                                            <span class="text-red-500">Hôtesse supprimée</span>
                                        @endif
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $commande->produits->count() }} produit(s)
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ number_format($commande->montant, 0, ',', ' ') }} FCFA
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $commande->statut === 'en cours' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $commande->statut === 'soldée' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $commande->statut === 'annulée' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ ucfirst($commande->statut) }}
                                        </span>
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if ($commande->user)
                                            {{ $commande->user->prenom }} {{ $commande->user->nom }}
                                        @else
                                            <span class="text-red-500">Caissier supprimé</span>
                                        @endif
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $commande->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('commandes.show', $commande) }}" class="text-blue-600 hover:text-blue-900 transition duration-150 ease-in-out" title="Voir détails">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            
                                            @if ($commande->peutEtreModifieePar(Auth::user()))
                                                <a href="{{ route('commandes.edit', $commande) }}" class="text-yellow-600 hover:text-yellow-900 transition duration-150 ease-in-out" title="Modifier">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </a>
                                            @endif
                                            
                                            @if ($commande->statut !== 'soldée' && (Auth::user()->isAdmin() || Auth::user()->isGerant() || $commande->user_id === Auth::id()))
                                                <!-- Lien vers la page de soldage -->
                                                <a href="{{ route('commandes.solder.form', $commande) }}" class="text-green-600 hover:text-green-900 transition duration-150 ease-in-out" title="Solder la commande">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </a>
                                            @endif
                                            
                                            @if ($commande->peutEtreModifieePar(Auth::user()))
                                                <form action="{{ route('commandes.destroy', $commande) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 transition duration-150 ease-in-out" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette commande ? Cette action est irréversible.')">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 sm:px-6 py-4 text-center text-sm text-gray-500">
                                        <div class="flex flex-col items-center justify-center py-8">
                                            <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                            <p class="text-gray-500">Aucune commande trouvée</p>
                                            @if (Auth::user()->isCaissier())
                                                <a href="{{ route('commandes.create') }}" class="mt-2 text-[#0b5f37] hover:text-[#0a4d2c] font-medium">
                                                    Créer votre première commande
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if ($commandes->hasPages())
                    <div class="mt-6">
                        {{ $commandes->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Messages de notification -->
@if (session('success'))
<div id="notification" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 ease-in-out">
    <div class="flex items-center">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
</div>
@endif

@if (session('error'))
<div id="notification" class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 ease-in-out">
    <div class="flex items-center">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
</div>
@endif

<script>
// Masquer automatiquement les notifications après 5 secondes
document.addEventListener('DOMContentLoaded', function() {
    const notification = document.getElementById('notification');
    if (notification) {
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }
});
</script>
@endsection