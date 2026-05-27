@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="px-3 sm:px-6">
        <!-- En-tête -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-[#0b5f37]">Gestion des Utilisateurs</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $users->total() }} utilisateur(s) trouvé(s)</p>
            </div>
            @if(Auth::user()->isSuperAdmin() || Auth::user()->isAdmin() || Auth::user()->isManager())
                <a href="{{ route('users.create') }}" class="bg-[#0b5f37] text-white px-4 py-2 rounded text-sm hover:bg-[#0a4d2c] w-full sm:w-auto text-center">
                    ➕ Nouvel Utilisateur
                </a>
            @endif
        </div>

        <!-- NOUVELLE BARRE DE RECHERCHE -->
        <div class="mb-6 bg-white rounded-lg shadow p-4">
            <form method="GET" action="{{ route('users.search') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                        🔍 Rechercher un utilisateur
                    </label>
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               id="search"
                               value="{{ request('search', '') }}"
                               placeholder="Rechercher par email, nom ou prénom..."
                               class="w-full border border-gray-300 rounded-md px-4 py-3 pl-10 text-sm focus:outline-none focus:ring-2 focus:ring-[#0b5f37] focus:border-[#0b5f37]"
                               autocomplete="off">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Tapez un email, nom ou prénom pour trouver un utilisateur
                    </p>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-[#0b5f37] text-white px-6 py-3 rounded-md hover:bg-[#0a4d2c] text-sm font-medium whitespace-nowrap">
                        Rechercher
                    </button>
                    @if(request('search'))
                        <a href="{{ route('users.index') }}" class="ml-2 bg-gray-500 text-white px-4 py-3 rounded-md hover:bg-gray-600 text-sm">
                            Effacer
                        </a>
                    @endif
                </div>
            </form>
            
            <!-- Suggestions de recherche -->
            @if(empty(request('search')))
            <div class="mt-3 text-xs text-gray-600">
                <span class="font-medium">Exemples :</span> 
                <span class="bg-gray-100 px-2 py-1 rounded mx-1">john@example.com</span>
                <span class="bg-gray-100 px-2 py-1 rounded mx-1">Jean Dupont</span>
                <span class="bg-gray-100 px-2 py-1 rounded mx-1">Marie</span>
            </div>
            @endif
        </div>

        <!-- Informations sur les permissions -->
        <div class="bg-blue-50 border border-blue-200 rounded p-3 mb-4 text-sm">
            @if(Auth::user()->isSuperAdmin())
                <p class="text-blue-700">👑 <strong>Super Admin</strong> - Vous gérez tous les utilisateurs</p>
            @elseif(Auth::user()->isAdmin())
                <p class="text-blue-700">🔧 <strong>Admin</strong> - Vous gérez vos utilisateurs dépendants</p>
            @elseif(Auth::user()->isManager())
                <p class="text-blue-700">👨‍💼 <strong>Manager</strong> - Vous pouvez créer des comptes hôtesses</p>
            @endif
        </div>

        <!-- Version desktop -->
        <div class="hidden sm:block bg-white rounded shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilisateur</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rôle</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admin Parent</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 bg-[#0b5f37] rounded-full flex items-center justify-center text-white text-xs font-bold">
                                        {{ strtoupper(substr($user->prenom, 0, 1)) }}{{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $user->prenom }} {{ $user->name }}
                                            @if($user->id === Auth::id())
                                                <span class="bg-green-100 text-green-800 text-xs px-1 py-0.5 rounded ml-1">Vous</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $roleColors = [
                                        'super_admin' => 'bg-purple-100 text-purple-800',
                                        'admin' => 'bg-red-100 text-red-800',
                                        'gerant' => 'bg-orange-100 text-orange-800',
                                        'caissier' => 'bg-blue-100 text-blue-800',
                                        'manager' => 'bg-indigo-100 text-indigo-800',
                                        'hotesse' => 'bg-pink-100 text-pink-800',
                                        'mobile_caissier' => 'bg-green-100 text-green-800',
                                        'commercial' => 'bg-blue-100 text-blue-800',
                                        'grande_caisse_mobile' => 'bg-purple-100 text-purple-800'
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $roleColors[$user->fonction] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst(str_replace('_', ' ', $user->fonction)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                @if($user->admin)
                                    {{ $user->admin->prenom }} {{ $user->admin->name }}
                                    <div class="text-xs text-gray-500">{{ $user->admin->fonction }}</div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                @if($user->contact)
                                    <div class="text-xs">{{ $user->contact }}</div>
                                @endif
                                @if($user->adresse)
                                    <div class="text-xs text-gray-500 truncate max-w-xs">{{ $user->adresse }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center space-x-3">
                                    <a href="{{ route('users.show', $user->id) }}" 
                                       class="text-blue-600 hover:text-blue-900 p-2 rounded hover:bg-blue-50 transition-colors border border-blue-200"
                                       title="Voir détails">
                                        👁️
                                    </a>
                                    
                                    @if(Auth::user()->canManageUser($user))
                                        <a href="{{ route('users.edit', $user->id) }}" 
                                           class="text-[#ee8f13] hover:text-[#d67f11] p-2 rounded hover:bg-orange-50 transition-colors border border-orange-200"
                                           title="Modifier">
                                            ✏️
                                        </a>
                                        
                                        @if($user->id !== Auth::id())
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="text-red-600 hover:text-red-900 p-2 rounded hover:bg-red-50 transition-colors border border-red-200"
                                                        onclick="return confirmSuppression(event)"
                                                        title="Supprimer">
                                                    🗑️
                                                </button>
                                            </form>
                                        @else
                                            <span class="w-8 h-8"></span> <!-- Espaceur pour l'alignement -->
                                        @endif
                                    @else
                                        <span class="text-gray-400 p-2 border border-gray-200 rounded" title="Non autorisé">
                                            🔒
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    @if(request('search'))
                                        <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-sm font-medium text-gray-600">Aucun utilisateur trouvé pour "{{ request('search') }}"</p>
                                        <a href="{{ route('users.index') }}" class="text-[#0b5f37] hover:text-[#0a4d2c] text-sm font-medium mt-2">
                                            Voir tous les utilisateurs
                                        </a>
                                    @else
                                        <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                        </svg>
                                        <p class="text-sm font-medium text-gray-600">Aucun utilisateur trouvé</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($users->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $users->links() }}
            </div>
            @endif
        </div>

        <!-- Version mobile -->
        <div class="sm:hidden space-y-3">
            @forelse($users as $user)
            <div class="bg-white rounded-lg shadow border border-gray-200 p-4">
                <!-- En-tête de la carte -->
                <div class="flex justify-between items-start mb-3">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 bg-[#0b5f37] rounded-full flex items-center justify-center text-white text-sm font-bold">
                            {{ strtoupper(substr($user->prenom, 0, 1)) }}{{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-semibold text-gray-900">
                                {{ $user->prenom }} {{ $user->name }}
                                @if($user->id === Auth::id())
                                    <span class="bg-green-100 text-green-800 text-xs px-1 py-0.5 rounded ml-1">Vous</span>
                                @endif
                            </h3>
                            <p class="text-xs text-gray-500">{{ $user->email }}</p>
                        </div>
                    </div>
                    @php
                        $roleColors = [
                            'super_admin' => 'bg-purple-100 text-purple-800',
                            'admin' => 'bg-red-100 text-red-800',
                            'gerant' => 'bg-orange-100 text-orange-800',
                            'caissier' => 'bg-blue-100 text-blue-800',
                            'manager' => 'bg-indigo-100 text-indigo-800',
                            'hotesse' => 'bg-pink-100 text-pink-800',
                            'mobile_caissier' => 'bg-green-100 text-green-800',
                            'commercial' => 'bg-blue-100 text-blue-800',
                            'grande_caisse_mobile' => 'bg-purple-100 text-purple-800'
                        ];
                    @endphp
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $roleColors[$user->fonction] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ ucfirst(str_replace('_', ' ', $user->fonction)) }}
                    </span>
                </div>

                <!-- Informations -->
                <div class="grid grid-cols-2 gap-3 text-xs mb-3">
                    <div>
                        <p class="text-gray-500">Admin parent</p>
                        <p class="font-medium text-gray-900">
                            @if($user->admin)
                                {{ $user->admin->prenom }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-500">Contact</p>
                        <p class="font-medium text-gray-900">
                            {{ $user->contact ?: '-' }}
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3 pt-3 border-t border-gray-200">
                    <a href="{{ route('users.show', $user->id) }}" 
                       class="text-blue-600 hover:text-blue-900 px-3 py-2 rounded text-xs flex items-center border border-blue-200 bg-blue-50 hover:bg-blue-100 transition-colors">
                        👁️ Détails
                    </a>
                    
                    @if(Auth::user()->canManageUser($user))
                        <a href="{{ route('users.edit', $user->id) }}" 
                           class="text-[#ee8f13] hover:text-[#d67f11] px-3 py-2 rounded text-xs flex items-center border border-orange-200 bg-orange-50 hover:bg-orange-100 transition-colors">
                            ✏️ Modifier
                        </a>
                        
                        @if($user->id !== Auth::id())
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="text-red-600 hover:text-red-900 px-3 py-2 rounded text-xs flex items-center border border-red-200 bg-red-50 hover:bg-red-100 transition-colors"
                                        onclick="return confirmSuppression(event)">
                                    🗑️ Supprimer
                                </button>
                            </form>
                        @endif
                    @else
                        <span class="text-gray-400 text-xs flex items-center px-3 py-2 border border-gray-200 rounded" title="Non autorisé">
                            🔒 Verrouillé
                        </span>
                    @endif
                </div>
            </div>
            @empty
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6 text-center">
                @if(request('search'))
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm font-medium text-gray-600">Aucun utilisateur trouvé pour "{{ request('search') }}"</p>
                    <a href="{{ route('users.index') }}" class="text-[#0b5f37] hover:text-[#0a4d2c] text-sm font-medium mt-2 inline-block">
                        Voir tous les utilisateurs
                    </a>
                @else
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    <p class="text-sm font-medium text-gray-600">Aucun utilisateur trouvé</p>
                @endif
            </div>
            @endforelse
            
            <!-- Pagination mobile -->
            @if($users->hasPages())
            <div class="bg-white rounded-lg shadow border border-gray-200 p-4">
                {{ $users->links() }}
            </div>
            @endif
        </div>

        <!-- Statistiques -->
      <!-- Statistiques -->
@if($users->count() > 0)
<div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
    <div class="bg-[#8c52ff] text-white p-3 rounded shadow">
        <div class="flex items-center">
            <div class="text-lg mr-2">👔</div>
            <div>
                <h3 class="text-xs font-semibold opacity-90">Managers</h3>
                <p class="text-sm font-bold">{{ $users->where('fonction', 'manager')->count() }}</p>
            </div>
        </div>
    </div>
    <div class="bg-[#ee8f13] text-white p-3 rounded shadow">
        <div class="flex items-center">
            <div class="text-lg mr-2">🔧</div>
            <div>
                <h3 class="text-xs font-semibold opacity-90">Admins</h3>
                <p class="text-sm font-bold">{{ $users->where('fonction', 'admin')->count() }}</p>
            </div>
        </div>
    </div>
    
    <div class="bg-[#0b5f37] text-white p-3 rounded shadow">
        <div class="flex items-center">
            <div class="text-lg mr-2">🏢</div>
            <div>
                <h3 class="text-xs font-semibold opacity-90">Gérants</h3>
                <p class="text-sm font-bold">{{ $users->where('fonction', 'gerant')->count() }}</p>
            </div>
        </div>
    </div>

    <div class="bg-[#cb6ce6] text-white p-3 rounded shadow">
        <div class="flex items-center">
            <div class="text-lg mr-2">💰</div>
            <div>
                <h3 class="text-xs font-semibold opacity-90">Caissiers</h3>
                <p class="text-sm font-bold">{{ $users->where('fonction', 'caissier')->count() }}</p>
            </div>
        </div>
    </div>
</div>

<!-- NOUVELLES STATISTIQUES POUR MOBILE CAISSIERS ET GRANDES CAISSES -->
<div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
    <!-- Mobile Caissiers -->
    <div class="bg-[#25D366] text-white p-3 rounded shadow">
        <div class="flex items-center">
            <div class="text-lg mr-2">📱</div>
            <div>
                <h3 class="text-xs font-semibold opacity-90">Mobile Caissiers</h3>
                <p class="text-sm font-bold">{{ $users->where('fonction', 'mobile_caissier')->count() }}</p>
            </div>
        </div>
        @php
            $mobileCaissiers = $users->where('fonction', 'mobile_caissier');
            $actifs = $mobileCaissiers->where('est_actif', true)->count();
            $inactifs = $mobileCaissiers->where('est_actif', false)->count();
        @endphp
        <div class="text-xs opacity-80 mt-1">
            {{ $actifs }} actifs / {{ $inactifs }} inactifs
        </div>
    </div>

    <!-- Grandes Caisses Mobile -->
    <div class="bg-[#8c52ff] text-white p-3 rounded shadow">
        <div class="flex items-center">
            <div class="text-lg mr-2">🏦</div>
            <div>
                <h3 class="text-xs font-semibold opacity-90">Grandes Caisses</h3>
                <p class="text-sm font-bold">{{ $users->where('fonction', 'grande_caisse_mobile')->count() }}</p>
            </div>
        </div>
        @php
            $grandesCaisses = $users->where('fonction', 'grande_caisse_mobile');
            $comptesRegroupes = 0;
            foreach ($grandesCaisses as $grandeCaisse) {
                $comptesRegroupes += $grandeCaisse->comptesMobileCaissiers()->count();
            }
        @endphp
        <div class="text-xs opacity-80 mt-1">
            {{ $comptesRegroupes }} comptes regroupés
        </div>
    </div>

    <!-- Commerciaux -->
    <div class="bg-[#007bff] text-white p-3 rounded shadow">
        <div class="flex items-center">
            <div class="text-lg mr-2">🤝</div>
            <div>
                <h3 class="text-xs font-semibold opacity-90">Commerciaux</h3>
                <p class="text-sm font-bold">{{ $users->where('fonction', 'commercial')->count() }}</p>
            </div>
        </div>
        @php
            $commerciaux = $users->where('fonction', 'commercial');
            $caissiersAffectes = 0;
            foreach ($commerciaux as $commercial) {
                $caissiersAffectes += $commercial->mobileCaissiersCommercial()->count();
            }
        @endphp
        <div class="text-xs opacity-80 mt-1">
            {{ $caissiersAffectes }} caissiers affectés
        </div>
    </div>

    <!-- Taux d'Affectation -->
    <div class="bg-[#ff6b35] text-white p-3 rounded shadow">
        <div class="flex items-center">
            <div class="text-lg mr-2">📊</div>
            <div>
                <h3 class="text-xs font-semibold opacity-90">Taux Affectation</h3>
                @php
                    $totalMobileCaissiers = $mobileCaissiers->count();
                    $totalAffectes = 0;
                    foreach ($mobileCaissiers as $caissier) {
                        if ($caissier->commercial_id || $caissier->grande_caisse_id) {
                            $totalAffectes++;
                        }
                    }
                    $tauxAffectation = $totalMobileCaissiers > 0 ? round(($totalAffectes / $totalMobileCaissiers) * 100, 0) : 0;
                    
                    // Déterminer la couleur du taux
                    if ($tauxAffectation >= 80) {
                        $tauxColor = 'text-green-300';
                    } elseif ($tauxAffectation >= 50) {
                        $tauxColor = 'text-yellow-300';
                    } else {
                        $tauxColor = 'text-red-300';
                    }
                @endphp
                <p class="text-sm font-bold {{ $tauxColor }}">{{ $tauxAffectation }}%</p>
            </div>
        </div>
        <div class="text-xs opacity-80 mt-1">
            {{ $totalAffectes }}/{{ $totalMobileCaissiers }} caissiers
        </div>
    </div>
</div>

<!-- Statistiques détaillées pour Super Admin seulement -->
@if(Auth::user()->isSuperAdmin())
<div class="mt-4 bg-white rounded-lg shadow p-4">
    <h3 class="text-sm font-semibold text-[#0b5f37] mb-3">📈 Statistiques Détaillées Mobile Money</h3>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
        <!-- Mobile Caissiers avec paiement du mois -->
        <div class="bg-blue-50 p-3 rounded border border-blue-200">
            <div class="flex items-center">
                <div class="text-blue-600 text-lg mr-2">💰</div>
                <div>
                    <h4 class="text-xs font-medium text-blue-800">Paiements du mois</h4>
                    @php
                        $moisCourant = now()->month;
                        $anneeCourante = now()->year;
                        $payesCeMois = 0;
                        
                        foreach ($mobileCaissiers as $caissier) {
                            $paiements = $caissier->paiements_mensuels ?? [];
                            if (is_string($paiements)) {
                                $paiements = json_decode($paiements, true) ?? [];
                            }
                            
                            if (isset($paiements[$anneeCourante][$moisCourant])) {
                                $payesCeMois++;
                            }
                        }
                    @endphp
                    <p class="text-lg font-bold text-blue-900">{{ $payesCeMois }}</p>
                    <p class="text-xs text-blue-600">{{ $moisCourant }}/{{ $anneeCourante }}</p>
                </div>
            </div>
        </div>

        <!-- Mobile Caissiers non affectés -->
        <div class="bg-yellow-50 p-3 rounded border border-yellow-200">
            <div class="flex items-center">
                <div class="text-yellow-600 text-lg mr-2">⚠️</div>
                <div>
                    <h4 class="text-xs font-medium text-yellow-800">Non affectés</h4>
                    @php
                        $nonAffectes = $mobileCaissiers->filter(function($caissier) {
                            return !$caissier->commercial_id && !$caissier->grande_caisse_id;
                        })->count();
                    @endphp
                    <p class="text-lg font-bold text-yellow-900">{{ $nonAffectes }}</p>
                    <p class="text-xs text-yellow-600">Sans commercial/grande caisse</p>
                </div>
            </div>
        </div>

        <!-- Moyenne des paiements -->
        <div class="bg-green-50 p-3 rounded border border-green-200">
            <div class="flex items-center">
                <div class="text-green-600 text-lg mr-2">📅</div>
                <div>
                    <h4 class="text-xs font-medium text-green-800">Moyenne paiements</h4>
                    @php
                        $moisPayesTotal = 0;
                        foreach ($mobileCaissiers as $caissier) {
                            $paiements = $caissier->paiements_mensuels ?? [];
                            if (is_string($paiements)) {
                                $paiements = json_decode($paiements, true) ?? [];
                            }
                            
                            if (isset($paiements[$anneeCourante])) {
                                $moisPayesTotal += count($paiements[$anneeCourante]);
                            }
                        }
                        $moyenne = $totalMobileCaissiers > 0 ? round($moisPayesTotal / $totalMobileCaissiers, 1) : 0;
                    @endphp
                    <p class="text-lg font-bold text-green-900">{{ $moyenne }}</p>
                    <p class="text-xs text-green-600">mois/an en moyenne</p>
                </div>
            </div>
        </div>

        <!-- Répartition par commercial -->
        <div class="bg-purple-50 p-3 rounded border border-purple-200">
            <div class="flex items-center">
                <div class="text-purple-600 text-lg mr-2">👥</div>
                <div>
                    <h4 class="text-xs font-medium text-purple-800">Répartition</h4>
                    @php
                        $commerciauxAvecCaissiers = $commerciaux->filter(function($commercial) {
                            return $commercial->mobileCaissiersCommercial()->count() > 0;
                        })->count();
                    @endphp
                    <p class="text-lg font-bold text-purple-900">{{ $commerciauxAvecCaissiers }}/{{ $commerciaux->count() }}</p>
                    <p class="text-xs text-purple-600">commerciaux avec caissiers</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Lien vers la gestion détaillée -->
    <div class="mt-3 text-center">
        <a href="{{ route('dashboard') }}#mobile-caissiers-section" 
           class="text-[#0b5f37] hover:text-[#0a4d2c] text-xs font-medium inline-flex items-center">
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Voir la gestion détaillée des paiements
        </a>
    </div>
</div>
@endif
@endif
    </div>
</div>

<script>
function confirmSuppression(event) {
    // Empêcher la soumission immédiate
    event.preventDefault();
    
    // Confirmation plus stricte
    const userRow = event.target.closest('tr') || event.target.closest('.bg-white');
    const userName = userRow ? userRow.querySelector('.text-sm.font-medium')?.textContent.trim() : 'cet utilisateur';
    
    Swal.fire({
        title: 'Êtes-vous sûr ?',
        html: `Vous allez supprimer <strong>${userName}</strong><br><span class="text-red-600">Cette action est irréversible !</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Oui, supprimer !',
        cancelButtonText: 'Annuler',
        backdrop: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        customClass: {
            confirmButton: 'bg-red-600 hover:bg-red-700 px-4 py-2 rounded',
            cancelButton: 'bg-gray-500 hover:bg-gray-600 px-4 py-2 rounded'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Soumettre le formulaire
            event.target.closest('form').submit();
        }
    });
    
    return false;
}

// Alternative si SweetAlert2 n'est pas disponible
if (typeof Swal === 'undefined') {
    function confirmSuppression(event) {
        const userRow = event.target.closest('tr') || event.target.closest('.bg-white');
        const userName = userRow ? userRow.querySelector('.text-sm.font-medium')?.textContent.trim() : 'cet utilisateur';
        
        const confirmation = confirm(`ATTENTION !\n\nVous allez supprimer "${userName}"\n\nCette action est irréversible !\n\nCliquez sur OK pour confirmer la suppression.`);
        
        if (!confirmation) {
            event.preventDefault();
            return false;
        }
        
        return true;
    }
}

// Recherche en temps réel (optionnel)
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const searchForm = document.querySelector('form[action*="search"]');
    
    if (searchInput && searchForm) {
        let searchTimeout;
        
        // Recherche automatique après 500ms d'inactivité
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            
            if (this.value.length >= 3 || this.value.length === 0) {
                searchTimeout = setTimeout(() => {
                    searchForm.submit();
                }, 500);
            }
        });
        
        // Empêcher la soumission du formulaire avec Enter si on veut garder la recherche en temps réel
        searchForm.addEventListener('submit', function(e) {
            if (searchInput.value.length < 2 && searchInput.value.length > 0) {
                e.preventDefault();
                alert('Veuillez saisir au moins 2 caractères pour la recherche.');
            }
        });
    }
});
</script>

<style>
/* Animation pour les cartes utilisateur */
.bg-white {
    transition: all 0.2s ease;
}

.bg-white:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Amélioration de l'accessibilité pour les boutons */
button, a {
    min-height: 32px;
    min-width: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* Espacement amélioré entre les boutons */
.flex.space-x-3 > * + * {
    margin-left: 0.75rem;
}

/* Style pour les boutons de suppression avec plus d'espace */
form.inline button[type="submit"] {
    margin-left: 0.5rem;
}

/* Amélioration de l'accessibilité */
@media (max-width: 640px) {
    .bg-white {
        margin: 0 -0.5rem;
    }
    
    .flex.space-x-3 > * + * {
        margin-left: 0.5rem;
    }
    
    /* Style pour la barre de recherche mobile */
    input[type="text"] {
        font-size: 16px; /* Empêche le zoom sur iOS */
    }
}

/* Style pour les résultats de recherche en surbrillance */
.highlight {
    background-color: #ffeb3b;
    padding: 2px;
    border-radius: 3px;
}
</style>
@endsection