@extends('layouts.app')

@section('title', 'Observation - ' . $observation->titre)

@section('content')
<div class="py-4">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Bouton retour -->
        <div class="mb-4">
            <a href="{{ url()->previous() }}" 
               class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Retour
            </a>
        </div>

        <!-- Carte de l'observation -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- En-tête avec couleur selon le type -->
            <div class="px-6 py-4 
                {{ $observation->type === 'positif' ? 'bg-green-600' : '' }}
                {{ $observation->type === 'negatif' ? 'bg-red-600' : '' }}
                {{ $observation->type === 'suggestion' ? 'bg-yellow-600' : '' }}">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center">
                        <div class="text-white text-2xl mr-3">
                            @if($observation->type === 'positif') 👍 @endif
                            @if($observation->type === 'negatif') 👎 @endif
                            @if($observation->type === 'suggestion') 💡 @endif
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-white">{{ $observation->titre }}</h1>
                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                <span class="text-white text-opacity-90 text-sm">
                                    De : {{ $observation->manager->prenom }} {{ $observation->manager->name }}
                                </span>
                                <span class="text-white text-opacity-70 text-xs">•</span>
                                <span class="text-white text-opacity-90 text-sm">
                                    Pour : {{ $observation->hotesse->prenom }} {{ $observation->hotesse->name }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3 sm:mt-0 flex flex-wrap gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white bg-opacity-20 text-white border border-white border-opacity-30">
                            @if($observation->type === 'positif') 👍 @endif
                            @if($observation->type === 'negatif') 👎 @endif
                            @if($observation->type === 'suggestion') 💡 @endif
                            {{ ucfirst($observation->type) }}
                        </span>
                        
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white bg-opacity-20 text-white border border-white border-opacity-30">
                            @if($observation->priorite === 'faible') 🟢 @endif
                            @if($observation->priorite === 'moyenne') 🟡 @endif
                            @if($observation->priorite === 'elevee') 🔴 @endif
                            {{ ucfirst($observation->priorite) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Métadonnées -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span class="text-gray-600">
                            <strong>Date :</strong> 
                            {{ $observation->date_observation->format('d/m/Y') }}
                        </span>
                    </div>
                    
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-gray-600">
                            <strong>Heure :</strong> 
                            {{ $observation->date_observation->format('H:i') }}
                        </span>
                    </div>
                    
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-gray-600">
                            <strong>Statut :</strong> 
                            <span class="{{ $observation->est_lu ? 'text-green-600 font-medium' : 'text-blue-600 font-medium' }}">
                                {{ $observation->est_lu ? 'Lu' : 'Non lu' }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Contenu de l'observation -->
            <div class="px-6 py-6">
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                        <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Détails de l'observation
                    </h2>
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <p class="text-gray-700 whitespace-pre-line leading-relaxed">{{ $observation->contenu }}</p>
                    </div>
                </div>

                <!-- Informations supplémentaires -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Informations du manager -->
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <h3 class="font-semibold text-blue-900 mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Émetteur
                        </h3>
                        <div class="space-y-2 text-sm">
                            <p class="text-blue-800">
                                <strong>Nom :</strong> {{ $observation->manager->prenom }} {{ $observation->manager->name }}
                            </p>
                            <p class="text-blue-800">
                                <strong>Email :</strong> {{ $observation->manager->email }}
                            </p>
                            <p class="text-blue-800">
                                <strong>Fonction :</strong> {{ ucfirst($observation->manager->fonction) }}
                            </p>
                        </div>
                    </div>

                    <!-- Informations de l'hôtesse -->
                    <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                        <h3 class="font-semibold text-green-900 mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Destinataire
                        </h3>
                        <div class="space-y-2 text-sm">
                            <p class="text-green-800">
                                <strong>Nom :</strong> {{ $observation->hotesse->prenom }} {{ $observation->hotesse->name }}
                            </p>
                            <p class="text-green-800">
                                <strong>Email :</strong> {{ $observation->hotesse->email }}
                            </p>
                            <p class="text-green-800">
                                <strong>Fonction :</strong> {{ ucfirst($observation->hotesse->fonction) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                    <div class="text-sm text-gray-500">
                        <p>
                            Observation créée le {{ $observation->created_at->format('d/m/Y à H:i') }}
                            @if($observation->created_at != $observation->updated_at)
                                • Modifiée le {{ $observation->updated_at->format('d/m/Y à H:i') }}
                            @endif
                        </p>
                    </div>
                    
                    <div class="flex space-x-3">
                        @if(Auth::user()->id === $observation->hotesse_id && !$observation->est_lu)
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
                        @endif

                        @if(Auth::user()->id === $observation->manager_id)
                        <form action="{{ route('observations.destroy', $observation) }}" method="POST" class="inline" 
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette observation ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition-colors text-sm font-medium flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Supprimer
                            </button>
                        </form>
                        @endif

                        <a href="{{ route('observations.mes-observations') }}" 
                           class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition-colors text-sm font-medium flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                            Liste des observations
                        </a>
                    </div>
                </div>
            </div>
        </div>

       <!-- Section commentaires -->
<div class="mt-6 bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
            </svg>
            Discussion ({{ $observation->commentaires->count() }} commentaire(s))
        </h3>
    </div>

    <!-- Liste des commentaires -->
    <div class="p-6 space-y-4 max-h-96 overflow-y-auto">
        @if($observation->commentaires->count() > 0)
            @foreach($observation->commentaires as $commentaire)
            <div class="flex space-x-3 {{ $commentaire->user_id === $observation->manager_id ? 'flex-row-reverse space-x-reverse' : '' }}">
                <!-- Avatar -->
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 rounded-full bg-{{ $commentaire->user_id === $observation->manager_id ? 'blue' : 'green' }}-500 flex items-center justify-center text-white text-sm font-bold">
                        {{ strtoupper(substr($commentaire->auteur->prenom, 0, 1)) }}
                    </div>
                </div>
                
                <!-- Contenu du commentaire -->
                <div class="flex-1 {{ $commentaire->user_id === $observation->manager_id ? 'text-right' : '' }}">
                    <div class="bg-{{ $commentaire->user_id === $observation->manager_id ? 'blue' : 'gray' }}-50 rounded-lg p-4 border border-{{ $commentaire->user_id === $observation->manager_id ? 'blue' : 'gray' }}-200">
                        <div class="flex justify-between items-start mb-2">
                            <p class="text-sm font-medium text-gray-900">
                                {{ $commentaire->auteur->prenom }} {{ $commentaire->auteur->name }}
                                <span class="text-xs text-gray-500 ml-2">
                                    ({{ $commentaire->user_id === $observation->manager_id ? 'Manager' : 'Hôtesse' }})
                                </span>
                            </p>
                            <span class="text-xs text-gray-500">
                                {{ $commentaire->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                        <p class="text-gray-700 whitespace-pre-line">{{ $commentaire->contenu }}</p>
                    </div>
                    
                    <!-- Actions -->
                    @if(Auth::user()->id === $commentaire->user_id || Auth::user()->id === $observation->manager_id)
                    <div class="mt-1 {{ $commentaire->user_id === $observation->manager_id ? 'text-right' : '' }}">
                        <form action="{{ route('observations.commentaires.destroy', [$observation, $commentaire]) }}" 
                              method="POST" 
                              onsubmit="return confirm('Supprimer ce commentaire ?')"
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 hover:text-red-800">
                                Supprimer
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        @else
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                </svg>
                <p class="text-gray-500 text-sm">Aucun commentaire pour le moment</p>
                <p class="text-gray-400 text-xs mt-1">Soyez le premier à commenter cette observation</p>
            </div>
        @endif
    </div>

    <!-- Formulaire d'ajout de commentaire -->
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
        <form action="{{ route('observations.commentaires.store', $observation) }}" method="POST">
            @csrf
            <div class="flex space-x-3">
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center text-white text-sm font-bold">
                        {{ strtoupper(substr(Auth::user()->prenom, 0, 1)) }}
                    </div>
                </div>
                <div class="flex-1">
                    <textarea name="contenu" 
                              rows="3" 
                              class="w-full border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500 resize-none"
                              placeholder="Votre commentaire..."
                              required></textarea>
                    <div class="mt-2 flex justify-between items-center">
                        <p class="text-xs text-gray-500">
                            Vous commentez en tant que <strong>{{ Auth::user()->prenom }} {{ Auth::user()->name }}</strong>
                        </p>
                        <button type="submit" 
                                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors text-sm font-medium">
                            Envoyer le commentaire
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
    </div>
</div>

<style>
.whitespace-pre-line {
    white-space: pre-line;
}
</style>
@endsection