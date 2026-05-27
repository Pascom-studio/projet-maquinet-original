@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- En-tête -->
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-[#0b5f37]">Solder la Commande</h2>
                    <p class="text-gray-600">Numéro: {{ $commande->numero_commande }}</p>
                </div>

                <!-- Détails de la commande -->
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <h3 class="text-lg font-semibold mb-3">Détails de la commande</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p><strong>Table:</strong> {{ $commande->table->nom_complet ?? $commande->table->nom }}</p>
                            <p><strong>Hôtesse:</strong> {{ $commande->hotesse->prenom }} {{ $commande->hotesse->name }}</p>
                            <p><strong>Caissier:</strong> {{ $commande->user->prenom }} {{ $commande->user->name }}</p>
                        </div>
                        <div>
                            <p><strong>Date:</strong> {{ $commande->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>Statut:</strong> 
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    {{ ucfirst($commande->statut) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <!-- Produits de la commande -->
                    <div class="mt-4">
                        <h4 class="font-semibold mb-2">Produits commandés:</h4>
                        <div class="space-y-2">
                            @foreach($commande->produits as $produit)
                            <div class="flex justify-between text-sm">
                                <span>{{ $produit->quantite }} x {{ $produit->produit->designation }}</span>
                                <span>{{ number_format($produit->prix_total, 0, ',', ' ') }} FCFA</span>
                            </div>
                            @endforeach
                        </div>
                        <div class="border-t mt-2 pt-2 font-semibold">
                            <div class="flex justify-between">
                                <span>Total à payer:</span>
                                <span class="text-lg text-[#0b5f37]">{{ number_format($commande->montant, 0, ',', ' ') }} FCFA</span>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('commandes.solder', $commande) }}" method="POST" id="solderForm">
                    @csrf
                    
                    <div class="space-y-4">
                        <!-- Description -->
                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description (optionnel)</label>
                            <textarea name="description" id="description" class="w-full border-gray-300 rounded-md shadow-sm focus:border-[#0b5f37] focus:ring-[#0b5f37]" rows="4" placeholder="Description détaillée de la commande...">{{ old('description', $commande->description) }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Maximum 1000 caractères</p>
                        </div>

                        <!-- Méthode de paiement -->
                        <div>
                            <label for="methode_paiement" class="block text-sm font-medium text-gray-700 mb-1">
                                Méthode de paiement *
                            </label>
                            <select name="methode_paiement" id="methode_paiement" required
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37]">
                                <option value="">Choisir une méthode</option>
                                <option value="espece" {{ old('methode_paiement') == 'espece' ? 'selected' : '' }}>Espèces</option>
                                <option value="carte" {{ old('methode_paiement') == 'carte' ? 'selected' : '' }}>Carte bancaire</option>
                                <option value="cheque" {{ old('methode_paiement') == 'cheque' ? 'selected' : '' }}>Chèque</option>
                                <option value="virement" {{ old('methode_paiement') == 'virement' ? 'selected' : '' }}>Virement</option>
                            </select>
                        </div>

                        <!-- Montant remis -->
                        <div>
                            <label for="montant_remis" class="block text-sm font-medium text-gray-700 mb-1">
                                Montant remis par le client *
                            </label>
                            <input type="number" 
                                   name="montant_remis" 
                                   id="montant_remis" 
                                   min="{{ $commande->montant }}"
                                   step="1"
                                   required
                                   value="{{ old('montant_remis') }}"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0b5f37] focus:border-[#0b5f37]"
                                   placeholder="Entrez le montant remis">
                            <p class="text-xs text-gray-500 mt-1">
                                Minimum: {{ number_format($commande->montant, 0, ',', ' ') }} FCFA
                            </p>
                            @error('montant_remis')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Montant de la commande (affichage seulement) -->
                        <div class="bg-gray-50 p-3 rounded border border-gray-200">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Montant de la commande</label>
                            <p class="text-lg font-semibold text-gray-900">{{ number_format($commande->montant, 0, ',', ' ') }} FCFA</p>
                        </div>

                        <!-- Monnaie à rendre (calcul automatique) -->
                        <div id="monnaie_container" class="bg-green-50 p-3 rounded border border-green-200 hidden">
                            <label class="block text-sm font-medium text-green-700 mb-1">Monnaie à rendre</label>
                            <p id="monnaie_a_rendre" class="text-lg font-semibold text-green-600">0 FCFA</p>
                            <input type="hidden" name="monnaie_rendue" id="monnaie_rendue" value="0">
                        </div>

                        <!-- Message d'erreur si montant insuffisant -->
                        <div id="erreur_montant" class="hidden bg-red-50 p-3 rounded border border-red-200">
                            <p class="text-red-700 text-sm font-medium">
                                ❌ Montant insuffisant. Le montant remis doit être supérieur ou égal au montant de la commande.
                            </p>
                        </div>

                        <!-- Validation message -->
                        <div id="validation_message" class="hidden"></div>
                    </div>

                    <!-- Boutons -->
                    <div class="flex justify-end space-x-3 mt-6 pt-6 border-t">
                        <a href="{{ route('commandes.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 text-sm">
                            Annuler
                        </a>
                        <button type="submit" 
                                id="btn_solder" 
                                class="bg-[#0b5f37] text-white px-6 py-2 rounded hover:bg-[#0a4d2c] text-sm">
                            ✅ Soldée la Commande
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const montantRemisInput = document.getElementById('montant_remis');
    const monnaieContainer = document.getElementById('monnaie_container');
    const monnaieARendre = document.getElementById('monnaie_a_rendre');
    const monnaieRendueInput = document.getElementById('monnaie_rendue');
    const erreurMontant = document.getElementById('erreur_montant');
    const btnSolder = document.getElementById('btn_solder');
    const validationMessage = document.getElementById('validation_message');
    const montantCommande = {{ $commande->montant }};

    function calculerMonnaie() {
        const montantRemis = parseFloat(montantRemisInput.value) || 0;
        const monnaie = montantRemis - montantCommande;
        
        if (montantRemis >= montantCommande) {
            // Montant suffisant
            monnaieARendre.textContent = new Intl.NumberFormat('fr-FR').format(monnaie) + ' FCFA';
            monnaieRendueInput.value = monnaie;
            monnaieContainer.classList.remove('hidden');
            erreurMontant.classList.add('hidden');
            validationMessage.classList.add('hidden');
            
            btnSolder.disabled = false;
            btnSolder.classList.remove('bg-gray-400', 'cursor-not-allowed');
            btnSolder.classList.add('bg-[#0b5f37]', 'hover:bg-[#0a4d2c]');
        } else if (montantRemis > 0) {
            // Montant insuffisant
            monnaieContainer.classList.add('hidden');
            erreurMontant.classList.remove('hidden');
            validationMessage.classList.add('hidden');
            
            btnSolder.disabled = true;
            btnSolder.classList.add('bg-gray-400', 'cursor-not-allowed');
            btnSolder.classList.remove('bg-[#0b5f37]', 'hover:bg-[#0a4d2c]');
        } else {
            // Aucun montant saisi
            monnaieContainer.classList.add('hidden');
            erreurMontant.classList.add('hidden');
            validationMessage.classList.add('hidden');
            
            btnSolder.disabled = false;
            btnSolder.classList.remove('bg-gray-400', 'cursor-not-allowed');
            btnSolder.classList.add('bg-[#0b5f37]', 'hover:bg-[#0a4d2c]');
        }
    }

    function validerMontant() {
        const montantRemis = parseFloat(montantRemisInput.value) || 0;
        
        if (montantRemis < montantCommande) {
            validationMessage.className = 'bg-red-50 p-3 rounded border border-red-200';
            validationMessage.innerHTML = `
                <p class="text-red-700 text-sm font-medium">
                    ❌ Le montant remis (${montantRemis.toLocaleString()} FCFA) est inférieur au montant de la commande (${montantCommande.toLocaleString()} FCFA).
                </p>
            `;
            validationMessage.classList.remove('hidden');
            return false;
        } else {
            validationMessage.classList.add('hidden');
            return true;
        }
    }

    // Événements
    montantRemisInput.addEventListener('input', calculerMonnaie);
    montantRemisInput.addEventListener('change', calculerMonnaie);
    
    // Validation avant soumission
    document.getElementById('solderForm').addEventListener('submit', function(e) {
        if (!validerMontant()) {
            e.preventDefault();
            montantRemisInput.focus();
        }
    });
    
    // Focus sur le champ montant remis
    montantRemisInput.focus();

    // Permettre la saisie de n'importe quel montant
    montantRemisInput.addEventListener('keydown', function(e) {
        // Autoriser tous les chiffres et touches de contrôle
        if (!/[\d\.]|Backspace|Delete|ArrowLeft|ArrowRight|Tab/.test(e.key)) {
            e.preventDefault();
        }
    });
});
</script>

<style>
#montant_remis:invalid {
    border-color: #ef4444;
    background-color: #fef2f2;
}

#montant_remis:valid {
    border-color: #10b981;
    background-color: #f0fdf4;
}

.btn-disabled {
    background-color: #9ca3af !important;
    cursor: not-allowed !important;
}

.btn-disabled:hover {
    background-color: #9ca3af !important;
}
</style>
@endsection