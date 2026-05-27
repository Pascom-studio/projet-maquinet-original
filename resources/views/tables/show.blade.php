@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- En-tête -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-[#0b5f37]">{{ $table->nom_complet }}</h2>
                        <p class="text-gray-600">Détails de la table</p>
                    </div>
                    <a href="{{ route('tables.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">
                        ← Retour
                    </a>
                </div>

                <!-- Informations de la table -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Informations</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Numéro:</dt>
                                <dd class="text-sm text-gray-900">#{{ $table->numero }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Nom:</dt>
                                <dd class="text-sm text-gray-900">{{ $table->nom }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Statut:</dt>
                                <dd>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $table->estAffectee() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $table->statut_formatted }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Hôtesse:</dt>
                                <dd class="text-sm text-gray-900">
                                    @if($table->estAffectee())
                                        {{ $table->user->prenom }} {{ $table->user->nom }}
                                    @else
                                        <span class="text-gray-400">Non affectée</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Actions -->
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Actions</h3>
                        <div class="space-y-3">
                            @if($table->estAffectee())
                                <form action="{{ route('tables.liberer', $table) }}" method="POST" class="inline w-full">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 text-sm flex items-center justify-center"
                                            onclick="return confirm('Libérer cette table ?')">
                                        🔓 Libérer la table
                                    </button>
                                </form>
                            @else
                                <button type="button" 
                                        onclick="afficherModalAffectation({{ $table->id }})"
                                        class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 text-sm flex items-center justify-center">
                                    👩‍💼 Affecter à une hôtesse
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Commandes en cours -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Commandes en Cours</h3>
                    @if($table->commandesEnCours->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Commande</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produits</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($table->commandesEnCours as $commande)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $commande->numero_commande }}
                                            </td>
                                            <td class="px-4 py-4 text-sm text-gray-500">
                                                {{ $commande->produits->count() }} produit(s)
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ number_format($commande->montant_total, 0, ',', ' ') }} F
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $commande->created_at->format('d/m/Y H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8 bg-gray-50 rounded-lg">
                            <div class="text-gray-400 text-4xl mb-2">📋</div>
                            <p class="text-gray-500">Aucune commande en cours pour cette table</p>
                        </div>
                    @endif
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
                        @foreach($hotesses as $serveuse)
                            <option value="{{ $serveuse->id }}">
                                {{ $serveuse->prenom }} {{ $serveuse->nom }}
                            </option>
                        @endforeach
                    </select>
                    @if($hotesses->isEmpty())
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
    
    // CORRECTION : Utiliser la même méthode que dans index.blade.php
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