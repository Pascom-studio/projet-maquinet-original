@extends('layouts.app')

@section('title', 'Modifier l\'utilisateur')

@section('content')
<div class="py-4">
    <div class="max-w-6xl mx-auto px-3 sm:px-6">
        <!-- Alertes -->
        @if(session('error'))
            <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-500 rounded-lg flex items-start">
                <svg class="w-5 h-5 text-red-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <div class="flex-1">
                    <p class="text-red-700 text-sm">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- En-tête -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Modifier l'utilisateur</h1>
                    <p class="mt-1 text-gray-600">{{ $user->prenom }} {{ $user->name }}</p>
                </div>
                <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Retour
                </a>
            </div>
        </div>

        <!-- FORMULAIRE DE MISE À JOUR -->
        <form action="{{ route('users.update', $user->id) }}" method="POST" id="updateForm">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Informations personnelles -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-[#0b5f37] to-[#0a4d2d] px-4 py-3">
                        <h2 class="text-base font-semibold text-white flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Informations personnelles
                        </h2>
                    </div>
                    <div class="p-4 space-y-4">
                        <!-- Prénom -->
                        <div>
                            <label for="prenom" class="block text-sm font-medium text-gray-700 mb-1">Prénom *</label>
                            <input type="text" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0b5f37] focus:border-[#0b5f37] text-sm @error('prenom') border-red-500 @enderror" 
                                   id="prenom" 
                                   name="prenom" 
                                   value="{{ old('prenom', $user->prenom) }}" 
                                   required>
                            @error('prenom')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nom -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                            <input type="text" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0b5f37] focus:border-[#0b5f37] text-sm @error('name') border-red-500 @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $user->name) }}" 
                                   required>
                            @error('name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                            <input type="email" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0b5f37] focus:border-[#0b5f37] text-sm @error('email') border-red-500 @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $user->email) }}" 
                                   required>
                            @error('email')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Contact -->
                        <div>
                            <label for="contact" class="block text-sm font-medium text-gray-700 mb-1">Contact</label>
                            <input type="text" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0b5f37] focus:border-[#0b5f37] text-sm @error('contact') border-red-500 @enderror" 
                                   id="contact" 
                                   name="contact" 
                                   value="{{ old('contact', $user->contact) }}">
                            @error('contact')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Rôle et sécurité -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-[#0b5f37] to-[#0a4d2d] px-4 py-3">
                        <h2 class="text-base font-semibold text-white flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Rôle et sécurité
                        </h2>
                    </div>
                    <div class="p-4 space-y-4">
                        <!-- Fonction/Rôle -->
                        <div>
                            <label for="fonction" class="block text-sm font-medium text-gray-700 mb-1">Fonction *</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0b5f37] focus:border-[#0b5f37] text-sm @error('fonction') border-red-500 @enderror" 
                                    id="fonction" 
                                    name="fonction" 
                                    required>
                                <option value="">Sélectionnez une fonction</option>
                                @foreach($fonctions as $value => $label)
                                    <option value="{{ $value }}" 
                                            {{ old('fonction', $user->fonction) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('fonction')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Admin responsable -->
                        <div id="admin-field">
                            <label for="admin_id" class="block text-sm font-medium text-gray-700 mb-1">Admin responsable</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0b5f37] focus:border-[#0b5f37] text-sm @error('admin_id') border-red-500 @enderror" 
                                    id="admin_id" 
                                    name="admin_id">
                                <option value="">Aucun (auto-géré)</option>
                                @foreach($admins as $id => $name)
                                    <option value="{{ $id }}" 
                                            {{ old('admin_id', $user->admin_id) == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('admin_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Mot de passe -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe</label>
                            <input type="password" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0b5f37] focus:border-[#0b5f37] text-sm @error('password') border-red-500 @enderror" 
                                   id="password" 
                                   name="password"
                                   placeholder="Laissez vide pour ne pas modifier">
                            @error('password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirmation mot de passe -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
                            <input type="password" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0b5f37] focus:border-[#0b5f37] text-sm" 
                                   id="password_confirmation" 
                                   name="password_confirmation"
                                   placeholder="Confirmez le nouveau mot de passe">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Adresse -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-[#0b5f37] to-[#0a4d2d] px-4 py-3">
                    <h2 class="text-base font-semibold text-white flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Adresse
                    </h2>
                </div>
                <div class="p-4">
                    <div>
                        <label for="adresse" class="block text-sm font-medium text-gray-700 mb-1">Adresse complète</label>
                        <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0b5f37] focus:border-[#0b5f37] text-sm @error('adresse') border-red-500 @enderror" 
                                  id="adresse" 
                                  name="adresse" 
                                  rows="3"
                                  placeholder="Adresse complète de l'utilisateur">{{ old('adresse', $user->adresse) }}</textarea>
                        @error('adresse')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 border-t border-gray-200">
                <!-- Bouton de suppression (SÉPARÉ du formulaire) -->
                @if(auth()->user()->canManageUser($user) && auth()->id() !== $user->id)
                    <button type="button" 
                            class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm transition-colors duration-200 order-2 sm:order-1"
                            onclick="openDeleteModal()">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Supprimer l'utilisateur
                    </button>
                @else
                    <div class="order-2 sm:order-1"></div> <!-- Espaceur -->
                @endif
                
                <!-- Boutons du formulaire -->
                <div class="flex gap-3 w-full sm:w-auto order-1 sm:order-2">
                    <a href="{{ route('users.index') }}" 
                       class="flex-1 sm:flex-none inline-flex items-center justify-center px-6 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 text-sm transition-colors duration-200">
                        Annuler
                    </a>
                    <button type="submit" 
                            class="flex-1 sm:flex-none inline-flex items-center justify-center px-6 py-2 bg-[#0b5f37] text-white rounded-lg hover:bg-[#0a4d2d] text-sm transition-colors duration-200"
                            id="updateButton">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Mettre à jour
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal de suppression (SÉPARÉE) -->
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
            <p class="text-gray-700 mb-3">Êtes-vous sûr de vouloir supprimer <strong>{{ $user->prenom }} {{ $user->name }}</strong> ?</p>
            <div class="bg-red-50 border border-red-200 rounded p-3">
                <p class="text-red-700 text-sm flex items-start">
                    <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <span>Cette action est irréversible. Toutes les données de cet utilisateur seront perdues.</span>
                </p>
            </div>
        </div>
        <div class="px-4 py-3 bg-gray-50 rounded-b-lg flex justify-end gap-3">
            <button type="button" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 text-sm transition-colors duration-200"
                    onclick="closeDeleteModal()">
                Annuler
            </button>
            <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm transition-colors duration-200">
                    Oui, supprimer
                </button>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
    // Gestion du champ admin
    function toggleAdminField() {
        const fonction = document.getElementById('fonction').value;
        const adminField = document.getElementById('admin-field');
        
        if (fonction === 'super_admin') {
            adminField.style.display = 'none';
            document.getElementById('admin_id').value = '';
        } else {
            adminField.style.display = 'block';
        }
    }

    document.getElementById('fonction').addEventListener('change', toggleAdminField);
    toggleAdminField(); // Initialiser au chargement

    // Gestion de la modal
    function openDeleteModal() {
        document.getElementById('deleteUserModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteUserModal').classList.add('hidden');
    }

    // Fermer la modal en cliquant à l'extérieur
    document.getElementById('deleteUserModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });

    // Validation du formulaire
    document.getElementById('updateForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('password_confirmation').value;
        
        if (password && password !== confirmPassword) {
            e.preventDefault();
            alert('Les mots de passe ne correspondent pas.');
            return false;
        }
        
        // Désactiver le bouton
        const btn = document.getElementById('updateButton');
        btn.disabled = true;
        btn.innerHTML = 'Mise à jour...';
    });
</script>
@endsection