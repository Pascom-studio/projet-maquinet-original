@extends('layouts.app')

@section('content')
<div class="py-4 sm:py-6">
    <div class="max-w-4xl mx-auto px-2 sm:px-0">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#0b5f37] mb-4 sm:mb-6">Modifier la Vente #{{ $vente->numero_vente }}</h1>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <form action="{{ route('ventes.update', $vente->id) }}" method="POST" id="edit-vente-form">
                @csrf
                @method('PUT')
                
                <!-- Informations générales -->
                <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-gray-50 rounded">
                    <h3 class="font-semibold text-gray-700 mb-2 sm:mb-3 text-sm sm:text-base">Informations générales</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-4 text-xs sm:text-sm">
                        <p><span class="font-medium">Numéro:</span> {{ $vente->numero_vente }}</p>
                        <p><span class="font-medium">Date:</span> {{ ($vente->created_at ?? now())->format('d/m/Y H:i') }}</p>
                        <p><span class="font-medium">Vendeur:</span> 
                            @if($vente->user)
                                {{ $vente->user->prenom }} {{ $vente->user->nom }}
                            @else
                                Utilisateur inconnu
                            @endif
                        </p>
                        <p><span class="font-medium">Montant actuel:</span> {{ number_format($vente->montant_total, 0, ',', ' ') }} FCFA</p>
                    </div>
                </div>

                <!-- Produits de la vente -->
                <div class="mb-4 sm:mb-6">
                    <h3 class="font-semibold text-gray-700 mb-2 sm:mb-3 text-sm sm:text-base">Produits de la vente</h3>
                    
                    @if($vente->lignesVente->count() > 0)
                        @foreach($vente->lignesVente as $ligne)
                            <div class="mb-3 sm:mb-4 p-3 sm:p-4 bg-gray-50 rounded border">
                                <input type="hidden" name="lignes[{{ $loop->index }}][ligne_id]" value="{{ $ligne->id }}">
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-2 sm:mb-3">
                                    <div>
                                        <p class="text-xs sm:text-sm font-medium text-gray-700">Produit</p>
                                        @if($ligne->product)
                                            <p class="text-sm sm:text-base">{{ $ligne->product->designation }}</p>
                                        @else
                                            <p class="text-sm sm:text-base text-red-600">Produit supprimé</p>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-xs sm:text-sm font-medium text-gray-700">Prix unitaire</p>
                                        <p class="text-sm sm:text-base">{{ number_format($ligne->prix_unitaire, 0, ',', ' ') }} FCFA</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-2 sm:mb-3">
                                    <div>
                                        <label for="quantite_{{ $ligne->id }}" class="block text-xs sm:text-sm font-medium text-gray-700">Quantité *</label>
                                        <input type="number" 
                                               name="lignes[{{ $loop->index }}][quantite]" 
                                               id="quantite_{{ $ligne->id }}" 
                                               required 
                                               min="1" 
                                               @if($ligne->product)
                                               max="{{ $ligne->product->quantite + $ligne->quantite }}"
                                               @endif
                                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm sm:text-base focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37] quantite-input"
                                               value="{{ old('lignes.' . $loop->index . '.quantite', $ligne->quantite) }}"
                                               data-prix="{{ $ligne->prix_unitaire }}"
                                               data-ligne-id="{{ $ligne->id }}"
                                               @if(!$ligne->product) disabled @endif>
                                        @error('lignes.' . $loop->index . '.quantite')
                                            <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                        @if(!$ligne->product)
                                            <p class="text-red-500 text-xs sm:text-sm mt-1">Produit indisponible</p>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-xs sm:text-sm font-medium text-gray-700">Stock disponible</p>
                                        @if($ligne->product)
                                            <p class="text-sm sm:text-base {{ $ligne->product->quantite > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $ligne->product->quantite + $ligne->quantite }}
                                            </p>
                                        @else
                                            <p class="text-sm sm:text-base text-red-600">0</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-2">
                                    <p class="text-xs sm:text-sm font-medium text-gray-700">Montant de la ligne</p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm sm:text-base font-semibold text-[#0b5f37] montant-ligne" id="montant-ligne-{{ $ligne->id }}">
                                            {{ number_format($ligne->montant_total, 0, ',', ' ') }} FCFA
                                        </span>
                                        <span class="text-xs sm:text-sm text-gray-500 ancien-montant hidden sm:block" id="ancien-montant-{{ $ligne->id }}">
                                            Ancien: {{ number_format($ligne->montant_total, 0, ',', ' ') }} FCFA
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="p-3 sm:p-4 bg-red-50 rounded border border-red-200">
                            <p class="text-red-600 text-sm sm:text-base">⚠️ Aucun produit associé à cette vente</p>
                        </div>
                    @endif
                </div>

                <!-- Section Paiement -->
                <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-blue-50 rounded border border-blue-200">
                    <h3 class="font-semibold text-gray-700 mb-3 sm:mb-4 text-sm sm:text-base">💰 Informations de Paiement</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <!-- Argent remis par le client -->
                        <div>
                            <label for="cash_client" class="block text-xs sm:text-sm font-medium text-gray-700">
                                Argent remis *
                            </label>
                            <input type="number" 
                                   id="cash_client" 
                                   name="cash_client" 
                                   min="0" 
                                   step="1"
                                   required
                                   class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm sm:text-base focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37]"
                                   value="{{ old('cash_client', $vente->cash_client) }}"
                                   placeholder="Montant remis">
                            @error('cash_client')
                                <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Monnaie à rendre -->
                        <div>
                            <label for="monnaie" class="block text-xs sm:text-sm font-medium text-gray-700">
                                Monnaie à rendre *
                            </label>
                            <input type="number" 
                                   id="monnaie" 
                                   name="monnaie" 
                                   min="0" 
                                   step="1"
                                   required
                                   class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm sm:text-base focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37]"
                                   value="{{ old('monnaie', $vente->monnaie_rendue) }}"
                                   placeholder="Monnaie à rendre">
                            @error('monnaie')
                                <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Indicateurs de paiement -->
                    <div class="mt-3 sm:mt-4 space-y-2">
                        <div id="monnaie-section" class="hidden">
                            <div class="flex justify-between items-center p-2 sm:p-3 bg-green-50 rounded border border-green-200">
                                <span class="font-semibold text-green-800 text-xs sm:text-sm">Monnaie à rendre:</span>
                                <span id="monnaie-calcul" class="text-base sm:text-lg font-bold text-green-800">0 FCFA</span>
                            </div>
                        </div>

                        <div id="montant-insuffisant" class="hidden">
                            <div class="flex justify-between items-center p-2 sm:p-3 bg-red-50 rounded border border-red-200">
                                <span class="font-semibold text-red-800 text-xs sm:text-sm">Montant insuffisant:</span>
                                <span id="manquant" class="text-base sm:text-lg font-bold text-red-800">0 FCFA</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-4 sm:mb-6">
                    <label for="notes" class="block text-xs sm:text-sm font-medium text-gray-700">Notes (optionnel)</label>
                    <textarea name="notes" id="notes" rows="2"
                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm sm:text-base focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37]">{{ old('notes', $vente->notes) }}</textarea>
                    @error('notes')
                        <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Résumé du nouveau montant -->
                <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-blue-50 rounded border border-blue-200">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-2 sm:space-y-0">
                        <div>
                            <p class="text-xs sm:text-sm font-medium text-gray-700">Montant actuel</p>
                            <p class="text-sm sm:text-base text-gray-600">{{ number_format($vente->montant_total, 0, ',', ' ') }} FCFA</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs sm:text-sm font-medium text-gray-700">Nouveau montant</p>
                            <p class="text-lg sm:text-2xl font-bold text-[#0b5f37]" id="nouveau-montant-total">
                                {{ number_format($vente->montant_total, 0, ',', ' ') }} FCFA
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3">
                    <a href="{{ route('ventes.show', $vente->id) }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition duration-200 text-sm sm:text-base text-center">
                        Annuler
                    </a>
                    <button type="submit" id="submit-btn" class="bg-[#0b5f37] text-white px-4 sm:px-6 py-2 rounded hover:bg-[#0a4d2c] transition duration-200 text-sm sm:text-base">
                        Modifier la Vente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputsQuantite = document.querySelectorAll('.quantite-input');
    const nouveauMontantTotal = document.getElementById('nouveau-montant-total');
    const cashClientInput = document.getElementById('cash_client');
    const monnaieInput = document.getElementById('monnaie');
    const monnaieSection = document.getElementById('monnaie-section');
    const montantInsuffisantSection = document.getElementById('montant-insuffisant');
    const monnaieCalcul = document.getElementById('monnaie-calcul');
    const manquantElement = document.getElementById('manquant');
    const submitBtn = document.getElementById('submit-btn');
    const form = document.getElementById('edit-vente-form');

    let nouveauTotal = parseFloat('{{ $vente->montant_total }}');

    function calculerNouveauMontantTotal() {
        nouveauTotal = 0;
        
        inputsQuantite.forEach(input => {
            if (!input.disabled) {
                const quantite = parseInt(input.value) || 0;
                const prix = parseFloat(input.dataset.prix);
                const ligneId = input.dataset.ligneId;
                
                const montantLigne = quantite * prix;
                nouveauTotal += montantLigne;
                
                // Mettre à jour le montant de la ligne
                const montantLigneElement = document.getElementById(`montant-ligne-${ligneId}`);
                const ancienMontantElement = document.getElementById(`ancien-montant-${ligneId}`);
                
                if (montantLigneElement) {
                    montantLigneElement.textContent = new Intl.NumberFormat().format(montantLigne) + ' FCFA';
                }
                
                // Changer la couleur si le montant a changé
                if (ancienMontantElement) {
                    const ancienMontant = parseFloat(ancienMontantElement.textContent.replace(/[^0-9]/g, ''));
                    if (montantLigne !== ancienMontant) {
                        montantLigneElement.classList.add('text-blue-600');
                        montantLigneElement.classList.remove('text-[#0b5f37]');
                    } else {
                        montantLigneElement.classList.remove('text-blue-600');
                        montantLigneElement.classList.add('text-[#0b5f37]');
                    }
                }
            }
        });
        
        // Mettre à jour le montant total
        if (nouveauMontantTotal) {
            nouveauMontantTotal.textContent = new Intl.NumberFormat().format(nouveauTotal) + ' FCFA';
            
            // Changer la couleur si le total a changé
            const ancienTotal = parseFloat('{{ $vente->montant_total }}');
            if (nouveauTotal !== ancienTotal) {
                nouveauMontantTotal.classList.add('text-blue-600');
                nouveauMontantTotal.classList.remove('text-[#0b5f37]');
            } else {
                nouveauMontantTotal.classList.remove('text-blue-600');
                nouveauMontantTotal.classList.add('text-[#0b5f37]');
            }
        }

        // Mettre à jour le calcul de la monnaie
        updateMonnaie();
    }

    function updateMonnaie() {
        const cashClient = parseFloat(cashClientInput.value) || 0;
        const difference = cashClient - nouveauTotal;
        
        // Cacher toutes les sections d'abord
        monnaieSection.classList.add('hidden');
        montantInsuffisantSection.classList.add('hidden');
        
        if (cashClient > 0 && nouveauTotal > 0) {
            if (difference >= 0) {
                // Monnaie à rendre
                monnaieSection.classList.remove('hidden');
                monnaieCalcul.textContent = new Intl.NumberFormat().format(difference) + ' FCFA';
                
                // Mettre à jour automatiquement le champ monnaie
                monnaieInput.value = difference;
            } else {
                // Montant insuffisant
                montantInsuffisantSection.classList.remove('hidden');
                manquantElement.textContent = new Intl.NumberFormat().format(Math.abs(difference)) + ' FCFA';
                
                // Réinitialiser le champ monnaie
                monnaieInput.value = 0;
            }
        } else {
            monnaieInput.value = 0;
        }

        // Activer/désactiver le bouton de soumission
        updateSubmitButton();
    }

    function updateSubmitButton() {
        const cashClient = parseFloat(cashClientInput.value) || 0;
        const peutEnregistrer = cashClient >= nouveauTotal;
        
        submitBtn.disabled = !peutEnregistrer;
        submitBtn.classList.toggle('opacity-50', !peutEnregistrer);
        submitBtn.classList.toggle('cursor-not-allowed', !peutEnregistrer);
        submitBtn.classList.toggle('hover:bg-[#0a4d2c]', peutEnregistrer);
    }

    // Écouter les changements sur tous les inputs de quantité
    inputsQuantite.forEach(input => {
        if (!input.disabled) {
            input.addEventListener('input', calculerNouveauMontantTotal);
        }
    });

    // Écouter les changements sur le cash client
    cashClientInput.addEventListener('input', updateMonnaie);

    // Formater le cash client lors de la perte de focus
    cashClientInput.addEventListener('blur', function() {
        if (this.value) {
            this.value = parseFloat(this.value).toFixed(0);
            updateMonnaie();
        }
    });

    // Validation du formulaire
    form.addEventListener('submit', function(e) {
        const cashClient = parseFloat(cashClientInput.value) || 0;
        
        if (cashClient < nouveauTotal) {
            e.preventDefault();
            alert('Le montant remis est insuffisant pour le nouveau total.');
            return;
        }

        // Vérifier que la monnaie correspond
        const monnaieCalculee = cashClient - nouveauTotal;
        const monnaieSaisie = parseFloat(monnaieInput.value) || 0;
        
        if (monnaieCalculee !== monnaieSaisie) {
            e.preventDefault();
            alert('La monnaie à rendre ne correspond pas au calcul.');
            return;
        }
    });

    // Calcul initial
    calculerNouveauMontantTotal();
});
</script>

<style>
/* Empêcher le zoom sur mobile */
@media (max-width: 768px) {
    input, select, textarea {
        font-size: 16px !important;
    }
}

/* Amélioration du responsive */
@media (max-width: 640px) {
    .min-w-0 {
        min-width: 0;
    }
}
</style>
@endsection