@extends('layouts.app')

@section('content')
<div class="py-4 sm:py-6">
    <div class="max-w-6xl mx-auto px-2 sm:px-0">
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-[#0b5f37]">📤 Exporter les Transactions Mobile Money</h1>
            <p class="text-gray-600">Exportez vos transactions dans différents formats pour analyse et archivage</p>
        </div>

        <!-- Cartes de statistiques améliorées -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700">Total Transactions</h3>
                        <p class="text-xl font-bold text-blue-600">{{ number_format($statistiques['total_transactions'], 0, ',', ' ') }}</p>
                        <p class="text-xs text-gray-500 mt-1">Période sélectionnée</p>
                    </div>
                    <div class="text-2xl">📊</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700">Total Dépôts</h3>
                        <p class="text-xl font-bold text-green-600">{{ number_format($statistiques['total_depots'], 0, ',', ' ') }} F</p>
                        <p class="text-xs text-gray-500 mt-1">Montant total des dépôts</p>
                    </div>
                    <div class="text-2xl">📥</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700">Total Retraits</h3>
                        <p class="text-xl font-bold text-red-600">{{ number_format($statistiques['total_retraits'], 0, ',', ' ') }} F</p>
                        <p class="text-xs text-gray-500 mt-1">Montant total des retraits</p>
                    </div>
                    <div class="text-2xl">📤</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700">Solde Net</h3>
                        <p class="text-xl font-bold text-purple-600">{{ number_format($statistiques['solde_net'], 0, ',', ' ') }} F</p>
                        <p class="text-xs text-gray-500 mt-1">Bénéfice net</p>
                    </div>
                    <div class="text-2xl">💰</div>
                </div>
            </div>
        </div>

        <!-- Indicateur de période -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <span class="text-blue-600 text-2xl mr-3">📅</span>
                    <div>
                        <h4 class="font-semibold text-blue-800">Période d'export</h4>
                        <p class="text-blue-600 text-sm">{{ $statistiques['periode'] }}</p>
                    </div>
                </div>
                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                    {{ $statistiques['total_transactions'] }} transactions
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Formulaire d'export amélioré -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-[#0b5f37]">🔧 Paramètres d'export</h3>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-500">Export rapide:</span>
                            <button type="button" onclick="setPeriod('today')" class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded">Aujourd'hui</button>
                            <button type="button" onclick="setPeriod('week')" class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded">Semaine</button>
                            <button type="button" onclick="setPeriod('month')" class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded">Mois</button>
                        </div>
                    </div>

                    <!-- CORRECTION : méthode GET -->
                    <form action="{{ route('mobile-money.export') }}" method="GET" id="exportForm">
                        <!-- Format d'export amélioré -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                📁 Format d'export <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <label class="export-format-card">
                                    <input type="radio" name="format" value="csv" checked class="hidden peer">
                                    <div class="export-format-content peer-checked:border-[#0b5f37] peer-checked:bg-green-50">
                                        <div class="text-2xl mb-2">📄</div>
                                        <div class="font-medium">CSV</div>
                                        <div class="text-xs text-gray-500 mt-1">Excel/Tableur</div>
                                        <div class="text-xs text-green-600 font-medium mt-1 hidden peer-checked:block">✓ Recommandé</div>
                                    </div>
                                </label>
                                <label class="export-format-card">
                                    <input type="radio" name="format" value="excel" class="hidden peer">
                                    <div class="export-format-content peer-checked:border-[#0b5f37] peer-checked:bg-green-50">
                                        <div class="text-2xl mb-2">📑</div>
                                        <div class="font-medium">Excel</div>
                                        <div class="text-xs text-gray-500 mt-1">.XLS Format</div>
                                    </div>
                                </label>
                                <label class="export-format-card">
                                    <input type="radio" name="format" value="pdf" class="hidden peer">
                                    <div class="export-format-content peer-checked:border-[#0b5f37] peer-checked:bg-green-50">
                                        <div class="text-2xl mb-2">📊</div>
                                        <div class="font-medium">PDF</div>
                                        <div class="text-xs text-gray-500 mt-1">Rapport imprimable</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Période avec validation -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label for="date_debut" class="block text-sm font-medium text-gray-700 mb-1">
                                    📅 Date de début
                                </label>
                                <input type="date" name="date_debut" id="date_debut" 
                                       value="{{ request('date_debut', $dates['debut']) }}"
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0b5f37] focus:border-[#0b5f37] transition-colors"
                                       required>
                                <p class="text-xs text-gray-500 mt-1">Date de début de la période</p>
                            </div>
                            <div>
                                <label for="date_fin" class="block text-sm font-medium text-gray-700 mb-1">
                                    📅 Date de fin
                                </label>
                                <input type="date" name="date_fin" id="date_fin" 
                                       value="{{ request('date_fin', $dates['fin']) }}"
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0b5f37] focus:border-[#0b5f37] transition-colors"
                                       required>
                                <p class="text-xs text-gray-500 mt-1">Date de fin de la période</p>
                            </div>
                        </div>

                        <!-- Filtres avancés avec icônes -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label for="type_operation" class="block text-sm font-medium text-gray-700 mb-1">
                                    🔄 Type d'opération
                                </label>
                                <select name="type_operation" id="type_operation"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0b5f37] focus:border-[#0b5f37] transition-colors">
                                    <option value="">Tous les types</option>
                                    <option value="depot" {{ request('type_operation') == 'depot' ? 'selected' : '' }}>💳 Dépôt</option>
                                    <option value="retrait" {{ request('type_operation') == 'retrait' ? 'selected' : '' }}>💸 Retrait</option>
                                </select>
                            </div>

                            <div>
                                <label for="nature" class="block text-sm font-medium text-gray-700 mb-1">
                                    📱 Opérateur Mobile
                                </label>
                                <select name="nature" id="nature"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0b5f37] focus:border-[#0b5f37] transition-colors">
                                    <option value="">Tous les opérateurs</option>
                                    <option value="orange_money" {{ request('nature') == 'orange_money' ? 'selected' : '' }}>🟠 Orange Money</option>
                                    <option value="telecel_money" {{ request('nature') == 'telecel_money' ? 'selected' : '' }}>🔵 Telecel Money</option>
                                    <option value="moov_money" {{ request('nature') == 'moov_money' ? 'selected' : '' }}>🟢 Moov Money</option>
                                    <option value="coris_money" {{ request('nature') == 'coris_money' ? 'selected' : '' }}>🟡 Coris Money</option>
                                </select>
                            </div>
                        </div>

                        <!-- Boutons d'action améliorés -->
                        <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t">
                            <button type="submit" id="exportButton"
                                    class="flex-1 bg-[#0b5f37] text-white px-6 py-3 rounded-lg hover:bg-[#0a4d2c] font-semibold inline-flex items-center justify-center transition-all duration-200 transform hover:scale-[1.02] shadow-lg">
                                <span class="mr-2" id="exportIcon">📥</span>
                                <span id="exportText">Exporter les Données</span>
                                <div id="exportSpinner" class="hidden ml-2">
                                    <div class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></div>
                                </div>
                            </button>
                            <div class="flex gap-3">
                                <a href="{{ route('mobile-money.index') }}" 
                                   class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 text-center inline-flex items-center justify-center transition-colors">
                                    <span class="mr-2">↶</span>
                                    Retour
                                </a>
                                <button type="button" onclick="resetForm()" 
                                        class="bg-orange-500 text-white px-4 py-3 rounded-lg hover:bg-orange-600 inline-flex items-center justify-center transition-colors">
                                    <span class="mr-2">🔄</span>
                                    Reset
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Panneau latéral amélioré -->
            <div class="space-y-6">
                <!-- Aperçu en temps réel -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-[#0b5f37] mb-4 flex items-center">
                        👀 Aperçu des données
                        <span class="ml-2 bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Live</span>
                    </h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-600">Transactions:</span>
                            <span class="font-bold text-blue-600">{{ number_format($statistiques['total_transactions'], 0, ',', ' ') }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                            <span class="text-gray-600">Dépôts:</span>
                            <span class="font-bold text-green-600">{{ number_format($statistiques['total_depots'], 0, ',', ' ') }} F</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                            <span class="text-gray-600">Retraits:</span>
                            <span class="font-bold text-red-600">{{ number_format($statistiques['total_retraits'], 0, ',', ' ') }} F</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-purple-50 rounded-lg border-t">
                            <span class="text-gray-700 font-medium">Solde net:</span>
                            <span class="font-bold text-purple-600 text-lg">{{ number_format($statistiques['solde_net'], 0, ',', ' ') }} F</span>
                        </div>
                    </div>
                </div>

                <!-- Informations sur le format sélectionné -->
                <div id="formatInfo" class="bg-blue-50 rounded-lg shadow p-6 border border-blue-200">
                    <h3 class="text-lg font-semibold text-blue-800 mb-3 flex items-center">
                        💡 Informations du format
                    </h3>
                    <div id="csvInfo" class="format-info-content">
                        <p class="text-blue-700 text-sm mb-2"><strong>CSV (Recommandé)</strong></p>
                        <ul class="text-blue-600 text-xs space-y-1">
                            <li>• Compatible Excel, Google Sheets</li>
                            <li>• Séparateur point-virgule (;)</li>
                            <li>• Encodage UTF-8</li>
                            <li>• Idéal pour l'analyse de données</li>
                        </ul>
                    </div>
                    <div id="excelInfo" class="format-info-content hidden">
                        <p class="text-blue-700 text-sm mb-2"><strong>Excel (.XLS)</strong></p>
                        <ul class="text-blue-600 text-xs space-y-1">
                            <li>• Format natif Microsoft Excel</li>
                            <li>• Mise en forme automatique</li>
                            <li>• Compatible avec les formules</li>
                            <li>• Poids de fichier optimisé</li>
                        </ul>
                    </div>
                    <div id="pdfInfo" class="format-info-content hidden">
                        <p class="text-blue-700 text-sm mb-2"><strong>PDF</strong></p>
                        <ul class="text-blue-600 text-xs space-y-1">
                            <li>• Format imprimable</li>
                            <li>• Mise en page professionnelle</li>
                            <li>• Idéal pour les rapports</li>
                            <li>• Conservation de la mise en forme</li>
                        </ul>
                    </div>
                </div>

                <!-- Conseils d'export -->
                <div class="bg-green-50 rounded-lg shadow p-6 border border-green-200">
                    <h3 class="text-lg font-semibold text-green-800 mb-3">🚀 Conseils d'export</h3>
                    <div class="space-y-2 text-sm text-green-700">
                        <div class="flex items-start">
                            <span class="mr-2">💡</span>
                            <span>Utilisez <strong>CSV</strong> pour l'analyse dans Excel</span>
                        </div>
                        <div class="flex items-start">
                            <span class="mr-2">📊</span>
                            <span><strong>PDF</strong> pour les rapports officiels</span>
                        </div>
                        <div class="flex items-start">
                            <span class="mr-2">⏱️</span>
                            <span>Les exports incluent automatiquement les totaux</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Éléments DOM
    const exportForm = document.getElementById('exportForm');
    const exportButton = document.getElementById('exportButton');
    const exportIcon = document.getElementById('exportIcon');
    const exportText = document.getElementById('exportText');
    const exportSpinner = document.getElementById('exportSpinner');
    const dateDebut = document.getElementById('date_debut');
    const dateFin = document.getElementById('date_fin');

    // Gestion de la sélection des formats avec feedback visuel
    const formatRadios = document.querySelectorAll('input[name="format"]');
    const formatInfoSections = {
        'csv': document.getElementById('csvInfo'),
        'excel': document.getElementById('excelInfo'),
        'pdf': document.getElementById('pdfInfo')
    };

    formatRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Masquer toutes les sections d'info
            Object.values(formatInfoSections).forEach(section => {
                section.classList.add('hidden');
            });
            
            // Afficher la section correspondante
            const selectedFormat = this.value;
            if (formatInfoSections[selectedFormat]) {
                formatInfoSections[selectedFormat].classList.remove('hidden');
            }
            
            // Mettre à jour le texte du bouton
            updateExportButtonText(selectedFormat);
        });
    });

    // Initialiser l'affichage du format par défaut
    const defaultFormat = document.querySelector('input[name="format"]:checked');
    if (defaultFormat) {
        formatInfoSections[defaultFormat.value].classList.remove('hidden');
        updateExportButtonText(defaultFormat.value);
    }

    function updateExportButtonText(format) {
        const formatTexts = {
            'csv': 'Exporter en CSV',
            'excel': 'Exporter en Excel',
            'pdf': 'Générer le PDF'
        };
        exportText.textContent = formatTexts[format] || 'Exporter les Données';
    }

    // Validation des dates améliorée
    function validateDates() {
        if (dateDebut.value && dateFin.value) {
            const startDate = new Date(dateDebut.value);
            const endDate = new Date(dateFin.value);
            
            if (startDate > endDate) {
                showNotification('❌ La date de début ne peut pas être après la date de fin', 'error');
                dateDebut.value = '';
                dateFin.value = '';
                return false;
            }
            
            // Vérifier si la période est trop longue (optionnel)
            const diffTime = Math.abs(endDate - startDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays > 365) {
                showNotification('⚠️ La période sélectionnée dépasse 1 an. L\'export peut prendre du temps.', 'warning');
            }
        }
        return true;
    }

    dateDebut.addEventListener('change', validateDates);
    dateFin.addEventListener('change', validateDates);

    // Gestion de la soumission du formulaire
    exportForm.addEventListener('submit', function(e) {
        if (!validateDates()) {
            e.preventDefault();
            return;
        }

        // Afficher l'indicateur de chargement
        exportIcon.classList.add('hidden');
        exportSpinner.classList.remove('hidden');
        exportText.textContent = 'Génération en cours...';
        exportButton.disabled = true;

        // Restaurer après 10 secondes maximum (au cas où)
        setTimeout(() => {
            resetExportButton();
        }, 10000);
    });

    function resetExportButton() {
        exportIcon.classList.remove('hidden');
        exportSpinner.classList.add('hidden');
        exportText.textContent = 'Exporter les Données';
        exportButton.disabled = false;
    }

    // Périodes prédéfinies
    function setPeriod(period) {
        const today = new Date();
        let startDate, endDate;

        switch(period) {
            case 'today':
                startDate = today;
                endDate = today;
                break;
            case 'week':
                startDate = new Date(today.setDate(today.getDate() - 7));
                endDate = new Date();
                break;
            case 'month':
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                break;
            default:
                return;
        }

        dateDebut.value = formatDateForInput(startDate);
        dateFin.value = formatDateForInput(endDate);
        
        showNotification(`📅 Période définie: ${getPeriodDescription(period)}`, 'success');
    }

    function formatDateForInput(date) {
        return date.toISOString().split('T')[0];
    }

    function getPeriodDescription(period) {
        const descriptions = {
            'today': 'Aujourd\'hui',
            'week': '7 derniers jours',
            'month': 'Mois en cours'
        };
        return descriptions[period] || 'Période personnalisée';
    }

    // Réinitialisation du formulaire
    function resetForm() {
        exportForm.reset();
        setDefaultDates();
        showNotification('🔄 Formulaire réinitialisé', 'info');
        
        // Réinitialiser l'affichage du format
        const defaultFormat = document.querySelector('input[name="format"][value="csv"]');
        if (defaultFormat) {
            defaultFormat.checked = true;
            defaultFormat.dispatchEvent(new Event('change'));
        }
    }

    // Notification système
    function showNotification(message, type = 'info') {
        // Créer une notification toast
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 transform transition-transform duration-300 ${
            type === 'error' ? 'bg-red-100 border border-red-300 text-red-800' :
            type === 'warning' ? 'bg-yellow-100 border border-yellow-300 text-yellow-800' :
            type === 'success' ? 'bg-green-100 border border-green-300 text-green-800' :
            'bg-blue-100 border border-blue-300 text-blue-800'
        }`;
        toast.innerHTML = `
            <div class="flex items-center">
                <span class="mr-2">${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-lg">×</button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Auto-suppression après 5 secondes
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 5000);
    }

    // Définir les dates par défaut au chargement
    function setDefaultDates() {
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        
        if (!dateDebut.value) {
            dateDebut.value = formatDateForInput(firstDay);
        }
        if (!dateFin.value) {
            dateFin.value = formatDateForInput(lastDay);
        }
    }

    setDefaultDates();
});

// Exposer les fonctions globales
window.setPeriod = setPeriod;
window.resetForm = resetForm;
</script>

<style>
.export-format-card {
    cursor: pointer;
}

.export-format-content {
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1rem;
    text-align: center;
    transition: all 0.2s ease-in-out;
    background: white;
}

.export-format-content:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-color: #0b5f37;
}

.export-format-content.peer-checked\:border-\[\#0b5f37\] {
    border-color: #0b5f37;
    background-color: #f0f9f0;
}

.format-info-content {
    transition: opacity 0.3s ease-in-out;
}

.hidden {
    display: none !important;
}

/* Animation pour le bouton d'export */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

#exportButton:hover {
    animation: pulse 1s infinite;
}

/* Styles responsifs */
@media (max-width: 768px) {
    .export-format-content {
        padding: 0.75rem;
    }
    
    .grid-cols-2 {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection