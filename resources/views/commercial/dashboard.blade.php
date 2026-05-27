@extends('layouts.app')

@section('content')
<div class="py-4 sm:py-6">
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-8">
        <!-- En-tête -->
        <div class="mb-4 sm:mb-6">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4">Tableau de Bord Commercial</h1>

            <!-- Barre de recherche responsive -->
            <form action="{{ route('commercial.search') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input type="text" name="search" placeholder="Rechercher par email, nom..." 
                           value="{{ $search ?? '' }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 sm:py-2 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-[#0b5f37]">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 sm:flex-none bg-[#0b5f37] text-white px-4 sm:px-6 py-3 sm:py-2 rounded-lg hover:bg-[#0a4d2c] text-sm sm:text-base flex items-center justify-center">
                        <span class="mr-2">🔍</span>
                        <span class="hidden sm:inline">Rechercher</span>
                    </button>
                    @if(isset($search))
                        <a href="{{ route('commercial.dashboard') }}" class="flex-1 sm:flex-none bg-gray-500 text-white px-4 sm:px-6 py-3 sm:py-2 rounded-lg hover:bg-gray-600 text-sm sm:text-base flex items-center justify-center">
                            <span class="mr-2">✕</span>
                            <span class="hidden sm:inline">Effacer</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Liste des Mobile Caissiers -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900">
                    Mobile Caissiers Assignés 
                    <span class="bg-[#0b5f37] text-white text-xs px-2 py-1 rounded-full ml-2">
                        {{ $mobileCaissiers->count() }}
                    </span>
                </h2>
            </div>

            <div class="divide-y divide-gray-200">
                @forelse($mobileCaissiers as $caissier)
                    <div class="px-4 sm:px-6 py-4">
                        <!-- En-tête du caissier -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-start space-y-2 sm:space-y-0 sm:space-x-4">
                                    <!-- Avatar et infos -->
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-[#0b5f37] rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                            {{ substr($caissier->prenom, 0, 1) }}{{ substr($caissier->name, 0, 1) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h3 class="text-base sm:text-lg font-medium text-gray-900 truncate">
                                                {{ $caissier->prenom }} {{ $caissier->name }}
                                            </h3>
                                            <div class="flex flex-col sm:flex-row sm:items-center text-xs sm:text-sm text-gray-500 space-y-1 sm:space-y-0 sm:space-x-4 mt-1">
                                                <span class="flex items-center">
                                                    <span class="mr-1">📧</span>
                                                    <span class="truncate">{{ $caissier->email }}</span>
                                                </span>
                                                @if($caissier->contact)
                                                <span class="flex items-center">
                                                    <span class="mr-1">📞</span>
                                                    <span>{{ $caissier->contact }}</span>
                                                </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Date de création - Mobile seulement -->
                                <p class="text-xs text-gray-400 mt-2 sm:hidden">
                                    Créé le {{ $caissier->created_at->format('d/m/Y') }}
                                </p>
                            </div>

                            <!-- Statut et date - Desktop -->
                            <div class="flex items-center justify-between sm:justify-end space-x-4">
                                <!-- Statut Actif/Inactif -->
                                <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium 
                                    {{ $caissier->est_actif ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    <span class="w-2 h-2 rounded-full mr-1 {{ $caissier->est_actif ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                    {{ $caissier->est_actif ? 'Actif' : 'Inactif' }}
                                </span>
                                
                                <!-- Date de création - Desktop -->
                                <p class="hidden sm:block text-xs text-gray-400 whitespace-nowrap">
                                    {{ $caissier->created_at->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>

                        <!-- Compteur des paiements mensuels -->
                        <div class="mt-4 sm:mt-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                <span class="mr-2">💰</span>
                                Paiements {{ now()->year }}
                            </h4>
                            
                            <!-- Cercles des mois -->
                            <div class="flex flex-wrap justify-center gap-1 sm:gap-2 mb-3">
                                @php
                                    // Gestion robuste des paiements mensuels
                                    $paiements = [];
                                    
                                    if ($caissier->paiements_mensuels) {
                                        if (is_array($caissier->paiements_mensuels)) {
                                            $paiements = $caissier->paiements_mensuels;
                                        } elseif (is_string($caissier->paiements_mensuels)) {
                                            $decoded = json_decode($caissier->paiements_mensuels, true);
                                            $paiements = is_array($decoded) ? $decoded : [];
                                        }
                                    }
                                    
                                    $moisEnCours = now()->month;
                                    $anneeEnCours = now()->year;
                                    $nomsMoisCourts = ['J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D'];
                                @endphp
                                
                                @for($i = 1; $i <= 12; $i++)
                                    @php
                                        $estPaye = isset($paiements[$anneeEnCours][$i]);
                                        $estMoisPasse = $i < $moisEnCours;
                                    @endphp
                                    
                                    <div class="flex flex-col items-center">
                                        <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center text-xs font-medium
                                            {{ $estPaye 
                                                ? 'bg-green-500 text-white border-2 border-green-600' 
                                                : ($estMoisPasse 
                                                    ? 'bg-red-500 text-white border-2 border-red-600' 
                                                    : 'bg-gray-200 text-gray-600 border-2 border-gray-300') }}">
                                            {{ $estPaye ? '✓' : $i }}
                                        </div>
                                        <span class="text-xs mt-1 text-gray-600 font-medium">
                                            {{ $nomsMoisCourts[$i-1] }}
                                        </span>
                                    </div>
                                @endfor
                            </div>
                            
                            <!-- Résumé statistiques -->
                            <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
                                @php
                                    $moisPayes = isset($paiements[$anneeEnCours]) ? count($paiements[$anneeEnCours]) : 0;
                                    $moisEnRetard = max(0, $moisEnCours - 1 - $moisPayes);
                                    $enAttente = ($moisEnCours <= 12 && !isset($paiements[$anneeEnCours][$moisEnCours])) ? 1 : 0;
                                @endphp
                                
                                <div class="text-center p-2 bg-green-50 rounded-lg border border-green-200">
                                    <div class="text-sm sm:text-base font-bold text-green-600">{{ $moisPayes }}</div>
                                    <div class="text-green-700 text-xs">Payés</div>
                                </div>
                                
                                <div class="text-center p-2 bg-red-50 rounded-lg border border-red-200">
                                    <div class="text-sm sm:text-base font-bold text-red-600">{{ $moisEnRetard }}</div>
                                    <div class="text-red-700 text-xs">En retard</div>
                                </div>
                                
                                <div class="text-center p-2 bg-yellow-50 rounded-lg border border-yellow-200">
                                    <div class="text-sm sm:text-base font-bold text-yellow-600">{{ $enAttente }}</div>
                                    <div class="text-yellow-700 text-xs">En attente</div>
                                </div>
                            </div>
                            
                            <!-- Légende -->
                            <div class="mt-3 flex flex-wrap justify-center gap-4 text-xs text-gray-500">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-green-500 rounded-full mr-1"></div>
                                    <span>Payé</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-red-500 rounded-full mr-1"></div>
                                    <span>En retard</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-gray-300 rounded-full mr-1"></div>
                                    <span>À venir</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <!-- État vide -->
                    <div class="px-4 sm:px-6 py-8 sm:py-12 text-center">
                        <div class="text-gray-400 text-4xl sm:text-6xl mb-4">👥</div>
                        <p class="text-gray-500 text-base sm:text-lg mb-2">Aucun mobile caissier assigné</p>
                        <p class="text-sm sm:text-base text-gray-400">
                            Les mobile caissiers vous seront assignés par le Super Admin
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Message si aucun caissier -->
        @if($mobileCaissiers->count() === 0)
        <div class="mt-6 px-4 sm:px-0">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                <p class="text-blue-700">
                    <span class="text-lg">👥</span><br>
                    Aucun mobile caissier n'est actuellement assigné à votre compte.<br>
                    <span class="text-sm">Contactez le Super Admin pour vous assigner des mobile caissiers.</span>
                </p>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
/* Améliorations responsive supplémentaires */
@media (max-width: 640px) {
    .text-truncate-mobile {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 200px;
    }
}

/* Empêcher le zoom sur les inputs mobiles */
@media (max-width: 768px) {
    input, select, textarea {
        font-size: 16px !important;
    }
}

/* Amélioration de l'affichage des cercles sur très petits écrans */
@media (max-width: 380px) {
    .flex-wrap > div {
        margin: 0 1px;
    }
}
</style>
@endsection