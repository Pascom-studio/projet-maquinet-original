@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="max-w-2xl mx-auto px-3 sm:px-6">
        <!-- En-tête -->
        <div class="mb-4">
            <h1 class="text-xl sm:text-2xl font-bold text-[#0b5f37] mb-2">Créer un Nouvel Utilisateur</h1>
            <div class="bg-blue-50 border border-blue-200 rounded p-3 text-sm">
                <p class="text-blue-700">👤 Remplissez les informations pour créer un nouvel utilisateur</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                
                <!-- Informations personnelles -->
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-700 mb-3 text-sm flex items-center">
                        <svg class="w-4 h-4 mr-2 text-[#0b5f37]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Informations Personnelles
                    </h3>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="prenom" class="block text-xs font-medium text-gray-700 mb-1">Prénom *</label>
                            <input type="text" name="prenom" id="prenom" required
                                   class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37]"
                                   value="{{ old('prenom') }}"
                                   placeholder="Prénom">
                            @error('prenom')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="name" class="block text-xs font-medium text-gray-700 mb-1">Nom *</label>
                            <input type="text" name="name" id="name" required
                                   class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37]"
                                   value="{{ old('name') }}"
                                   placeholder="Nom">
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Informations de compte -->
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-700 mb-3 text-sm flex items-center">
                        <svg class="w-4 h-4 mr-2 text-[#0b5f37]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Informations de Compte
                    </h3>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="email" class="block text-xs font-medium text-gray-700 mb-1">Email *</label>
                            <input type="email" name="email" id="email" required
                                   class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37]"
                                   value="{{ old('email') }}"
                                   placeholder="email@exemple.com">
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label for="fonction" class="block text-xs font-medium text-gray-700 mb-1">Rôle *</label>
                                <select name="fonction" id="fonction" required
                                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37]">
                                    <option value="">Sélectionnez un rôle</option>
                                    @foreach($fonctions as $value => $label)
                                        <option value="{{ $value }}" {{ old('fonction') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('fonction')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            @if($admins->count() > 0)
                            <div id="admin-field">
                                <label for="admin_id" class="block text-xs font-medium text-gray-700 mb-1">Admin Parent</label>
                                <select name="admin_id" id="admin_id"
                                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37]">
                                    <option value="">Auto-attribution</option>
                                    @foreach($admins as $id => $name)
                                        <option value="{{ $id }}" {{ old('admin_id') == $id ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('admin_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 mt-1">
                                    Si vide, vous serez automatiquement l'admin parent
                                </p>
                            </div>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label for="password" class="block text-xs font-medium text-gray-700 mb-1">Mot de passe *</label>
                                <input type="password" name="password" id="password" required
                                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37]"
                                       placeholder="Mot de passe">
                                @error('password')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-xs font-medium text-gray-700 mb-1">Confirmation *</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" required
                                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37]"
                                       placeholder="Confirmer le mot de passe">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations de contact -->
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-700 mb-3 text-sm flex items-center">
                        <svg class="w-4 h-4 mr-2 text-[#0b5f37]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        Informations de Contact (Optionnel)
                    </h3>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="contact" class="block text-xs font-medium text-gray-700 mb-1">Téléphone</label>
                            <input type="text" name="contact" id="contact"
                                   class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37]"
                                   value="{{ old('contact') }}"
                                   placeholder="+225 XX XX XX XX">
                            @error('contact')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="adresse" class="block text-xs font-medium text-gray-700 mb-1">Adresse</label>
                            <textarea name="adresse" id="adresse" rows="3"
                                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37]"
                                      placeholder="Adresse complète">{{ old('adresse') }}</textarea>
                            @error('adresse')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('users.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded text-sm hover:bg-gray-600 text-center">
                        Annuler
                    </a>
                    <button type="submit" class="bg-[#0b5f37] text-white px-4 py-2 rounded text-sm hover:bg-[#0a4d2c] font-semibold">
                        Créer l'Utilisateur
                    </button>
                </div>
            </form>
        </div>

        <!-- Informations sur les rôles -->
        <div class="mt-4 bg-blue-50 rounded p-4 border border-blue-200">
            <h4 class="font-semibold text-blue-800 mb-3 text-sm flex items-center">
                📋 Informations sur les Rôles
            </h4>
            <div class="grid grid-cols-1 gap-3 text-xs">
                <div class="bg-white p-3 rounded border border-blue-100">
                    <span class="font-semibold text-blue-700">👑 Super Admin:</span>
                    <p class="text-blue-600 mt-1">Accès complet à tout le système</p>
                </div>
                <div class="bg-white p-3 rounded border border-blue-100">
                    <span class="font-semibold text-blue-700">🔧 Admin:</span>
                    <p class="text-blue-600 mt-1">Gère ses propres utilisateurs et données</p>
                </div>
                <div class="bg-white p-3 rounded border border-blue-100">
                    <span class="font-semibold text-blue-700">👔 Gérant:</span>
                    <p class="text-blue-600 mt-1">Gère produits, catégories et ventes</p>
                </div>
                <div class="bg-white p-3 rounded border border-blue-100">
                    <span class="font-semibold text-blue-700">💰 Caissier:</span>
                    <p class="text-blue-600 mt-1">Effectue des ventes et gère sa caisse</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fonctionSelect = document.getElementById('fonction');
    const adminField = document.getElementById('admin-field');
    
    if (fonctionSelect && adminField) {
        fonctionSelect.addEventListener('change', function() {
            const selectedFonction = this.value;
            
            // Masquer le champ admin pour les super admins
            if (selectedFonction === 'super_admin') {
                adminField.style.display = 'none';
                document.getElementById('admin_id').value = '';
            } else {
                adminField.style.display = 'block';
            }
        });
        
        // Déclencher l'événement au chargement
        fonctionSelect.dispatchEvent(new Event('change'));
    }

    // Validation des mots de passe
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('password_confirmation');
    
    function validatePasswords() {
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Les mots de passe ne correspondent pas');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
    
    password.addEventListener('input', validatePasswords);
    confirmPassword.addEventListener('input', validatePasswords);
});
</script>

<style>
/* Améliorations pour mobile */
@media (max-width: 640px) {
    .bg-white {
        margin: 0 -0.75rem;
        border-radius: 0;
    }
}

/* Animation pour les champs */
input:focus, select:focus, textarea:focus {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}
</style>
@endsection