@extends('layouts.app')

@section('title', 'Mes Observations')

@section('content')
<div class="py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- En-tête -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Mes Observations</h1>
                    <p class="text-gray-600 mt-1">Toutes les observations que vous avez envoyées aux hôtesses</p>
                </div>
                <div class="flex items-center space-x-4 mt-3 sm:mt-0">
                    <a href="{{ route('dashboard') }}" 
                       class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition-colors text-sm">
                        ← Retour au dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Alertes -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Filtres -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-4">
                <div class="flex flex-wrap gap-4 items-center">
                    <span class="text-sm font-medium text-gray-700">Filtrer par :</span>
                    
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ request()->fullUrlWithQuery(['type' => '']) }}" 
                           class="px-3 py-1 rounded-full text-xs font-medium 
                                  {{ !request('type') ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            Tous
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['type' => 'positif']) }}" 
                           class="px-3 py-1 rounded-full text-xs font-medium 
                                  {{ request('type') == 'positif' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            Positifs
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['type' => 'negatif']) }}" 
                           class="px-3 py-1 rounded-full text-xs font-medium 
                                  {{ request('type') == 'negatif' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            Négatifs
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['type' => 'suggestion']) }}" 
                           class="px-3 py-1 rounded-full text-xs font-medium 
                                  {{ request('type') == 'suggestion' ? 'bg-yellow-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            Suggestions
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des observations -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    📝 Toutes mes observations
                    <span class="ml-2 bg-gray-100 text-gray-600 text-sm px-2 py-1 rounded-full">
                        {{ $observations->total() }} observation(s)
                    </span>
                </h3>
            </div>

            <div class="p-4">
                @if($observations->count() > 0)
                    <div class="space-y-4">
                        @foreach($observations as $observation)
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition duration-150">
                            <!-- En-tête -->
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <h4 class="font-semibold text-gray-900 text-lg">{{ $observation->titre }}</h4>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                            {{ $observation->type === 'positif' ? 'bg-green-100 text-green-800 border border-green-200' : '' }}
                                            {{ $observation->type === 'negatif' ? 'bg-red-100 text-red-800 border border-red-200' : '' }}
                                            {{ $observation->type === 'suggestion' ? 'bg-yellow-100 text-yellow-800 border border-yellow-200' : '' }}">
                                            @if($observation->type === 'positif') 👍 @endif
                                            @if($observation->type === 'negatif') 👎 @endif
                                            @if($observation->type === 'suggestion') 💡 @endif
                                            {{ ucfirst($observation->type) }}
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            Pour : {{ $observation->hotesse->prenom }} {{ $observation->hotesse->name }}
                                        </span>
                                        <span class="text-gray-400">•</span>
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ $observation->date_observation->format('d/m/Y') }}
                                        </span>
                                        <span class="text-gray-400">•</span>
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $observation->date_observation->format('H:i') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end space-y-2">
                                    <!-- Badge priorité -->
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium 
                                        {{ $observation->priorite === 'faible' ? 'bg-gray-100 text-gray-800 border border-gray-200' : '' }}
                                        {{ $observation->priorite === 'moyenne' ? 'bg-orange-100 text-orange-800 border border-orange-200' : '' }}
                                        {{ $observation->priorite === 'elevee' ? 'bg-red-100 text-red-800 border border-red-200' : '' }}">
                                        @if($observation->priorite === 'faible') 🟢 @endif
                                        @if($observation->priorite === 'moyenne') 🟡 @endif
                                        @if($observation->priorite === 'elevee') 🔴 @endif
                                        {{ ucfirst($observation->priorite) }}
                                    </span>
                                    
                                    <!-- Badge statut -->
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium 
                                        {{ $observation->est_lu ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                                        @if($observation->est_lu)
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Lu
                                        @else
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Non lu
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <!-- Contenu -->
                            <div class="mb-4">
                                <p class="text-gray-700 whitespace-pre-line">{{ $observation->contenu }}</p>
                            </div>

                            <!-- Commentaires -->
                            @if($observation->commentaires->count() > 0)
                            <div class="mb-4 pt-3 border-t border-gray-200">
                                <div class="flex items-center text-sm text-gray-600 mb-2">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                    </svg>
                                    {{ $observation->commentaires->count() }} commentaire(s)
                                </div>
                                <!-- Afficher le dernier commentaire -->
                                @php
                                    $dernierCommentaire = $observation->commentaires->first();
                                @endphp
                                <div class="bg-gray-50 rounded p-3 border border-gray-200">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="font-medium text-gray-700 text-sm">
                                            {{ $dernierCommentaire->auteur->prenom }} {{ $dernierCommentaire->auteur->name }}
                                        </span>
                                        <span class="text-gray-500 text-xs">{{ $dernierCommentaire->created_at->format('d/m H:i') }}</span>
                                    </div>
                                    <p class="text-gray-600 text-sm">{{ Str::limit($dernierCommentaire->contenu, 100) }}</p>
                                </div>
                            </div>
                            @endif

                            <!-- Actions -->
                            <div class="flex justify-between items-center pt-3 border-t border-gray-200">
                                <div class="text-xs text-gray-500">
                                    Créée le {{ $observation->created_at->format('d/m/Y à H:i') }}
                                </div>
                                <div class="flex space-x-2">
                                    <a href="{{ route('observations.show', $observation) }}" 
                                       class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700 transition-colors text-sm font-medium flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Voir
                                    </a>
                                    
                                    <form action="{{ route('observations.destroy', $observation) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette observation ?')"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="bg-red-600 text-white px-3 py-2 rounded hover:bg-red-700 transition-colors text-sm font-medium flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $observations->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune observation</h3>
                        <p class="text-gray-500 max-w-md mx-auto">
                            @if(request('type'))
                                Aucune observation ne correspond à vos critères de filtrage.
                                <a href="{{ route('observations.index') }}" class="text-blue-600 hover:text-blue-500 underline">
                                    Voir toutes les observations
                                </a>
                            @else
                                Vous n'avez pas encore envoyé d'observation aux hôtesses.
                                <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-500 underline">
                                    Retourner au dashboard
                                </a> pour en créer une.
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Statistiques -->
        @if($observations->count() > 0)
        <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="text-blue-600 text-lg mr-3">📝</div>
                    <div>
                        <p class="text-sm font-medium text-blue-900">Total</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $observations->total() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="text-green-600 text-lg mr-3">👍</div>
                    <div>
                        <p class="text-sm font-medium text-green-900">Positifs</p>
                        <p class="text-2xl font-bold text-green-600">{{ $observations->where('type', 'positif')->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="text-red-600 text-lg mr-3">👎</div>
                    <div>
                        <p class="text-sm font-medium text-red-900">Négatifs</p>
                        <p class="text-2xl font-bold text-red-600">{{ $observations->where('type', 'negatif')->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="text-yellow-600 text-lg mr-3">💡</div>
                    <div>
                        <p class="text-sm font-medium text-yellow-900">Suggestions</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ $observations->where('type', 'suggestion')->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
.whitespace-pre-line {
    white-space: pre-line;
}
</style>
@endsection