@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- En-tête -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-[#0b5f37]">Export des Données</h2>
                        <p class="text-gray-600">Téléchargez les commandes et ventes au format CSV</p>
                    </div>
                    <a href="{{ route('commandes.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">
                        Retour aux Commandes
                    </a>
                </div>

                <!-- Formulaire d'export -->
                <div class="bg-gray-50 p-6 rounded-lg">
                    <form action="{{ route('commandes.download-ventes') }}" method="GET" class="space-y-6">
                        @csrf

                        <!-- Type d'export -->
                        <div>
                            <label for="type_export" class="block text-sm font-medium text-gray-700 mb-2">
                                Type d'export *
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                    <input type="radio" name="type_export" value="commandes" class="text-[#0b5f37] focus:ring-[#0b5f37]" required>
                                    <div class="ml-3">
                                        <span class="block text-sm font-medium text-gray-900">Commandes Détaillées</span>
                                        <span class="block text-sm text-gray-500">Tous les détails des commandes (produits, notes, etc.)</span>
                                    </div>
                                </label>
                                <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                    <input type="radio" name="type_export" value="ventes" class="text-[#0b5f37] focus:ring-[#0b5f37]" required>
                                    <div class="ml-3">
                                        <span class="block text-sm font-medium text-gray-900">Ventes Simplifiées</span>
                                        <span class="block text-sm text-gray-500">Format condensé pour analyse des ventes</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Statut des commandes -->
                        <div>
                            <label for="statut_export" class="block text-sm font-medium text-gray-700 mb-2">
                                Statut des commandes *
                            </label>
                            <select name="statut_export" id="statut_export" class="w-full border-gray-300 rounded-md focus:border-[#0b5f37] focus:ring-[#0b5f37]" required>
                                <option value="toutes">Toutes les commandes</option>
                                <option value="soldees">Commandes soldées seulement</option>
                                <option value="en_cours">Commandes en cours seulement</option>
                            </select>
                        </div>

                        <!-- Période -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="date_debut_export" class="block text-sm font-medium text-gray-700 mb-2">
                                    Date de début
                                </label>
                                <input type="date" name="date_debut_export" id="date_debut_export" 
                                       class="w-full border-gray-300 rounded-md focus:border-[#0b5f37] focus:ring-[#0b5f37]">
                            </div>
                            <div>
                                <label for="date_fin_export" class="block text-sm font-medium text-gray-700 mb-2">
                                    Date de fin
                                </label>
                                <input type="date" name="date_fin_export" id="date_fin_export" 
                                       class="w-full border-gray-300 rounded-md focus:border-[#0b5f37] focus:ring-[#0b5f37]">
                            </div>
                        </div>

                        <!-- Informations -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">
                                        Format CSV compatible Excel
                                    </h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <p>• Le fichier généré est au format CSV avec séparateur point-virgule</p>
                                        <p>• Compatible avec Microsoft Excel et Google Sheets</p>
                                        <p>• Encodage UTF-8 pour supporter les caractères spéciaux</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="flex justify-end space-x-3 pt-4">
                            <a href="{{ route('commandes.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                                Annuler
                            </a>
                            <button type="submit" class="bg-[#0b5f37] text-white px-6 py-2 rounded hover:bg-[#0a4d2c] flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Télécharger l'export
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection