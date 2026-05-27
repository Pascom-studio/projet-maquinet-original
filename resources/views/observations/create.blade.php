@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- En-tête -->
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-[#0b5f37]">Nouvelle Observation</h2>
                    <p class="text-gray-600">
                        Observation pour {{ $hotesse->prenom }} {{ $hotesse->name }}
                    </p>
                </div>

                <!-- Formulaire -->
                <form action="{{ route('observations.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="hotesse_id" value="{{ $hotesse->id }}">
                    
                    <div class="space-y-6">
                        <!-- Type d'observation -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Type d'observation *</label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <label class="relative flex cursor-pointer">
                                    <input type="radio" name="type" value="positif" class="sr-only" required>
                                    <div class="flex items-center justify-center w-full p-4 border-2 border-gray-200 rounded-lg hover:border-green-500 transition-colors">
                                        <div class="text-center">
                                            <div class="text-2xl mb-2">👍</div>
                                            <div class="font-medium text-gray-900">Positif</div>
                                            <div class="text-sm text-gray-500">Feedback positif</div>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="relative flex cursor-pointer">
                                    <input type="radio" name="type" value="negatif" class="sr-only" required>
                                    <div class="flex items-center justify-center w-full p-4 border-2 border-gray-200 rounded-lg hover:border-red-500 transition-colors">
                                        <div class="text-center">
                                            <div class="text-2xl mb-2">👎</div>
                                            <div class="font-medium text-gray-900">Négatif</div>
                                            <div class="text-sm text-gray-500">Point à améliorer</div>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="relative flex cursor-pointer">
                                    <input type="radio" name="type" value="suggestion" class="sr-only" required>
                                    <div class="flex items-center justify-center w-full p-4 border-2 border-gray-200 rounded-lg hover:border-yellow-500 transition-colors">
                                        <div class="text-center">
                                            <div class="text-2xl mb-2">💡</div>
                                            <div class="font-medium text-gray-900">Suggestion</div>
                                            <div class="text-sm text-gray-500">Idée d'amélioration</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Priorité -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Priorité *</label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <label class="relative flex cursor-pointer">
                                    <input type="radio" name="priorite" value="faible" class="sr-only" required>
                                    <div class="flex items-center justify-center w-full p-4 border-2 border-gray-200 rounded-lg hover:border-green-500 transition-colors">
                                        <div class="text-center">
                                            <div class="text-2xl mb-2">🟢</div>
                                            <div class="font-medium text-gray-900">Faible</div>
                                            <div class="text-sm text-gray-500">Peu urgent</div>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="relative flex cursor-pointer">
                                    <input type="radio" name="priorite" value="moyenne" class="sr-only" required>
                                    <div class="flex items-center justify-center w-full p-4 border-2 border-gray-200 rounded-lg hover:border-yellow-500 transition-colors">
                                        <div class="text-center">
                                            <div class="text-2xl mb-2">🟡</div>
                                            <div class="font-medium text-gray-900">Moyenne</div>
                                            <div class="text-sm text-gray-500">À traiter</div>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="relative flex cursor-pointer">
                                    <input type="radio" name="priorite" value="elevee" class="sr-only" required>
                                    <div class="flex items-center justify-center w-full p-4 border-2 border-gray-200 rounded-lg hover:border-red-500 transition-colors">
                                        <div class="text-center">
                                            <div class="text-2xl mb-2">🔴</div>
                                            <div class="font-medium text-gray-900">Élevée</div>
                                            <div class="text-sm text-gray-500">Urgent</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @error('priorite')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Titre -->
                        <div>
                            <label for="titre" class="block text-sm font-medium text-gray-700 mb-2">
                                Titre de l'observation *
                            </label>
                            <input type="text" name="titre" id="titre" required
                                   class="w-full border-gray-300 rounded-md focus:border-[#0b5f37] focus:ring-[#0b5f37]"
                                   placeholder="Ex: Excellente gestion de la table 5"
                                   value="{{ old('titre') }}">
                            @error('titre')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Contenu -->
                        <div>
                            <label for="contenu" class="block text-sm font-medium text-gray-700 mb-2">
                                Contenu détaillé *
                            </label>
                            <textarea name="contenu" id="contenu" rows="6" required
                                      class="w-full border-gray-300 rounded-md focus:border-[#0b5f37] focus:ring-[#0b5f37]"
                                      placeholder="Décrivez en détail votre observation...">{{ old('contenu') }}</textarea>
                            @error('contenu')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                            <a href="{{ route('dashboard') }}" 
                               class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 transition-colors">
                                Annuler
                            </a>
                            <button type="submit" 
                                    class="bg-[#0b5f37] text-white px-6 py-2 rounded hover:bg-[#0a4d2c] transition-colors">
                                📝 Envoyer l'observation
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Gestion des radios custom
document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('input[type="radio"]');
    
    radios.forEach(radio => {
        // Déclencher le style pour les valeurs pré-remplies
        if (radio.checked) {
            updateRadioStyle(radio);
        }
        
        radio.addEventListener('change', function() {
            updateRadioStyle(this);
        });
    });
    
    function updateRadioStyle(radio) {
        // Retirer toutes les classes de style actives
        const allContainers = document.querySelectorAll('label > div');
        allContainers.forEach(container => {
            container.classList.remove('border-green-500', 'bg-green-50', 
                                     'border-red-500', 'bg-red-50',
                                     'border-yellow-500', 'bg-yellow-50',
                                     'border-blue-500', 'bg-blue-50');
        });
        
        // Appliquer le style au radio sélectionné
        if (radio.checked) {
            const container = radio.parentElement.querySelector('div');
            let colorClass = '';
            
            if (radio.name === 'type') {
                switch(radio.value) {
                    case 'positif':
                        colorClass = 'border-green-500 bg-green-50';
                        break;
                    case 'negatif':
                        colorClass = 'border-red-500 bg-red-50';
                        break;
                    case 'suggestion':
                        colorClass = 'border-yellow-500 bg-yellow-50';
                        break;
                }
            } else if (radio.name === 'priorite') {
                switch(radio.value) {
                    case 'faible':
                        colorClass = 'border-green-500 bg-green-50';
                        break;
                    case 'moyenne':
                        colorClass = 'border-yellow-500 bg-yellow-50';
                        break;
                    case 'elevee':
                        colorClass = 'border-red-500 bg-red-50';
                        break;
                }
            }
            
            if (colorClass) {
                container.classList.add(...colorClass.split(' '));
            }
        }
    }
});
</script>

<style>
/* Styles pour améliorer l'apparence des inputs */
input:focus, textarea:focus {
    outline: none;
    ring: 2px;
}

/* Animation douce pour les transitions */
label > div {
    transition: all 0.2s ease-in-out;
}
</style>
@endsection