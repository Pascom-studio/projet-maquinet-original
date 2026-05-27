@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="px-3 sm:px-6">
        <!-- En-tête -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-[#0b5f37]">Mouvements de Stock</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $mouvements->count() }} mouvement(s)</p>
            </div>
            <a href="{{ route('mouvements.create') }}" class="bg-[#0b5f37] text-white px-4 py-2 rounded text-sm hover:bg-[#0a4d2c] w-full sm:w-auto text-center">
                ➕ Nouveau Mouvement
            </a>
        </div>

        <!-- Filtres rapides -->
        <div class="bg-white rounded-lg shadow p-3 mb-4">
            <div class="flex flex-wrap gap-2">
                <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded text-xs hover:bg-gray-200 active-filter">
                    Tous ({{ $mouvements->count() }})
                </button>
                <button class="px-3 py-1 bg-green-100 text-green-700 rounded text-xs hover:bg-green-200">
                    Entrées ({{ $mouvements->where('type', 'entrée')->count() }})
                </button>
                <button class="px-3 py-1 bg-red-100 text-red-700 rounded text-xs hover:bg-red-200">
                    Sorties ({{ $mouvements->where('type', 'sortie')->count() }})
                </button>
            </div>
        </div>

        <!-- Version desktop -->
        <div class="hidden sm:block bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prix</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilisateur</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($mouvements as $mouvement)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">{{ $mouvement->product->designation }}</div>
                                <div class="text-xs text-gray-500">{{ $mouvement->product->categorie->nom }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $mouvement->type === 'entrée' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $mouvement->type === 'entrée' ? '🟢 Entrée' : '🔴 Sortie' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ $mouvement->quantite }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ number_format($mouvement->prix, 0, ',', ' ') }} F
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ $mouvement->user->prenom }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $mouvement->created_at->format('d/m H:i') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('mouvements.show', $mouvement) }}" 
                                       class="text-blue-600 hover:text-blue-900 text-xs flex items-center">
                                        👁️
                                    </a>
                                    <a href="{{ route('mouvements.edit', $mouvement) }}" 
                                       class="text-[#ee8f13] hover:text-[#d67f11] text-xs flex items-center">
                                        ✏️
                                    </a>
                                    @if(auth()->user()->isAdmin() || auth()->user()->isGerant())
                                    <form action="{{ route('mouvements.destroy', $mouvement) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900 text-xs flex items-center"
                                                onclick="return confirm('Supprimer ce mouvement ?')">
                                            🗑️
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Version mobile -->
        <div class="sm:hidden space-y-3">
            @forelse($mouvements as $mouvement)
            <div class="bg-white rounded-lg shadow border border-gray-200 p-4">
                <!-- En-tête de la carte -->
                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-gray-900">{{ $mouvement->product->designation }}</h3>
                        <p class="text-xs text-gray-500 mt-1">{{ $mouvement->product->categorie->nom }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                        {{ $mouvement->type === 'entrée' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $mouvement->type === 'entrée' ? '🟢 Entrée' : '🔴 Sortie' }}
                    </span>
                </div>

                <!-- Détails du mouvement -->
                <div class="grid grid-cols-2 gap-3 text-xs mb-3">
                    <div>
                        <p class="text-gray-500">Quantité</p>
                        <p class="font-medium text-gray-900">{{ $mouvement->quantite }} unités</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Prix unitaire</p>
                        <p class="font-medium text-gray-900">{{ number_format($mouvement->prix, 0, ',', ' ') }} F</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Utilisateur</p>
                        <p class="font-medium text-gray-900">{{ $mouvement->user->prenom }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Date</p>
                        <p class="font-medium text-gray-900">{{ $mouvement->created_at->format('d/m H:i') }}</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3 pt-3 border-t border-gray-200">
                    <a href="{{ route('mouvements.show', $mouvement) }}" 
                       class="text-blue-600 hover:text-blue-900 text-xs flex items-center">
                        👁️ Détails
                    </a>
                    <a href="{{ route('mouvements.edit', $mouvement) }}" 
                       class="text-[#ee8f13] hover:text-[#d67f11] text-xs flex items-center">
                        ✏️ Modifier
                    </a>
                    @if(auth()->user()->isAdmin() || auth()->user()->isGerant())
                    <form action="{{ route('mouvements.destroy', $mouvement) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="text-red-600 hover:text-red-900 text-xs flex items-center"
                                onclick="return confirm('Supprimer ce mouvement ?')">
                            🗑️ Supprimer
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6 text-center">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                <p class="text-sm font-medium text-gray-600">Aucun mouvement de stock</p>
                <p class="text-xs text-gray-500 mt-1">Commencez par créer votre premier mouvement</p>
                <a href="{{ route('mouvements.create') }}" class="inline-block mt-3 bg-[#0b5f37] text-white px-4 py-2 rounded text-sm hover:bg-[#0a4d2c]">
                    ➕ Créer un mouvement
                </a>
            </div>
            @endforelse
        </div>

        <!-- Statistiques -->
        @if($mouvements->count() > 0)
        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-[#0b5f37] text-white p-3 rounded shadow">
                <div class="flex items-center">
                    <div class="text-lg mr-2">📊</div>
                    <div>
                        <h3 class="text-xs font-semibold opacity-90">Total</h3>
                        <p class="text-sm font-bold">{{ $mouvements->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-green-600 text-white p-3 rounded shadow">
                <div class="flex items-center">
                    <div class="text-lg mr-2">🟢</div>
                    <div>
                        <h3 class="text-xs font-semibold opacity-90">Entrées</h3>
                        <p class="text-sm font-bold">{{ $mouvements->where('type', 'entrée')->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-red-600 text-white p-3 rounded shadow">
                <div class="flex items-center">
                    <div class="text-lg mr-2">🔴</div>
                    <div>
                        <h3 class="text-xs font-semibold opacity-90">Sorties</h3>
                        <p class="text-sm font-bold">{{ $mouvements->where('type', 'sortie')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-[#ee8f13] text-white p-3 rounded shadow">
                <div class="flex items-center">
                    <div class="text-lg mr-2">💰</div>
                    <div>
                        <h3 class="text-xs font-semibold opacity-90">Valeur</h3>
                        <p class="text-sm font-bold">
                            {{ number_format($mouvements->sum(function($m) { return $m->quantite * $m->prix; }), 0, ',', ' ') }} F
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
/* Animation pour les cartes */
.bg-white {
    transition: all 0.2s ease;
}

.bg-white:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Amélioration de l'accessibilité */
@media (max-width: 640px) {
    .bg-white {
        margin: 0 -0.5rem;
    }
}

/* Style pour les filtres */
.active-filter {
    background-color: #0b5f37 !important;
    color: white !important;
}
</style>

<script>
// Filtrage simple (pour une future implémentation)
document.querySelectorAll('.bg-white .px-3.py-1').forEach(button => {
    button.addEventListener('click', function() {
        // Retirer la classe active de tous les boutons
        document.querySelectorAll('.bg-white .px-3.py-1').forEach(btn => {
            btn.classList.remove('active-filter');
        });
        // Ajouter la classe active au bouton cliqué
        this.classList.add('active-filter');
        
        // Ici, vous pourriez ajouter la logique de filtrage
        const filterType = this.textContent.split(' ')[0].toLowerCase();
        console.log('Filtrer par:', filterType);
    });
});
</script>
@endsection