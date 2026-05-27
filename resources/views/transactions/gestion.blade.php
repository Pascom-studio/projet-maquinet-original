@extends('layouts.app')

@section('content')
<div class="py-4 sm:py-6">
    <div class="max-w-7xl mx-auto px-2 sm:px-0">
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-[#0b5f37]">📦 Gestion des fonds Mobile Money</h1>
            <p class="text-gray-600">Gérez les approvisionnements et remboursements de vos comptes Mobile Money</p>
        </div>

        <!-- Cartes des soldes -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            @foreach($soldes as $operateur => $data)
            <div class="bg-white rounded-lg shadow border p-4 text-center transform hover:scale-105 transition duration-200">
                <div class="text-lg font-semibold text-gray-700 mb-2">
                    {{ $data['nom'] }}
                </div>
                <div class="text-2xl font-bold {{ $data['solde'] >= 0 ? 'text-green-600' : 'text-red-600' }} mb-2">
                    {{ number_format($data['solde'], 0, ',', ' ') }} F
                </div>
                <div class="text-xs text-gray-500 space-y-1">
                    <div class="flex justify-between">
                        <span>Dépôts:</span>
                        <span class="font-medium">{{ number_format($data['depots'], 0, ',', ' ') }} F</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Retraits:</span>
                        <span class="font-medium">{{ number_format($data['retraits'], 0, ',', ' ') }} F</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Appro.:</span>
                        <span class="font-medium text-green-600">{{ number_format($data['approvisionnements'], 0, ',', ' ') }} F</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Remb.:</span>
                        <span class="font-medium text-red-600">{{ number_format($data['remboursements'], 0, ',', ' ') }} F</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Carte Liquidité -->
        <div class="bg-white rounded-lg shadow border p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                        <span class="text-2xl text-purple-600">💵</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Liquidité Disponible</h3>
                        <p class="text-gray-600">Fonds disponibles pour les opérations</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold text-purple-600">
                        {{ number_format($liquidite, 0, ',', ' ') }} F
                    </div>
                    <div class="text-sm text-gray-500">Solde actuel</div>
                </div>
            </div>
        </div>

        <!-- Nouvelle section pour Avoir et Dépense -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Carte Avoir - Augmente la liquidité -->
            <div class="bg-white rounded-lg shadow border p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                        <span class="text-2xl text-blue-600">💎</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Avoir</h3>
                        <p class="text-gray-600">Augmenter la liquidité globale</p>
                    </div>
                </div>

                <form action="{{ route('mobile-money.ajouter-avoir') }}" method="POST" id="formAvoir">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="avoir_montant" class="block text-sm font-medium text-gray-700 mb-1">Montant (FCFA) *</label>
                            <input type="number" name="montant" id="avoir_montant" required min="0.01" step="0.01"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="0.00">
                        </div>

                        <div>
                            <label for="avoir_reference" class="block text-sm font-medium text-gray-700 mb-1">Référence *</label>
                            <input type="text" name="reference" id="avoir_reference" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Référence de l'opération">
                        </div>

                        <div>
                            <label for="avoir_notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea name="notes" id="avoir_notes" rows="2"
                                      class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Notes supplémentaires"></textarea>
                        </div>

                        <button type="submit"
                                class="w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 font-semibold transition duration-200 flex items-center justify-center">
                            <span class="mr-2">➕</span>
                            Ajouter à l'avoir
                        </button>
                    </div>
                </form>
            </div>

            <!-- Carte Dépense - Réduit le solde d'un opérateur -->
            <div class="bg-white rounded-lg shadow border p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mr-4">
                        <span class="text-2xl text-orange-600">💸</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Dépense</h3>
                        <p class="text-gray-600">Effectuer une dépense sur un opérateur ou la liquidité</p>
                    </div>
                </div>

                <form action="{{ route('mobile-money.effectuer-depense') }}" method="POST" id="formDepense">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="depense_operateur" class="block text-sm font-medium text-gray-700 mb-1">Opérateur *</label>
                            <select name="operateur" id="depense_operateur" required
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                <option value="">Sélectionnez l'opérateur</option>
                                <option value="orange_money">Orange Money</option>
                                <option value="telecel_money">Telecel Money</option>
                                <option value="moov_money">Moov Money</option>
                                <option value="coris_money">Coris Money</option>
                                <option value="liquidite">Liquidité</option>
                            </select>
                        </div>

                        <div>
                            <label for="depense_montant" class="block text-sm font-medium text-gray-700 mb-1">Montant (FCFA) *</label>
                            <input type="number" name="montant" id="depense_montant" required min="0.01" step="0.01"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                   placeholder="0.00">
                        </div>

                        <div>
                            <label for="depense_reference" class="block text-sm font-medium text-gray-700 mb-1">Référence *</label>
                            <input type="text" name="reference" id="depense_reference" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                   placeholder="Référence de l'opération">
                        </div>

                        <div>
                            <label for="depense_notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea name="notes" id="depense_notes" rows="2"
                                      class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                      placeholder="Description de la dépense"></textarea>
                        </div>

                        <button type="submit"
                                class="w-full bg-orange-600 text-white py-3 px-4 rounded-md hover:bg-orange-700 font-semibold transition duration-200 flex items-center justify-center">
                            <span class="mr-2">➖</span>
                            Effectuer la dépense
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Cartes d'actions existantes -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Carte Approvisionnement existante -->
            <div class="bg-white rounded-lg shadow border p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                        <span class="text-2xl text-green-600">💰</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Approvisionnement</h3>
                        <p class="text-gray-600">Ajouter des fonds à un compte</p>
                    </div>
                </div>

                <form action="{{ route('mobile-money.approvisionner') }}" method="POST" id="formApprovisionnement">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="appro_operateur" class="block text-sm font-medium text-gray-700 mb-1">Opérateur *</label>
                            <select name="operateur" id="appro_operateur" required
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">Sélectionnez l'opérateur</option>
                                <option value="orange_money">Orange Money</option>
                                <option value="telecel_money">Telecel Money</option>
                                <option value="moov_money">Moov Money</option>
                                <option value="coris_money">Coris Money</option>
                            </select>
                        </div>

                        <div>
                            <label for="appro_montant" class="block text-sm font-medium text-gray-700 mb-1">Montant (FCFA) *</label>
                            <input type="number" name="montant" id="appro_montant" required min="0.01" step="0.01"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="0.00">
                        </div>

                        <div>
                            <label for="appro_reference" class="block text-sm font-medium text-gray-700 mb-1">Référence *</label>
                            <input type="text" name="reference" id="appro_reference" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="Référence de l'opération">
                        </div>

                        <div>
                            <label for="appro_notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea name="notes" id="appro_notes" rows="2"
                                      class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                      placeholder="Notes supplémentaires"></textarea>
                        </div>

                        <button type="submit"
                                class="w-full bg-green-600 text-white py-3 px-4 rounded-md hover:bg-green-700 font-semibold transition duration-200 flex items-center justify-center">
                            <span class="mr-2">💸</span>
                            Approvisionner le compte
                        </button>
                    </div>
                </form>
            </div>

            <!-- Carte Remboursement existante -->
            <div class="bg-white rounded-lg shadow border p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                        <span class="text-2xl text-red-600">💳</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Remboursement</h3>
                        <p class="text-gray-600">Retirer des fonds d'un compte ou de la liquidité</p>
                    </div>
                </div>

                <form action="{{ route('mobile-money.rembourser') }}" method="POST" id="formRemboursement">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="remb_operateur" class="block text-sm font-medium text-gray-700 mb-1">Opérateur *</label>
                            <select name="operateur" id="remb_operateur" required
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Sélectionnez l'opérateur</option>
                                <option value="orange_money">Orange Money</option>
                                <option value="telecel_money">Telecel Money</option>
                                <option value="moov_money">Moov Money</option>
                                <option value="coris_money">Coris Money</option>
                                <option value="liquidite">Liquidité</option>
                            </select>
                        </div>

                        <div>
                            <label for="remb_montant" class="block text-sm font-medium text-gray-700 mb-1">Montant (FCFA) *</label>
                            <input type="number" name="montant" id="remb_montant" required min="0.01" step="0.01"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                   placeholder="0.00">
                        </div>

                        <div>
                            <label for="remb_reference" class="block text-sm font-medium text-gray-700 mb-1">Référence *</label>
                            <input type="text" name="reference" id="remb_reference" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                   placeholder="Référence de l'opération">
                        </div>

                        <div>
                            <label for="remb_notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea name="notes" id="remb_notes" rows="2"
                                      class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                      placeholder="Notes supplémentaires"></textarea>
                        </div>

                        <button type="submit"
                                class="w-full bg-red-600 text-white py-3 px-4 rounded-md hover:bg-red-700 font-semibold transition duration-200 flex items-center justify-center">
                            <span class="mr-2">↩️</span>
                            Effectuer le remboursement
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Résumé global -->
        <div class="bg-white rounded-lg shadow border p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">📊 Résumé Global</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ number_format($solde_total, 0, ',', ' ') }} F</div>
                    <div class="text-sm text-gray-600">Solde Total</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ number_format(collect($soldes)->sum('solde'), 0, ',', ' ') }} F</div>
                    <div class="text-sm text-gray-600">Solde Opérateurs</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ number_format($liquidite, 0, ',', ' ') }} F</div>
                    <div class="text-sm text-gray-600">Liquidité</div>
                </div>
            </div>
        </div>

        <!-- Historique des mouvements -->
        <div class="bg-white rounded-lg shadow border">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-800">📊 Historique des Mouvements</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Opérateur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Référence</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($mouvements as $mouvement)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $mouvement->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $mouvement->type_mouvement == 'approvisionnement' ? 'bg-green-100 text-green-800' : 
                                       ($mouvement->type_mouvement == 'remboursement' ? 'bg-red-100 text-red-800' :
                                       ($mouvement->type_mouvement == 'avoir' ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800')) }}">
                                    @if($mouvement->type_mouvement == 'approvisionnement')
                                        Approvisionnement
                                    @elseif($mouvement->type_mouvement == 'remboursement')
                                        Remboursement
                                    @elseif($mouvement->type_mouvement == 'avoir')
                                        Avoir
                                    @else
                                        Dépense
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($mouvement->operateur == 'liquidite')
                                    Liquidité
                                @elseif($mouvement->operateur == 'orange_money')
                                    Orange Money
                                @elseif($mouvement->operateur == 'telecel_money')
                                    Telecel Money
                                @elseif($mouvement->operateur == 'moov_money')
                                    Moov Money
                                @elseif($mouvement->operateur == 'coris_money')
                                    Coris Money
                                @else
                                    {{ $mouvement->operateur }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold 
                                {{ $mouvement->type_mouvement == 'approvisionnement' || $mouvement->type_mouvement == 'avoir' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $mouvement->type_mouvement == 'approvisionnement' || $mouvement->type_mouvement == 'avoir' ? '+' : '-' }}{{ number_format($mouvement->montant, 0, ',', ' ') }} F
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $mouvement->reference }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $mouvement->notes ?? '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                Aucun mouvement de stock enregistré
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">
                {{ $mouvements->links() }}
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Générer des références automatiques
    function generateReference(prefix) {
        const now = new Date();
        const timestamp = now.getTime().toString().slice(-6);
        const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        return prefix + timestamp + random;
    }

    // Générer références automatiques
    document.getElementById('appro_reference').value = generateReference('APPRO_');
    document.getElementById('remb_reference').value = generateReference('REMB_');
    document.getElementById('avoir_reference').value = generateReference('AVOIR_');
    document.getElementById('depense_reference').value = generateReference('DEPENSE_');

    // Validation des formulaires
    const forms = ['formApprovisionnement', 'formRemboursement', 'formAvoir', 'formDepense'];
    
    forms.forEach(formId => {
        const form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', function(e) {
                const montantInput = form.querySelector('input[type="number"]');
                if (montantInput) {
                    const montant = parseFloat(montantInput.value);
                    if (montant <= 0) {
                        e.preventDefault();
                        alert('Veuillez saisir un montant valide supérieur à 0');
                        return false;
                    }
                }
            });
        }
    });

    // Vérification du solde en temps réel pour les dépenses et remboursements
    function checkSolde(operateurSelectId, montantInputId, type) {
        const operateurSelect = document.getElementById(operateurSelectId);
        const montantInput = document.getElementById(montantInputId);
        
        if (operateurSelect && montantInput) {
            operateurSelect.addEventListener('change', updateSoldeInfo);
            montantInput.addEventListener('input', updateSoldeInfo);
            
            function updateSoldeInfo() {
                const operateur = operateurSelect.value;
                const montant = parseFloat(montantInput.value) || 0;
                
                if (operateur && montant > 0) {
                    // Ici vous pourriez implémenter une vérification AJAX du solde
                    console.log(`Vérification ${type}: ${operateur} - ${montant}F`);
                }
            }
        }
    }

    // Activer les vérifications de solde
    checkSolde('depense_operateur', 'depense_montant', 'dépense');
    checkSolde('remb_operateur', 'remb_montant', 'remboursement');

    // Afficher les informations de solde pour les opérateurs
    const operateurSelects = ['depense_operateur', 'remb_operateur'];
    
    operateurSelects.forEach(selectId => {
        const select = document.getElementById(selectId);
        if (select) {
            select.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value === 'liquidite') {
                    // Afficher une info-bulle pour la liquidité
                    console.log('Liquidité sélectionnée - Vérification du solde de liquidité');
                }
            });
        }
    });
});
</script>
@endsection