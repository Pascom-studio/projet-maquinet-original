@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- En-tête -->
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-[#0b5f37]">Nouvelle Table</h2>
                    <a href="{{ route('tables.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">
                        ← Retour
                    </a>
                </div>

                <!-- Formulaire -->
                <form action="{{ route('tables.store') }}" method="POST">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Numéro de table -->
                        <div>
                            <label for="numero" class="block text-sm font-medium text-gray-700 mb-2">
                                Numéro de table *
                            </label>
                            <input type="number" name="numero" id="numero" 
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-[#0b5f37] focus:ring-[#0b5f37] @error('numero') border-red-500 @enderror"
                                   value="{{ old('numero') }}" 
                                   min="1" required>
                            @error('numero')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nom de la table -->
                        <div>
                            <label for="nom" class="block text-sm font-medium text-gray-700 mb-2">
                                Nom de la table *
                            </label>
                            <input type="text" name="nom" id="nom" 
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-[#0b5f37] focus:ring-[#0b5f37] @error('nom') border-red-500 @enderror"
                                   value="{{ old('nom') }}" 
                                   placeholder="Ex: Table du fond, Table terrasse..."
                                   required>
                            @error('nom')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Informations -->
                    <div class="bg-blue-50 p-4 rounded-lg mb-6">
                        <div class="flex items-start">
                            <div class="text-blue-400 mr-3">💡</div>
                            <div>
                                <h4 class="text-sm font-medium text-blue-800">Information</h4>
                                <p class="text-sm text-blue-600 mt-1">
                                    La table sera créée avec le statut "Libre". Vous pourrez l'affecter à une hôtesse ultérieurement.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Boutons -->
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('tables.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 transition-colors">
                            Annuler
                        </a>
                        <button type="submit" class="bg-[#0b5f37] text-white px-6 py-2 rounded hover:bg-[#0a4d2c] transition-colors">
                            Créer la Table
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection