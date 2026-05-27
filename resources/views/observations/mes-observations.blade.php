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
                    <p class="text-gray-600 mt-1">Toutes les observations que vous avez reçues</p>
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

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ request()->fullUrlWithQuery(['statut' => '']) }}" 
                           class="px-3 py-1 rounded-full text-xs font-medium 
                                  {{ !request('statut') ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            Tous les statuts
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['statut' => 'non_lu']) }}" 
                           class="px-3 py-1 rounded-full text-xs font-medium 
                                  {{ request('statut') == 'non_lu' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            Non lus
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['statut' => 'lu']) }}" 
                           class="px-3 py-1 rounded-full text-xs font-medium 
                                  {{ request('statut') == 'lu' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            Lus
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
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition duration-150
                            {{ !$observation->est_lu ? 'bg-blue-50 border-blue-200' : '' }}">
                            <!-- En-tête -->
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 text-lg">{{ $observation->titre }}</h4>
                                    <div class="flex flex-wrap items-center gap-2 mt-1">
                                        <span class="text-sm text-gray-500">
                                            Par {{ $observation->manager->prenom }} {{ $observation->manager->name }}
                                        </span>
                                        <span class="text-xs text-gray-400">•</span>
                                        <span class="text-sm text-gray-500">
                                            {{ $observation->date_observation->format('d/m/Y à H:i') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end space-y-2">
                                    <!-- Badge type -->
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium 
                                        {{ $observation->type === 'positif' ? 'bg-green-100 text-green-800 border border-green-200' : '' }}
                                        {{ $observation->type === 'negatif' ? 'bg-red-100 text-red-800 border border-red-200' : '' }}
                                        {{ $observation->type === 'suggestion' ? 'bg-yellow-100 text-yellow-800 border border-yellow-200' : '' }}">
                                        @if($observation->type === 'positif') 👍 @endif
                                        @if($observation->type === 'negatif') 👎 @endif
                                        @if($observation->type === 'suggestion') 💡 @endif
                                        {{ ucfirst($observation->type) }}
                                    </span>
                                    
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
                                </div>
                            </div>

                            <!-- Contenu -->
                            <div class="mb-4">
                                <p class="text-gray-700 whitespace-pre-line">{{ $observation->contenu }}</p>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-between items-center pt-3 border-t border-gray-200">
                                <div class="flex items-center space-x-4">
                                    <span class="text-sm font-medium 
                                        {{ !$observation->est_lu ? 'text-blue-600' : 'text-green-600' }}">
                                        @if(!$observation->est_lu)
                                            <span class="flex items-center">
                                                <span class="w-2 h-2 bg-blue-600 rounded-full mr-2"></span>
                                                Nouveau - Non lu
                                            </span>
                                        @else
                                            <span class="flex items-center">
                                                <span class="w-2 h-2 bg-green-600 rounded-full mr-2"></span>
                                                Lu le {{ $observation->updated_at->format('d/m/Y') }}
                                            </span>
                                        @endif
                                    </span>
                                </div>
                                
                                <div class="flex space-x-2">
                                    @if(!$observation->est_lu)
                                    <form action="{{ route('observations.marquer-lu', $observation) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition-colors text-sm font-medium flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Marquer comme lu
                                        </button>
                                    </form>
                                    @else
                                    <span class="bg-green-100 text-green-800 px-3 py-2 rounded text-sm font-medium flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Déjà lu
                                    </span>
                                    @endif
                                    
                                    <a href="{{ route('observations.show', $observation) }}" 
                                       class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors text-sm font-medium flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Détails
                                    </a>
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
                            @if(request('type') || request('statut'))
                                Aucune observation ne correspond à vos critères de filtrage.
                                <a href="{{ route('observations.mes-observations') }}" class="text-blue-600 hover:text-blue-500 underline">
                                    Voir toutes les observations
                                </a>
                            @else
                                Vous n'avez reçu aucune observation pour le moment.
                                Votre manager vous enverra des feedbacks ici.
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