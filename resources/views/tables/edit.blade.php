@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- En-tête -->
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-[#0b5f37]">Modifier la Table</h2>
                    <a href="{{ route('tables.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">
                        ← Retour
                    </a>
                </div>

                <!-- Formulaire -->
                <form action="{{ route('tables.update', $table) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Numéro de table -->
                        <div>
                            <label for="numero" class="block text-sm font-medium text-gray-700 mb-2">
                                Numéro de table *
                            </label>
                            <input type="number" name="numero" id="numero" 
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-[#0b5f37] focus:ring-[#0b5f37] @error('numero') border-red-500 @enderror"
                                   value="{{ old('numero', $table->numero) }}" 
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
                                   value="{{ old('nom', $table->nom) }}" 
                                   placeholder="Ex: Table du fond, Table terrasse..."
                                   required>
                            @error('nom')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- CORRECTION DÉFINITIVE : Affectation à une hôtesse -->
                    <div class="mb-6">
                        <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Affecter à une hôtesse
                        </label>
                        <select name="user_id" id="user_id" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-[#0b5f37] focus:ring-[#0b5f37] @error('user_id') border-red-500 @enderror">
                            <option value="">-- Aucune affectation (Table libre) --</option>
                            @foreach($hotesses as $hotesse)
                                <option value="{{ $hotesse->id }}" 
                                    {{ old('user_id', $table->user_id) == $hotesse->id ? 'selected' : '' }}>
                                    {{ $hotesse->prenom }} {{ $hotesse->name }} - {{ $hotesse->email }}
                                    @if($hotesse->admin_id)
                                        (Admin: {{ $hotesse->admin->prenom ?? 'N/A' }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        
                        @error('user_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        
                        <!-- Informations de debug -->
                        <div class="mt-2 p-3 bg-blue-50 rounded-md">
                            <p class="text-xs text-blue-700">
                                <strong>Debug Info:</strong><br>
                                - Utilisateur: {{ Auth::user()->prenom }} ({{ Auth::user()->fonction }})<br>
                                - Hôtesses trouvées: {{ $hotesses->count() }}<br>
                                - Admin ID de l'utilisateur: {{ Auth::user()->admin_id }}<br>
                                @if(Auth::user()->isGerant())
                                    - Recherche hôtesses avec admin_id: {{ Auth::user()->admin_id }}
                                @endif
                            </p>
                        </div>
                        
                        @if($hotesses->isEmpty())
                            <p class="mt-2 text-sm text-yellow-600">
                                ⚠️ Aucune hôtesse disponible pour l'affectation dans votre établissement.
                                @if(Auth::user()->isGerant())
                                    <br>Vérifiez que votre admin a créé des hôtesses.
                                @endif
                            </p>
                        @endif
                    </div>

                    <!-- Statut actuel -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Statut actuel</label>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $table->estAffectee() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $table->statut_formatted }}
                            </span>
                            @if($table->estAffectee())
                                <span class="text-sm text-gray-600">
                                    Affectée à : {{ $table->user->prenom }} {{ $table->user->nom }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Boutons -->
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('tables.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 transition-colors">
                            Annuler
                        </a>
                        <button type="submit" class="bg-[#0b5f37] text-white px-6 py-2 rounded hover:bg-[#0a4d2c] transition-colors">
                            Modifier la Table
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection