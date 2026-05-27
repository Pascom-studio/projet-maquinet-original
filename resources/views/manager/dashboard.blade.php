@extends('layouts.app')

@section('content')
@php
    $user = Auth::user();
@endphp

<div class="py-4 sm:py-6">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#0b5f37] px-2 sm:px-0">Tableau de Bord Manager</h1>
        <p class="text-gray-600 px-2 sm:px-0">Gestion des hôtesses et supervision des performances</p>
    </div>

    <!-- Cartes Statistiques -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6 px-2 sm:px-0">
        <!-- Total Hôtesses -->
        <div class="bg-white rounded-lg shadow p-3 sm:p-4 border-l-4 border-[#0b5f37]">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Total Hôtesses</h3>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#0b5f37]">{{ $totalHotesses }}</p>
                </div>
                <div class="text-lg sm:text-xl lg:text-2xl ml-2">👩‍💼</div>
            </div>
            <p class="text-xs text-gray-500 mt-1 truncate">
                Sous votre supervision
            </p>
        </div>

        <!-- Hôtesses avec Tables -->
        <div class="bg-white rounded-lg shadow p-3 sm:p-4 border-l-4 border-[#10b981]">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Avec Tables</h3>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#10b981]">{{ $hotessesAvecTables }}</p>
                </div>
                <div class="text-lg sm:text-xl lg:text-2xl ml-2">🏪</div>
            </div>
            <p class="text-xs text-gray-500 mt-1 truncate">
                Affectées à des tables
            </p>
        </div>

        <!-- Tables Affectées -->
        <div class="bg-white rounded-lg shadow p-3 sm:p-4 border-l-4 border-[#8c52ff]">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Tables Affectées</h3>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#8c52ff]">{{ $tablesAffectees }}</p>
                </div>
                <div class="text-lg sm:text-xl lg:text-2xl ml-2">📊</div>
            </div>
            <p class="text-xs text-gray-500 mt-1 truncate">
                Sur {{ $totalTables }} total
            </p>
        </div>

        <!-- Commandes en Cours -->
        <div class="bg-white rounded-lg shadow p-3 sm:p-4 border-l-4 border-[#ff6b6b]">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Commandes en Cours</h3>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#ff6b6b]">{{ $commandesEnCours->count() }}</p>
                </div>
                <div class="text-lg sm:text-xl lg:text-2xl ml-2">📋</div>
            </div>
            <p class="text-xs text-gray-500 mt-1 truncate">
                En attente de soldage
            </p>
        </div>
    </div>

    <!-- NOUVELLE SECTION : Top Performances -->
    @if(isset($topPerformances) && count($topPerformances) > 0)
    <div class="mb-6 px-2 sm:px-0">
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg sm:text-xl font-semibold text-[#0b5f37]">🏆 Top Performances</h3>
                <a href="{{ route('audit.performance-hotesses') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    Voir toutes les performances →
                </a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($topPerformances as $index => $performance)
                <div class="bg-gradient-to-br 
                    @if($index === 0) from-yellow-50 to-yellow-100 border-l-4 border-yellow-400
                    @elseif($index === 1) from-gray-50 to-gray-100 border-l-4 border-gray-400
                    @else from-orange-50 to-orange-100 border-l-4 border-orange-400 @endif 
                    rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center">
                            <div class="text-xl mr-2">
                                @if($index === 0) 🥇
                                @elseif($index === 1) 🥈
                                @else 🥉 @endif
                            </div>
                            <h4 class="font-semibold text-gray-900">
                                {{ $performance['hotesse']->prenom }} {{ $performance['hotesse']->name }}
                            </h4>
                        </div>
                        <span class="text-xs bg-white px-2 py-1 rounded-full font-medium">
                            #{{ $index + 1 }}
                        </span>
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Chiffre d'affaires:</span>
                            <span class="font-bold text-green-600">
                                {{ number_format($performance['total_ventes'], 0, ',', ' ') }} FCFA
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Commandes:</span>
                            <span class="font-medium">{{ $performance['total_commandes'] }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Efficacité:</span>
                            <span class="font-medium {{ $performance['taux_efficacite'] >= 80 ? 'text-green-600' : ($performance['taux_efficacite'] >= 60 ? 'text-orange-600' : 'text-red-600') }}">
                                {{ $performance['taux_efficacite'] }}%
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 px-2 sm:px-0">
        <!-- Actions Rapides -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <h3 class="text-lg sm:text-xl font-semibold text-[#0b5f37] mb-3 sm:mb-4">🚀 Actions Rapides</h3>
            <div class="space-y-2 sm:space-y-3">
                <!-- Gestion des utilisateurs (uniquement hôtesses) -->
                <a href="{{ route('users.index') }}" class="flex items-center justify-center w-full bg-[#0b5f37] text-white py-2 sm:py-3 px-3 sm:px-4 rounded hover:bg-[#0a4d2c] transition-colors text-sm sm:text-base">
                    <span class="mr-2 text-base">👩‍💼</span>
                    <span class="truncate">Gérer les Hôtesses</span>
                </a>

                <!-- Gestion des tables -->
                <a href="{{ route('tables.index') }}" class="flex items-center justify-center w-full bg-[#8c52ff] text-white py-2 sm:py-3 px-3 sm:px-4 rounded hover:bg-[#7a41e6] transition-colors text-sm sm:text-base">
                    <span class="mr-2 text-base">🏪</span>
                    <span class="truncate">Gérer les Tables</span>
                </a>

                <!-- Performance des hôtesses -->
                <a href="{{ route('audit.performance-hotesses') }}" class="flex items-center justify-center w-full bg-[#10b981] text-white py-2 sm:py-3 px-3 sm:px-4 rounded hover:bg-[#0da271] transition-colors text-sm sm:text-base">
                    <span class="mr-2 text-base">📈</span>
                    <span class="truncate">Performance Hôtesses</span>
                </a>

                <!-- Commandes soldées -->
                <a href="#commandes-soldees" class="flex items-center justify-center w-full bg-[#f59e0b] text-white py-2 sm:py-3 px-3 sm:px-4 rounded hover:bg-[#d97706] transition-colors text-sm sm:text-base">
                    <span class="mr-2 text-base">💰</span>
                    <span class="truncate">Commandes Soldées</span>
                </a>
            </div>
        </div>

        <!-- Observations Récentes -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg sm:text-xl font-semibold text-[#0b5f37]">📝 Observations Récentes</h3>
                <span class="text-sm text-gray-500">
                    {{ $observationsRecentes->count() }} envoyée(s)
                </span>
            </div>
            
            <div class="space-y-3 max-h-60 overflow-y-auto">
                @foreach($observationsRecentes as $observation)
                    <div class="flex justify-between items-start p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2 mb-1">
                                <span class="text-sm font-medium text-gray-900 truncate">
                                    {{ $observation->hotesse->prenom }} {{ $observation->hotesse->name }}
                                </span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                    {{ $observation->type === 'positif' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $observation->type === 'negatif' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $observation->type === 'suggestion' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                    {{ ucfirst($observation->type) }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 mb-1 truncate">
                                {{ $observation->titre }}
                            </div>
                            <div class="text-xs text-gray-400">
                                {{ $observation->date_observation->format('d/m/Y H:i') }}
                            </div>
                        </div>
                        <div class="text-right ml-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                {{ $observation->priorite === 'faible' ? 'bg-gray-100 text-gray-800' : '' }}
                                {{ $observation->priorite === 'moyenne' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $observation->priorite === 'elevee' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ ucfirst($observation->priorite) }}
                            </span>
                        </div>
                    </div>
                @endforeach
                
                @if($observationsRecentes->count() == 0)
                    <div class="text-center py-4">
                        <div class="text-gray-400 text-2xl mb-2">📝</div>
                        <p class="text-gray-500 text-sm">Aucune observation récente</p>
                        <p class="text-gray-400 text-xs mt-1">Envoyez des observations pour suivre les performances</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Section Commentaires Récents -->
    <div class="mt-8 px-2 sm:px-0">
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg sm:text-xl font-semibold text-[#0b5f37]">💬 Commentaires Récents</h3>
                    @if($commentairesRecents->count() > 0)
                    <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded-full">
                        {{ $commentairesRecents->count() }} nouveau(x)
                    </span>
                    @endif
                </div>
            </div>
            
            <div class="p-4 sm:p-6">
                @if($commentairesRecents->count() > 0)
                    <div class="space-y-4">
                        @foreach($commentairesRecents as $commentaire)
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition duration-150">
                            <!-- En-tête du commentaire -->
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 bg-green-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
                                        {{ strtoupper(substr($commentaire->auteur->prenom, 0, 1)) }}
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="font-semibold text-gray-900 text-sm">
                                            {{ $commentaire->auteur->prenom }} {{ $commentaire->auteur->name }}
                                        </h4>
                                        <p class="text-xs text-gray-500">
                                            sur l'observation : "{{ $commentaire->observation->titre }}"
                                        </p>
                                    </div>
                                </div>
                                <span class="text-xs text-gray-500">
                                    {{ $commentaire->created_at->format('d/m/Y H:i') }}
                                </span>
                            </div>

                            <!-- Contenu du commentaire -->
                            <p class="text-sm text-gray-700 mb-3">{{ Str::limit($commentaire->contenu, 150) }}</p>

                            <!-- Actions -->
                            <div class="flex justify-between items-center pt-3 border-t border-gray-200">
                                <span class="text-xs text-gray-500">
                                    Type : 
                                    <span class="font-medium 
                                        {{ $commentaire->observation->type === 'positif' ? 'text-green-600' : '' }}
                                        {{ $commentaire->observation->type === 'negatif' ? 'text-red-600' : '' }}
                                        {{ $commentaire->observation->type === 'suggestion' ? 'text-yellow-600' : '' }}">
                                        {{ ucfirst($commentaire->observation->type) }}
                                    </span>
                                    • 
                                    <span class="font-medium 
                                        {{ $commentaire->observation->priorite === 'faible' ? 'text-gray-600' : '' }}
                                        {{ $commentaire->observation->priorite === 'moyenne' ? 'text-yellow-600' : '' }}
                                        {{ $commentaire->observation->priorite === 'elevee' ? 'text-red-600' : '' }}">
                                        {{ ucfirst($commentaire->observation->priorite) }}
                                    </span>
                                </span>
                                <div class="flex space-x-2">
                                    <a href="{{ route('observations.show', $commentaire->observation) }}" 
                                       class="text-blue-600 hover:text-blue-900 text-xs font-medium">
                                        Voir la discussion
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="text-gray-400 text-2xl mb-2">💬</div>
                        <p class="text-gray-500 text-sm">Aucun commentaire récent</p>
                        <p class="text-gray-400 text-xs mt-1">Les hôtesses commenteront vos observations ici</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Liste des Hôtesses -->
    <div class="mt-8 px-2 sm:px-0">
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-lg sm:text-xl font-semibold text-[#0b5f37]">👩‍💼 Liste des Hôtesses</h3>
                    <div class="mt-2 sm:mt-0 flex space-x-2">
                        <a href="{{ route('users.create') }}" class="bg-[#0b5f37] text-white px-3 sm:px-4 py-2 rounded hover:bg-[#0a4d2c] text-sm">
                            + Nouvelle Hôtesse
                        </a>
                        <a href="{{ route('audit.performance-hotesses') }}" class="bg-blue-600 text-white px-3 sm:px-4 py-2 rounded hover:bg-blue-700 text-sm">
                            📈 Voir Performances
                        </a>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hôtesse</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tables Affectées</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commandes Actives</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Performance</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($hotesses as $hotesse)
                            @php
                                // Calcul de la performance en temps réel
                                $commandesHotesse = $commandesEnCours->where('hotesse_id', $hotesse->id)->count();
                                $commandesTotal = $commandesEnCours->where('hotesse_id', $hotesse->id)->count() + 
                                                $commandesSoldees->where('hotesse_id', $hotesse->id)->count();
                                $tauxPerformance = $commandesTotal > 0 ? round(($commandesSoldees->where('hotesse_id', $hotesse->id)->count() / $commandesTotal) * 100, 1) : 0;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-[#0b5f37] bg-opacity-10 rounded-full flex items-center justify-center">
                                            <span class="text-[#0b5f37] font-semibold text-sm">
                                                {{ $hotesse->initiales }}
                                            </span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $hotesse->prenom }} {{ $hotesse->name }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $hotesse->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $hotesse->contact ?? 'Non renseigné' }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $hotesse->tables_count > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $hotesse->tables_count }} table(s)
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $commandesHotesse > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $commandesHotesse }} commande(s)
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-16 bg-gray-200 rounded-full h-2">
                                            <div class="bg-{{ $tauxPerformance >= 80 ? 'green' : ($tauxPerformance >= 60 ? 'yellow' : 'red') }}-500 h-2 rounded-full" 
                                                 style="width: {{ $tauxPerformance }}%"></div>
                                        </div>
                                        <span class="text-xs font-medium {{ $tauxPerformance >= 80 ? 'text-green-600' : ($tauxPerformance >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ $tauxPerformance }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-1">
                                        <!-- Observation -->
                                        <a href="{{ route('observations.create', $hotesse->id) }}" 
                                           class="text-blue-600 hover:text-blue-900" 
                                           title="Envoyer une observation">
                                            📝
                                        </a>
                                        
                                        <!-- Voir profil -->
                                        <a href="{{ route('users.show', $hotesse) }}" 
                                           class="text-green-600 hover:text-green-900" 
                                           title="Voir le profil">
                                            👁️
                                        </a>
                                        
                                        <!-- Performance détaillée -->
                                        <a href="{{ route('audit.performance-hotesses', ['hotesse_id' => $hotesse->id]) }}" 
                                           class="text-purple-600 hover:text-purple-900" 
                                           title="Voir performance">
                                            📈
                                        </a>
                                        
                                        <!-- Modifier -->
                                        <a href="{{ route('users.edit', $hotesse) }}" 
                                           class="text-yellow-600 hover:text-yellow-900" 
                                           title="Modifier">
                                            ✏️
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 sm:px-6 py-4 text-center text-sm text-gray-500">
                                    Aucune hôtesse trouvée
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Commandes en Cours -->
    <div class="mt-8 px-2 sm:px-0">
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg sm:text-xl font-semibold text-[#0b5f37]">📋 Commandes en Cours</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Commande</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Table</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hôtesse</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produits</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Caissier</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($commandesEnCours as $commande)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $commande->numero_commande }}</div>
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $commande->table->nom_complet ?? 'N/A' }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $commande->hotesse->prenom ?? 'N/A' }} {{ $commande->hotesse->name ?? '' }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $commande->produits->count() }} produit(s)
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ number_format($commande->montant, 0, ',', ' ') }} F
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $commande->user->prenom ?? 'N/A' }} {{ $commande->user->name ?? '' }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $commande->created_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 sm:px-6 py-4 text-center text-sm text-gray-500">
                                    Aucune commande en cours
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Commandes Soldées -->
    <div id="commandes-soldees" class="mt-8 px-2 sm:px-0">
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg sm:text-xl font-semibold text-[#0b5f37]">💰 Commandes Soldées</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Commande</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Table</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hôtesse</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Soldée</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($commandesSoldees as $commande)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $commande->numero_commande }}</div>
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $commande->table->nom_complet ?? 'N/A' }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $commande->hotesse->prenom ?? 'N/A' }} {{ $commande->hotesse->name ?? '' }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                    {{ number_format($commande->montant, 0, ',', ' ') }} F
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $commande->updated_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('commandes.receipt', $commande->id) }}" 
                                       target="_blank"
                                       class="text-blue-600 hover:text-blue-900" 
                                       title="Voir le reçu">
                                        🧾
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 sm:px-6 py-4 text-center text-sm text-gray-500">
                                    Aucune commande soldée
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($commandesSoldees->hasPages())
                <div class="px-4 sm:px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $commandesSoldees->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
/* Style pour la scrollbar personnalisée */
.max-h-32::-webkit-scrollbar,
.max-h-40::-webkit-scrollbar,
.max-h-60::-webkit-scrollbar {
    width: 3px;
}

.max-h-32::-webkit-scrollbar-track,
.max-h-40::-webkit-scrollbar-track,
.max-h-60::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.max-h-32::-webkit-scrollbar-thumb,
.max-h-40::-webkit-scrollbar-thumb,
.max-h-60::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.max-h-32::-webkit-scrollbar-thumb:hover,
.max-h-40::-webkit-scrollbar-thumb:hover,
.max-h-60::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Amélioration du responsive */
@media (max-width: 640px) {
    .min-w-0 {
        min-width: 0;
    }
    
    table {
        font-size: 0.75rem;
    }
    
    .px-4 {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
}
</style>
@endsection