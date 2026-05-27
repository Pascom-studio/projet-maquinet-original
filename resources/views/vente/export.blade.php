@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto">
        <!-- En-tête -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-[#0b5f37]">📤 Exporter les Ventes</h1>
            <a href="{{ route('ventes.historique') }}" class="text-[#0b5f37] hover:text-[#0a4d2c]">
                ← Retour à l'historique
            </a>
        </div>

        <!-- Formulaire d'export -->
        <div class="bg-white rounded-lg shadow p-6">
            <form action="{{ route('ventes.export') }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Type d'export -->
                    <div>
                        <label for="type_export" class="block text-sm font-medium text-gray-700 mb-2">
                            Type d'export
                        </label>
                        <select name="type_export" id="type_export" 
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37]">
                            <option value="ventes_detaillees">Ventes détaillées (avec produits)</option>
                            <option value="ventes_simplifiees">Ventes simplifiées (pour analyse)</option>
                        </select>
                    </div>

                    <!-- Utilisateur (seulement pour Admin/Gérant) -->
                    @if(Auth::user()->isAdmin() || Auth::user()->isGerant())
                    <div>
                        <label for="user_id_export" class="block text-sm font-medium text-gray-700 mb-2">
                            Utilisateur (optionnel)
                        </label>
                        <select name="user_id_export" id="user_id_export" 
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37]">
                            <option value="">Tous les utilisateurs</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->prenom }} {{ $user->name }} ({{ $user->fonction }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Date début -->
                    <div>
                        <label for="date_debut_export" class="block text-sm font-medium text-gray-700 mb-2">
                            Date début (optionnel)
                        </label>
                        <input type="date" name="date_debut_export" id="date_debut_export" 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37]">
                    </div>

                    <!-- Date fin -->
                    <div>
                        <label for="date_fin_export" class="block text-sm font-medium text-gray-700 mb-2">
                            Date fin (optionnel)
                        </label>
                        <input type="date" name="date_fin_export" id="date_fin_export" 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37]">
                    </div>
                </div>

                <!-- Informations -->
                <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <span class="text-blue-600 text-lg">💡</span>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Informations sur l'export</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>• Format : CSV (compatible Excel)</p>
                                <p>• Encodage : UTF-8</p>
                                <p>• Séparateur : Point-virgule (;)</p>
                                <p>• Les dates vides exporteront toutes les ventes</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Boutons -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('ventes.historique') }}" 
                       class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 transition duration-150">
                        Annuler
                    </a>
                    <button type="submit" 
                            class="bg-[#0b5f37] text-white px-6 py-2 rounded-md hover:bg-[#0a4d2c] transition duration-150 flex items-center">
                        <span class="mr-2">📥</span>
                        Télécharger l'export
                    </button>
                </div>
            </form>
        </div>

        <!-- Statistiques rapides -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="bg-blue-100 p-3 rounded-lg mr-4">
                        <span class="text-blue-600 text-xl">📈</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-blue-800">Total Ventes</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $stats['total_ventes'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="bg-green-100 p-3 rounded-lg mr-4">
                        <span class="text-green-600 text-xl">💰</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-green-800">Chiffre d'Affaires</p>
                        <p class="text-2xl font-bold text-green-600">
                            {{ number_format($stats['chiffre_affaires'] ?? 0, 0, ',', ' ') }} F
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="bg-purple-100 p-3 rounded-lg mr-4">
                        <span class="text-purple-600 text-xl">📊</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-purple-800">Vente Moyenne</p>
                        <p class="text-2xl font-bold text-purple-600">
                            {{ number_format($stats['vente_moyenne'] ?? 0, 0, ',', ' ') }} F
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Script pour limiter les dates
document.addEventListener('DOMContentLoaded', function() {
    const dateDebut = document.getElementById('date_debut_export');
    const dateFin = document.getElementById('date_fin_export');
    
    // Définir la date de début max à aujourd'hui
    const today = new Date().toISOString().split('T')[0];
    if (dateDebut) dateDebut.max = today;
    if (dateFin) dateFin.max = today;
    
    // Validation des dates
    if (dateDebut && dateFin) {
        dateDebut.addEventListener('change', function() {
            dateFin.min = this.value;
        });
        
        dateFin.addEventListener('change', function() {
            dateDebut.max = this.value;
        });
    }
});
</script>
@endsection