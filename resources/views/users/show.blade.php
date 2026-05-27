@extends('layouts.app')

@section('title', 'Profil Utilisateur - ' . $user->prenom . ' ' . $user->name)

@section('content')
<div class="py-4">
    <div class="max-w-7xl mx-auto px-3 sm:px-6">
        <!-- En-tête mobile -->
        <div class="sm:hidden mb-4">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Profil Utilisateur</h1>
                    <p class="text-sm text-gray-600 mt-1">{{ $user->prenom }} {{ $user->name }}</p>
                </div>
                <a href="{{ route('users.index') }}" class="bg-gray-600 text-white p-2 rounded-lg hover:bg-gray-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
            </div>
            <div class="bg-white rounded-lg p-3 shadow border">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 bg-[#0b5f37] rounded-full flex items-center justify-center text-white text-sm font-bold">
                            {{ strtoupper(substr($user->prenom, 0, 1)) }}{{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">{{ $user->email }}</p>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $user->email_verified_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $user->email_verified_at ? 'Vérifié' : 'Non vérifié' }}
                            </span>
                        </div>
                    </div>
                    @if(auth()->user()->canManageUser($user))
                        <a href="{{ route('users.edit', $user) }}" class="bg-[#0b5f37] text-white p-2 rounded-lg hover:bg-[#0a4d2c]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- En-tête desktop -->
        <div class="hidden sm:flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    <svg class="w-6 h-6 inline-block mr-2 text-[#0b5f37]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Profil de : {{ $user->prenom }} {{ $user->name }}
                </h1>
                <p class="text-gray-600 mt-1">{{ $user->email }}</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('users.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 text-sm flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Retour
                </a>
                @if(auth()->user()->canManageUser($user))
                    <a href="{{ route('users.edit', $user) }}" class="bg-[#0b5f37] text-white px-4 py-2 rounded-lg hover:bg-[#0a4d2c] text-sm flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Modifier
                    </a>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <!-- Informations personnelles -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
                    <h2 class="text-base font-semibold text-white flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Informations Personnelles
                    </h2>
                </div>
                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Prénom</label>
                            <p class="text-sm font-medium text-gray-900">{{ $user->prenom }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Nom</label>
                            <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Email</label>
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-900">{{ $user->email }}</p>
                            @if($user->email_verified_at)
                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Vérifié</span>
                            @else
                                <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Non vérifié</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Contact</label>
                        <div class="flex items-center">
                            @if($user->contact)
                                <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <p class="text-sm text-gray-900">{{ $user->contact }}</p>
                            @else
                                <p class="text-sm text-gray-500">Non renseigné</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations du compte -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-green-600 to-green-700 px-4 py-3">
                    <h2 class="text-base font-semibold text-white flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Informations du Compte
                    </h2>
                </div>
                <div class="p-4 space-y-4">
                    @php
                        $roleColors = [
                            'super_admin' => 'bg-purple-100 text-purple-800',
                            'admin' => 'bg-red-100 text-red-800',
                            'gerant' => 'bg-orange-100 text-orange-800',
                            'caissier' => 'bg-blue-100 text-blue-800'
                        ];
                        
                        $roleLabels = [
                            'super_admin' => 'Super Admin',
                            'admin' => 'Admin',
                            'gerant' => 'Gérant',
                            'caissier' => 'Caissier'
                        ];
                    @endphp

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Rôle</label>
                        <div class="flex items-center space-x-2">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $roleColors[$user->fonction] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $roleLabels[$user->fonction] ?? $user->fonction }}
                            </span>
                            @if(auth()->id() === $user->id)
                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Vous</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Admin Parent</label>
                        @if($user->admin)
                            <a href="{{ route('users.show', $user->admin) }}" class="text-sm text-[#0b5f37] hover:underline flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                {{ $user->admin->prenom }} {{ $user->admin->name }}
                            </a>
                        @else
                            <p class="text-sm text-gray-500">Aucun</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">ID Utilisateur</label>
                        <code class="text-xs bg-gray-100 px-2 py-1 rounded text-gray-700">{{ $user->id }}</code>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Statut</label>
                        @if($user->email_verified_at)
                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Compte actif</span>
                        @else
                            <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">En attente de vérification</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Adresse -->
        @if($user->adresse)
        <div class="mt-4 sm:mt-6 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-cyan-600 to-cyan-700 px-4 py-3">
                <h2 class="text-base font-semibold text-white flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Adresse
                </h2>
            </div>
            <div class="p-4">
                <p class="text-sm text-gray-900">{{ $user->adresse }}</p>
            </div>
        </div>
        @endif

        <!-- Informations système -->
        <div class="mt-4 sm:mt-6 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-gray-600 to-gray-700 px-4 py-3">
                <h2 class="text-base font-semibold text-white flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Informations Système
                </h2>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs font-medium text-gray-500">Créé le</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs font-medium text-gray-500">Modifié le</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $user->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs font-medium text-gray-500">Utilisateurs gérés</p>
                        @php
                            $subUsersCount = 0;
                            try {
                                $subUsersCount = $user->subUsers()->count();
                            } catch (Exception $e) {
                                $subUsersCount = 0;
                            }
                        @endphp
                        <p class="text-sm font-semibold text-gray-900">{{ $subUsersCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Utilisateurs gérés (si admin) -->
        @if($user->isAdmin() || $user->isSuperAdmin())
        <div class="mt-4 sm:mt-6 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-amber-500 to-amber-600 px-4 py-3">
                <h2 class="text-base font-semibold text-white flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    Utilisateurs Gérés
                    <span class="bg-white text-amber-600 text-xs px-2 py-1 rounded-full ml-2">{{ $subUsersCount }}</span>
                </h2>
            </div>
            <div class="p-4">
                @if($subUsersCount > 0)
                    <!-- Version desktop -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Rôle</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($user->subUsers as $subUser)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $subUser->prenom }} {{ $subUser->name }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $subUser->email }}</td>
                                        <td class="px-4 py-2">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $roleColors[$subUser->fonction] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $roleLabels[$subUser->fonction] ?? $subUser->fonction }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2">
                                            <a href="{{ route('users.show', $subUser) }}" class="text-blue-600 hover:text-blue-900 text-sm flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Voir
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Version mobile -->
                    <div class="sm:hidden space-y-3">
                        @foreach($user->subUsers as $subUser)
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900">{{ $subUser->prenom }} {{ $subUser->name }}</h4>
                                    <p class="text-xs text-gray-600">{{ $subUser->email }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $roleColors[$subUser->fonction] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $roleLabels[$subUser->fonction] ?? $subUser->fonction }}
                                </span>
                            </div>
                            <div class="flex justify-end">
                                <a href="{{ route('users.show', $subUser) }}" class="text-blue-600 hover:text-blue-900 text-xs flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Voir profil
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm text-center py-4">Aucun utilisateur géré</p>
                @endif
            </div>
        </div>
        @endif

        <!-- Pied de page avec actions -->
        <div class="mt-4 sm:mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
                <div class="text-sm text-gray-600">
                    <span class="font-medium">Dernière connexion: </span>
                    @if($user->last_login_at)
                        {{ \Carbon\Carbon::parse($user->last_login_at)->format('d/m/Y H:i') }}
                    @else
                        <span class="text-gray-400">Jamais connecté</span>
                    @endif
                </div>
                
                @if(auth()->user()->canManageUser($user) && auth()->id() !== $user->id)
                    <button type="button" 
                            class="w-full sm:w-auto bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 text-sm flex items-center justify-center"
                            onclick="openModal()">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Supprimer l'utilisateur
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
@if(auth()->user()->canManageUser($user) && auth()->id() !== $user->id)
<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden" id="deleteUserModal">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full">
        <div class="bg-red-600 text-white px-4 py-3 rounded-t-lg">
            <h3 class="text-base font-semibold flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                Confirmer la suppression
            </h3>
        </div>
        <div class="p-4">
            <p class="text-gray-700 mb-3 text-sm">Êtes-vous sûr de vouloir supprimer l'utilisateur :</p>
            <p class="text-center font-semibold text-gray-900">
                {{ $user->prenom }} {{ $user->name }}
            </p>
            <p class="text-center text-gray-600 text-sm mb-4">{{ $user->email }}</p>
            <div class="bg-yellow-50 border border-yellow-200 rounded p-3">
                <div class="flex items-start">
                    <svg class="w-4 h-4 text-yellow-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <p class="text-yellow-700 text-sm">Cette action est irréversible !</p>
                </div>
            </div>
        </div>
        <div class="px-4 py-3 bg-gray-50 rounded-b-lg flex justify-end gap-2">
            <button type="button" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 text-sm" onclick="closeModal()">
                Annuler
            </button>
            <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
                    Supprimer
                </button>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
function openModal() {
    document.getElementById('deleteUserModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('deleteUserModal').classList.add('hidden');
}

// Fermer la modal en cliquant à l'extérieur
document.getElementById('deleteUserModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Fermer la modal avec la touche Échap
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>

<style>
/* Animation pour les cartes */
.bg-white {
    transition: all 0.2s ease;
}

.bg-white:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Amélioration de l'accessibilité */
@media (max-width: 640px) {
    .bg-white {
        margin: 0 -0.5rem;
    }
}

/* Style pour les badges */
.bg-green-100 { background-color: #dcfce7; }
.bg-yellow-100 { background-color: #fef9c3; }
.bg-purple-100 { background-color: #f3e8ff; }
.bg-red-100 { background-color: #fee2e2; }
.bg-orange-100 { background-color: #ffedd5; }
.bg-blue-100 { background-color: #dbeafe; }
</style>
@endsection