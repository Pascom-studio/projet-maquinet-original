@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- En-tête -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-[#0b5f37]">Gestion des Tables</h2>
                        <p class="text-gray-600">Gérez l'affectation des tables aux hôtesses</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('tables.create') }}" class="bg-[#0b5f37] text-white px-4 py-2 rounded hover:bg-[#0a4d2c] text-sm">
                            + Nouvelle Table
                        </a>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-semibold text-blue-800">Total Tables</h3>
                                <p class="text-2xl font-bold text-blue-600">{{ $stats['total_tables'] }}</p>
                            </div>
                            <div class="text-2xl">🏪</div>
                        </div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-semibold text-green-800">Tables Affectées</h3>
                                <p class="text-2xl font-bold text-green-600">{{ $stats['tables_affectees'] }}</p>
                            </div>
                            <div class="text-2xl">👩‍💼</div>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Tables Libres</h3>
                                <p class="text-2xl font-bold text-gray-600">{{ $stats['tables_libres'] }}</p>
                            </div>
                            <div class="text-2xl">🆓</div>
                        </div>
                    </div>
                </div>

                <!-- Liste des tables -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Table
                                </th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Statut
                                </th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Hôtesse Affectée
                                </th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Commandes en Cours
                                </th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($tables as $table)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $table->nom_complet }}</div>
                                        <div class="text-xs text-gray-500">#{{ $table->numero }}</div>
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $table->estAffectee() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $table->statut_formatted }}
                                        </span>
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($table->estAffectee())
                                            {{ $table->user->prenom }} {{ $table->user->name }}
                                        @else
                                            <span class="text-gray-400">Non affectée</span>
                                        @endif
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $table->commandesEnCours->count() }}
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            @if($table->estAffectee())
                                                <!-- Formulaire de libération -->
                                                <form action="{{ route('tables.liberer', $table) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('POST')
                                                    <button type="submit" class="text-yellow-600 hover:text-yellow-900" 
                                                            onclick="return confirm('Libérer cette table ?')"
                                                            title="Libérer la table">
                                                        🔓
                                                    </button>
                                                </form>
                                            @else
                                                <!-- Bouton d'affectation -->
                                                <button type="button" 
                                                        onclick="afficherModalAffectation({{ $table->id }})"
                                                        class="text-blue-600 hover:text-blue-900"
                                                        title="Affecter à une hôtesse">
                                                    👩‍💼
                                                </button>
                                            @endif

                                            <!-- Bouton édition -->
                                            <a href="{{ route('tables.edit', $table) }}" 
                                               class="text-green-600 hover:text-green-900"
                                               title="Modifier">
                                                ✏️
                                            </a>

                                            <!-- Bouton suppression -->
                                            <form action="{{ route('tables.destroy', $table) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900"
                                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette table ?')"
                                                        title="Supprimer">
                                                    🗑️
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 sm:px-6 py-4 text-center text-sm text-gray-500">
                                        Aucune table trouvée
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'affectation -->
<div id="modal-affectation" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Affecter la table</h3>
            
            <form id="form-affectation" method="POST">
                @csrf
                <input type="hidden" name="table_id" id="table_id_modal">
                
                <div class="mb-4">
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Sélectionner une hôtesse
                    </label>
                    <select name="user_id" id="user_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-[#0b5f37] focus:ring-[#0b5f37]" required>
                        <option value="">Choisir une hôtesse</option>
                        @foreach($serveuses as $serveuse)
                            <option value="{{ $serveuse->id }}">
                                {{ $serveuse->prenom }} {{ $serveuse->name }}
                            </option>
                        @endforeach
                    </select>
                    @if($serveuses->isEmpty())
                        <p class="mt-2 text-sm text-yellow-600">
                            Aucune hôtesse disponible pour l'affectation.
                        </p>
                    @endif
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="fermerModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Annuler
                    </button>
                    <button type="submit" class="bg-[#0b5f37] text-white px-4 py-2 rounded hover:bg-[#0a4d2c]">
                        Affecter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function afficherModalAffectation(tableId) {
    document.getElementById('table_id_modal').value = tableId;
    document.getElementById('modal-affectation').classList.remove('hidden');
}

function fermerModal() {
    document.getElementById('modal-affectation').classList.add('hidden');
    document.getElementById('user_id').value = '';
}

// Fermer la modal en cliquant à l'extérieur
document.getElementById('modal-affectation').addEventListener('click', function(e) {
    if (e.target.id === 'modal-affectation') {
        fermerModal();
    }
});

// Gérer la soumission du formulaire d'affectation
document.getElementById('form-affectation').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // CORRECTION : Utiliser la route nommée avec fetch
    fetch('{{ route("tables.affecter") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur réseau');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload(); // Recharger la page pour voir les changements
        } else {
            alert('Erreur: ' + (data.error || data.message || 'Erreur inconnue'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur lors de l\'affectation: ' + error.message);
    })
    .finally(() => {
        fermerModal();
    });
});

// Messages de confirmation améliorés
document.addEventListener('DOMContentLoaded', function() {
    // Confirmation pour la libération de table
    const formsLiberer = document.querySelectorAll('form[action*="liberer"]');
    formsLiberer.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir libérer cette table ?')) {
                e.preventDefault();
            }
        });
    });

    // Confirmation pour la suppression de table
    const formsSupprimer = document.querySelectorAll('form[action*="destroy"]');
    formsSupprimer.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette table ? Cette action est irréversible.')) {
                e.preventDefault();
            }
        });
    });
});
</script>

<!-- Affichage des messages de session -->
@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            alert('{{ session('success') }}');
        });
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            alert('Erreur: {{ session('error') }}');
        });
    </script>
@endif
@endsection