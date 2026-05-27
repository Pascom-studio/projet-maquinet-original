@extends('layouts.app')

@section('title', 'Personnalisation du thème')

@section('content')
<div class="container-fluid py-6">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-[#0b5f37]">🎨 Personnalisation du Thème</h1>
            <p class="text-gray-600">Personnalisez l'apparence de votre interface GestCool</p>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-green-800 font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <form action="{{ route('admin.theme.update') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Colonne des couleurs -->
                <div class="space-y-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Couleurs principales</h3>
                        
                        <!-- Couleur de la navbar -->
                        <div class="mb-4">
                            <label for="navbar_bg" class="block text-sm font-medium text-gray-700 mb-2">
                                Couleur de la navigation
                            </label>
                            <div class="flex items-center space-x-3">
                                <input type="color" 
                                       class="w-16 h-10 rounded border border-gray-300 cursor-pointer"
                                       id="navbar_bg" 
                                       name="navbar_bg" 
                                       value="#{{ $colors['navbar_bg'] }}"
                                       title="Choisir la couleur de la navigation">
                                <input type="text" 
                                       class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37]"
                                       value="{{ $colors['navbar_bg'] }}" 
                                       maxlength="6"
                                       oninput="document.getElementById('navbar_bg').value = '#' + this.value"
                                       placeholder="Couleur hexadécimale">
                                <span class="text-sm text-gray-500">#</span>
                            </div>
                        </div>

                        <!-- Couleur du footer -->
                        <div class="mb-4">
                            <label for="footer_bg" class="block text-sm font-medium text-gray-700 mb-2">
                                Couleur du pied de page
                            </label>
                            <div class="flex items-center space-x-3">
                                <input type="color" 
                                       class="w-16 h-10 rounded border border-gray-300 cursor-pointer"
                                       id="footer_bg" 
                                       name="footer_bg" 
                                       value="#{{ $colors['footer_bg'] }}"
                                       title="Choisir la couleur du pied de page">
                                <input type="text" 
                                       class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37]"
                                       value="{{ $colors['footer_bg'] }}" 
                                       maxlength="6"
                                       oninput="document.getElementById('footer_bg').value = '#' + this.value"
                                       placeholder="Couleur hexadécimale">
                                <span class="text-sm text-gray-500">#</span>
                            </div>
                        </div>

                        <!-- Couleur du texte -->
                        <div class="mb-4">
                            <label for="primary_text" class="block text-sm font-medium text-gray-700 mb-2">
                                Couleur du texte
                            </label>
                            <div class="flex items-center space-x-3">
                                <input type="color" 
                                       class="w-16 h-10 rounded border border-gray-300 cursor-pointer"
                                       id="primary_text" 
                                       name="primary_text" 
                                       value="#{{ $colors['primary_text'] }}"
                                       title="Choisir la couleur du texte">
                                <input type="text" 
                                       class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37]"
                                       value="{{ $colors['primary_text'] }}" 
                                       maxlength="6"
                                       oninput="document.getElementById('primary_text').value = '#' + this.value"
                                       placeholder="Couleur hexadécimale">
                                <span class="text-sm text-gray-500">#</span>
                            </div>
                        </div>

                        <!-- Couleur au survol -->
                        <div class="mb-4">
                            <label for="hover_color" class="block text-sm font-medium text-gray-700 mb-2">
                                Couleur au survol
                            </label>
                            <div class="flex items-center space-x-3">
                                <input type="color" 
                                       class="w-16 h-10 rounded border border-gray-300 cursor-pointer"
                                       id="hover_color" 
                                       name="hover_color" 
                                       value="#{{ $colors['hover_color'] }}"
                                       title="Choisir la couleur au survol">
                                <input type="text" 
                                       class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37]"
                                       value="{{ $colors['hover_color'] }}" 
                                       maxlength="6"
                                       oninput="document.getElementById('hover_color').value = '#' + this.value"
                                       placeholder="Couleur hexadécimale">
                                <span class="text-sm text-gray-500">#</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Colonne d'aperçu -->
                <div class="space-y-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Aperçu du thème</h3>
                        
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <!-- Aperçu de la navigation -->
                            <div class="bg-[#{{ $colors['navbar_bg'] }}] text-[#{{ $colors['primary_text'] }}] p-4">
                                <div class="flex justify-between items-center">
                                    <div class="font-semibold">🍊 GestCool</div>
                                    <div class="flex space-x-2">
                                        <div class="w-3 h-3 rounded-full bg-[#{{ $colors['hover_color'] }}] opacity-70"></div>
                                        <div class="w-3 h-3 rounded-full bg-[#{{ $colors['primary_text'] }}] opacity-50"></div>
                                        <div class="w-3 h-3 rounded-full bg-[#{{ $colors['primary_text'] }}] opacity-30"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Aperçu du contenu -->
                            <div class="p-4 bg-gray-50">
                                <div class="space-y-3">
                                    <div class="bg-white rounded p-3 border border-gray-200">
                                        <div class="text-sm text-gray-600">Contenu de l'application...</div>
                                    </div>
                                    <div class="bg-white rounded p-3 border border-gray-200">
                                        <div class="text-sm text-gray-600">Vos données et statistiques...</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Aperçu du footer -->
                            <div class="bg-[#{{ $colors['footer_bg'] }}] text-[#{{ $colors['primary_text'] }}] p-3 text-center text-sm">
                                &copy; 2024 GestCool - Votre thème personnalisé
                            </div>
                        </div>
                        
                        <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div class="text-sm text-blue-700">
                                    <strong>Note :</strong> Les changements de thème sont personnels et n'affectent pas les autres utilisateurs.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="mt-8 flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                <div class="text-sm text-gray-500">
                    Votre thème personnalisé sera appliqué immédiatement après sauvegarde.
                </div>
                
                <div class="flex space-x-3">
                    <a href="{{ route('dashboard') }}" 
                       class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors text-sm font-medium">
                        Retour au dashboard
                    </a>
                    <button type="submit" 
                            class="bg-[#0b5f37] text-white px-6 py-2 rounded-lg hover:bg-[#0a4d2c] transition-colors text-sm font-medium">
                        💾 Sauvegarder le thème
                    </button>
                </div>
            </div>
        </form>

        <!-- Option de réinitialisation -->
        <div class="mt-8 border-t border-gray-200 pt-6">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-yellow-800 mb-2">Réinitialiser le thème</h4>
                <p class="text-sm text-yellow-700 mb-3">
                    Remettre les couleurs par défaut de l'application.
                </p>
                <form action="{{ route('admin.theme.reset') }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 transition-colors text-sm font-medium"
                            onclick="return confirm('Êtes-vous sûr de vouloir réinitialiser le thème aux valeurs par défaut ?')">
                        🔄 Réinitialiser
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
/* Amélioration de l'apparence des inputs couleur */
input[type="color"] {
    -webkit-appearance: none;
    border: none;
    padding: 0;
}

input[type="color"]::-webkit-color-swatch-wrapper {
    padding: 0;
}

input[type="color"]::-webkit-color-swatch {
    border: 2px solid #e5e7eb;
    border-radius: 4px;
}
</style>
@endsection