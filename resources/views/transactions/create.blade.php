@extends('layouts.app')

@section('content')
<div class="py-4 sm:py-6">
    <div class="max-w-4xl mx-auto px-2 sm:px-0">
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-[#0b5f37]">📱 Nouvelle Transaction Mobile Money</h1>
            <p class="text-gray-600">Enregistrer une nouvelle transaction Mobile Money</p>
        </div>

        <!-- Cartes des commissions -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-[#0b5f37] mb-4 flex items-center">
                <span class="mr-2">💰</span>
                Commissions du Mois ({{ now()->format('F Y') }})
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @foreach($commissionStats as $operateur => $data)
                <div class="bg-white rounded-lg shadow border p-4 text-center transform hover:scale-105 transition duration-200">
                    <div class="text-2xl font-bold text-[#0b5f37] mb-2">
                        {{ number_format($data['commission'], 0, ',', ' ') }} F
                    </div>
                    <div class="text-sm text-gray-600 mb-1 font-medium">
                        {{ $data['operateur_nom'] }}
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ $data['transactions'] }} transaction(s)
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        

        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <form action="{{ route('mobile-money.store') }}" method="POST" id="transactionForm">
                @csrf
                
                <!-- Section Recherche client -->
                <div class="mb-6 p-4 border-2 border-dashed border-purple-300 rounded-lg bg-purple-50">
                    <h3 class="text-lg font-semibold text-purple-700 mb-3 flex items-center">
                        <span class="mr-2">🔍</span>
                        Recherche Client Existant
                    </h3>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-3">
                            Entrez le numéro de téléphone pour retrouver automatiquement les informations du client
                        </p>
                        
                        <div class="flex gap-3">
                            <div class="flex-1">
                                <input type="text" 
                                       id="search_phone" 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                       placeholder="Ex: 07 12 34 56 78"
                                       maxlength="20">
                            </div>
                            <button type="button" 
                                    onclick="searchClient()"
                                    class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 transition duration-200 font-semibold">
                                🔍 Rechercher
                            </button>
                        </div>
                    </div>
                    
                    <!-- Résultats de recherche -->
                    <div id="search_results" class="hidden bg-white rounded border border-purple-200 p-4 shadow-sm mt-4">
                        <div id="loading" class="hidden text-center py-4">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                            <p class="text-sm text-purple-600 mt-2">Recherche en cours...</p>
                        </div>
                        
                        <div id="client_found" class="hidden">
                            <div class="flex justify-between items-start mb-4">
                                <h4 class="font-semibold text-green-600 flex items-center">
                                    <span class="mr-2">✅</span>
                                    Client trouvé dans la base de données
                                </h4>
                                <span class="text-xs text-gray-500" id="last_transaction_date"></span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm mb-4">
                                <div class="p-3 rounded bg-green-50 border border-green-200">
                                    <div class="font-medium text-gray-700">Nom</div>
                                    <div id="found_nom" class="font-semibold text-green-700 text-lg"></div>
                                </div>
                                <div class="p-3 rounded bg-green-50 border border-green-200">
                                    <div class="font-medium text-gray-700">Prénom</div>
                                    <div id="found_prenom" class="font-semibold text-green-700 text-lg"></div>
                                </div>
                                <div class="p-3 rounded bg-green-50 border border-green-200">
                                    <div class="font-medium text-gray-700">CNIB</div>
                                    <div id="found_cnib" class="font-semibold text-green-700 text-lg"></div>
                                </div>
                            </div>
                            
                            <!-- Statistiques client -->
                            <div class="mb-4 p-3 bg-gray-50 rounded border">
                                <h5 class="font-medium text-gray-700 mb-2">📊 Historique du client</h5>
                                <div class="grid grid-cols-3 gap-2 text-xs">
                                    <div class="text-center p-2 bg-white rounded">
                                        <div class="font-bold text-gray-800" id="total_transactions">0</div>
                                        <div class="text-gray-600">Transactions</div>
                                    </div>
                                    <div class="text-center p-2 bg-white rounded">
                                        <div class="font-bold text-gray-800" id="avg_amount">0</div>
                                        <div class="text-gray-600">Moyenne</div>
                                    </div>
                                    <div class="text-center p-2 bg-white rounded">
                                        <div class="font-bold text-gray-800" id="total_commission">0</div>
                                        <div class="text-gray-600">Commission</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Dernières transactions -->
                            <div id="recent_transactions" class="hidden">
                                <h5 class="font-medium text-gray-700 mb-2">📝 Dernières transactions</h5>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-xs bg-white rounded border">
                                        <thead>
                                            <tr class="bg-gray-100">
                                                <th class="py-2 px-3 text-left">Date</th>
                                                <th class="py-2 px-3 text-left">Opérateur</th>
                                                <th class="py-2 px-3 text-left">Type</th>
                                                <th class="py-2 px-3 text-left">Montant</th>
                                            </tr>
                                        </thead>
                                        <tbody id="transactions_list">
                                            <!-- Les transactions seront ajoutées ici dynamiquement -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="flex gap-2 mt-4">
                                <button type="button" 
                                        onclick="applyFoundClient()"
                                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition duration-200 font-semibold text-sm">
                                    👆 Utiliser ces informations
                                </button>
                                <button type="button" 
                                        onclick="loadTransactions()"
                                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-200 text-sm">
                                    📝 Voir l'historique
                                </button>
                                <button type="button" 
                                        onclick="clearSearch()"
                                        class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition duration-200 text-sm">
                                    ✖️ Effacer
                                </button>
                            </div>
                        </div>
                        
                        <div id="client_not_found" class="hidden text-center p-4">
                            <div class="text-yellow-500 text-4xl mb-3">👤</div>
                            <p class="font-medium text-gray-700 mb-2">Aucun client trouvé</p>
                            <p class="text-sm text-gray-600">Ce numéro n'existe pas dans votre historique de transactions</p>
                            <button type="button" 
                                    onclick="clearSearch()"
                                    class="mt-3 bg-gray-500 text-white px-4 py-1 rounded hover:bg-gray-600 transition duration-200 text-sm">
                                Fermer
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Section Scanner de document avec OCR amélioré -->
                <div class="mb-6 p-4 border-2 border-dashed border-blue-300 rounded-lg bg-blue-50">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        📷 Scanner le document d'identité 
                    </label>
                    
                    <!-- Boutons de capture -->
                    <div class="flex flex-col sm:flex-row gap-3 mb-4">
                        <button type="button" onclick="openCamera()" 
                                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 inline-flex items-center justify-center transition duration-200">
                            <span class="mr-2">📷</span>
                            Prendre une photo
                        </button>
                        <button type="button" onclick="document.getElementById('document_upload').click()" 
                                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 inline-flex items-center justify-center transition duration-200">
                            <span class="mr-2">📁</span>
                            Importer une image
                        </button>
                        <button type="button" onclick="clearAll()" 
                                class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 inline-flex items-center justify-center transition duration-200">
                            <span class="mr-2">🔄</span>
                            Tout effacer
                        </button>
                    </div>

                    <!-- Input fichier caché -->
                    <input type="file" 
                           id="document_upload" 
                           accept="image/*"
                           class="hidden"
                           onchange="handleImageUpload(this)">

                    <!-- Zone de preview et caméra -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Preview de l'image -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Aperçu du document</label>
                            <div id="image_preview" class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hidden">
                                <img id="preview_image" class="max-w-full h-48 mx-auto rounded shadow" alt="Aperçu du document">
                                <div class="mt-2 flex gap-2 justify-center">
                                    <button type="button" onclick="clearImage()" 
                                            class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600 transition duration-200">
                                        ❌ Supprimer
                                    </button>
                                    <button type="button" onclick="retryOCR()" 
                                            class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600 transition duration-200">
                                        🔄 Rescanner
                                    </button>
                                </div>
                            </div>
                            <div id="no_image" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center text-gray-500 bg-gray-50">
                                <span class="text-4xl">📄</span>
                                <p class="mt-2 text-sm">Aucune image sélectionnée</p>
                                <p class="text-xs text-gray-400 mt-1">Prenez une photo ou importez une image</p>
                            </div>
                        </div>

                        <!-- Flux caméra -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Caméra en direct</label>
                            <div id="camera_container" class="hidden">
                                <video id="camera_stream" autoplay playsinline class="w-full h-48 rounded border shadow"></video>
                                <div class="flex gap-2 mt-2">
                                    <button type="button" onclick="captureImage()" 
                                            class="flex-1 bg-[#0b5f37] text-white px-3 py-2 rounded hover:bg-[#0a4d2c] transition duration-200 font-semibold">
                                        📸 Capturer
                                    </button>
                                    <button type="button" onclick="stopCamera()" 
                                            class="bg-gray-500 text-white px-3 py-2 rounded hover:bg-gray-600 transition duration-200">
                                        ❌ Arrêter
                                    </button>
                                </div>
                            </div>
                            <div id="no_camera" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center text-gray-500 bg-gray-50">
                                <span class="text-4xl">📷</span>
                                <p class="mt-2 text-sm">Caméra non activée</p>
                                <p class="text-xs text-gray-400 mt-1">Cliquez sur "Prendre une photo"</p>
                            </div>
                        </div>
                    </div>

                    <!-- Statut et résultats OCR -->
                    <div class="mt-4">
                        <div id="scan_status" class="text-sm mb-2 p-2 rounded hidden"></div>
                        <div id="ocr_results" class="hidden bg-white rounded border border-green-200 p-4 shadow-sm">
                            <h4 class="font-semibold text-green-600 mb-3 flex items-center">
                                <span class="mr-2">✅</span>
                                Données extraites du document
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                                <div class="p-2 rounded bg-green-50">
                                    <span class="font-medium text-gray-700">Nom:</span>
                                    <span id="ocr_nom" class="ml-1 font-semibold text-green-700">-</span>
                                </div>
                                <div class="p-2 rounded bg-green-50">
                                    <span class="font-medium text-gray-700">Prénom:</span>
                                    <span id="ocr_prenom" class="ml-1 font-semibold text-green-700">-</span>
                                </div>
                                <div class="p-2 rounded bg-green-50">
                                    <span class="font-medium text-gray-700">CNIB:</span>
                                    <span id="ocr_cnib" class="ml-1 font-semibold text-green-700">-</span>
                                </div>
                            </div>
                            <div class="mt-3 flex gap-2">
                                <button type="button" onclick="applyOCRData()" 
                                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition duration-200 font-semibold text-sm">
                                    👆 Appliquer ces données
                                </button>
                                <button type="button" onclick="manualCorrection()" 
                                        class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 transition duration-200 text-sm">
                                    ✏️ Corriger manuellement
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 mb-6">
                    <!-- Informations client -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-[#0b5f37] border-b pb-2 flex items-center">
                            <span class="mr-2">👤</span>
                            Informations Client
                        </h3>
                        
                        <div>
                            <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                            <input type="text" name="nom" id="nom" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0b5f37] focus:border-[#0b5f37] transition duration-200"
                                   value="{{ old('nom') }}"
                                   placeholder="Entrez le nom">
                            @error('nom')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="prenom" class="block text-sm font-medium text-gray-700 mb-1">Prénom *</label>
                            <input type="text" name="prenom" id="prenom" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0b5f37] focus:border-[#0b5f37] transition duration-200"
                                   value="{{ old('prenom') }}"
                                   placeholder="Entrez le prénom">
                            @error('prenom')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="cnib" class="block text-sm font-medium text-gray-700 mb-1">CNIB *</label>
                            <input type="text" name="cnib" id="cnib"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0b5f37] focus:border-[#0b5f37] transition duration-200"
                                   value="{{ old('cnib') }}"
                                   placeholder="B123456789">
                            @error('cnib')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="telephone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone *</label>
                            <input type="text" name="telephone" id="telephone" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0b5f37] focus:border-[#0b5f37] transition duration-200"
                                   value="{{ old('telephone') }}"
                                   placeholder="Ex: 07 12 34 56 78">
                            @error('telephone')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Informations transaction -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-[#0b5f37] border-b pb-2 flex items-center">
                            <span class="mr-2">💰</span>
                            Informations Transaction
                        </h3>
                        
                        <div>
                            <label for="type_operation" class="block text-sm font-medium text-gray-700 mb-1">Type d'opération *</label>
                            <select name="type_operation" id="type_operation" required
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0b5f37] focus:border-[#0b5f37] transition duration-200">
                                <option value="">Sélectionnez le type</option>
                                <option value="depot" {{ old('type_operation') == 'depot' ? 'selected' : '' }}>Dépôt</option>
                                <option value="retrait" {{ old('type_operation') == 'retrait' ? 'selected' : '' }}>Retrait</option>
                            </select>
                            @error('type_operation')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="nature" class="block text-sm font-medium text-gray-700 mb-1">Opérateur *</label>
                            <select name="nature" id="nature" required
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0b5f37] focus:border-[#0b5f37] transition duration-200">
                                <option value="">Sélectionnez l'opérateur</option>
                                <option value="orange_money" {{ old('nature') == 'orange_money' ? 'selected' : '' }}>Orange Money</option>
                                <option value="telecel_money" {{ old('nature') == 'telecel_money' ? 'selected' : '' }}>Telecel Money</option>
                                <option value="moov_money" {{ old('nature') == 'moov_money' ? 'selected' : '' }}>Moov Money</option>
                                <option value="coris_money" {{ old('nature') == 'coris_money' ? 'selected' : '' }}>Coris Money</option>
                            </select>
                            @error('nature')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="montant" class="block text-sm font-medium text-gray-700 mb-1">Montant (FCFA) *</label>
                            <input type="number" name="montant" id="montant" required min="0.01" step="0.01"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0b5f37] focus:border-[#0b5f37] transition duration-200"
                                   value="{{ old('montant') }}"
                                   placeholder="0.00">
                            @error('montant')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="id_transaction" class="block text-sm font-medium text-gray-700 mb-1">ID Transaction *</label>
                            <input type="text" name="id_transaction" id="id_transaction" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0b5f37] focus:border-[#0b5f37] transition duration-200"
                                   value="{{ old('id_transaction') }}"
                                   placeholder="ID de transaction du Mobile Money">
                            @error('id_transaction')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-6 border-t">
                    <a href="{{ route('mobile-money.index') }}" 
                       class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-center transition duration-200">
                        Annuler
                    </a>
                    <button type="submit" 
                            class="bg-[#0b5f37] text-white px-6 py-2 rounded hover:bg-[#0a4d2c] font-semibold transition duration-200 flex items-center justify-center">
                        <span class="mr-2">💰</span>
                        Enregistrer la Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Inclure Tesseract.js depuis CDN -->
<script src="https://unpkg.com/tesseract.js@v4.1.1/dist/tesseract.min.js"></script>

<script>
// Variables globales
let currentStream = null;
let currentImageData = null;
let ocrWorker = null;
let isOCRInitialized = false;

// ==================== INITIALISATION ====================

// Initialiser Tesseract.js au chargement
document.addEventListener('DOMContentLoaded', function() {
    initializeTesseract();
    generateTransactionId();
    
    // Ajouter un écouteur d'événement pour la touche Entrée sur le champ de recherche
    document.getElementById('search_phone').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchClient();
        }
    });
    
    // Mettre en place l'auto-complétion
    document.getElementById('telephone').addEventListener('blur', async function() {
        const phone = this.value.trim();
        
        if (phone && phone.length >= 8) {
            // Optionnel: Recherche automatique lors de la sortie du champ téléphone
            const response = await fetch('{{ route("mobile-money.search-client") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ telephone: phone })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Remplir automatiquement si le champ nom est vide
                if (!document.getElementById('nom').value && data.client.nom) {
                    document.getElementById('nom').value = data.client.nom;
                }
                
                if (!document.getElementById('prenom').value && data.client.prenom) {
                    document.getElementById('prenom').value = data.client.prenom;
                }
                
                if (!document.getElementById('cnib').value && data.client.cnib) {
                    document.getElementById('cnib').value = data.client.cnib;
                }
                
                // Afficher une petite notification
                const notification = document.createElement('div');
                notification.className = 'mt-2 p-2 bg-green-50 text-green-700 text-sm rounded border border-green-200';
                notification.innerHTML = `
                    <div class="flex items-center">
                        <span class="mr-2">👤</span>
                        <span>Client trouvé: ${data.client.nom} ${data.client.prenom} (${data.client.derniere_transaction})</span>
                    </div>
                `;
                
                const telephoneDiv = document.getElementById('telephone').parentElement;
                if (!telephoneDiv.querySelector('.client-notification')) {
                    notification.classList.add('client-notification');
                    telephoneDiv.appendChild(notification);
                    
                    // Supprimer après 5 secondes
                    setTimeout(() => notification.remove(), 5000);
                }
            }
        }
    });
});

// Initialiser Tesseract.js
async function initializeTesseract() {
    try {
        updateScanStatus('🔄 Initialisation du scanner OCR...', 'info');
        
        console.log('Chargement Tesseract.js...');
        const { createWorker } = Tesseract;
        
        ocrWorker = await createWorker('fra', 1, {
            logger: m => {
                console.log('Tesseract:', m);
                if (m.status === 'recognizing text') {
                    const progress = Math.round(m.progress * 100);
                    updateScanStatus(`🔍 Analyse OCR: ${progress}%`, 'info');
                }
            }
        });
        
        isOCRInitialized = true;
        console.log('✅ Tesseract.js initialisé avec succès');
        updateScanStatus('✅ Scanner OCR prêt! Prenez une photo du document.', 'success');
        
        // Cacher le statut après 3 secondes
        setTimeout(() => {
            document.getElementById('scan_status').classList.add('hidden');
        }, 3000);
        
    } catch (error) {
        console.error('❌ Erreur initialisation Tesseract:', error);
        updateScanStatus('⚠️ Scanner avancé indisponible. Utilisation du mode basique.', 'warning');
        isOCRInitialized = false;
    }
}

// Générer un ID de transaction
function generateTransactionId() {
    const idField = document.getElementById('id_transaction');
    if (!idField.value) {
        const now = new Date();
        const timestamp = now.getTime().toString().slice(-8);
        const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        idField.value = 'MM' + timestamp + random;
    }
}

// ==================== FONCTIONS CAMÉRA ====================

// Ouvrir la caméra
async function openCamera() {
    try {
        updateScanStatus('📷 Activation de la caméra...', 'info');
        
        const stream = await navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'environment',
                width: { ideal: 1280 },
                height: { ideal: 720 }
            } 
        });
        
        currentStream = stream;
        const video = document.getElementById('camera_stream');
        video.srcObject = stream;
        
        document.getElementById('camera_container').classList.remove('hidden');
        document.getElementById('no_camera').classList.add('hidden');
        
        updateScanStatus('✅ Caméra activée - Cadrez le document', 'success');
        
    } catch (error) {
        console.error('Erreur caméra:', error);
        updateScanStatus('❌ Impossible d\'accéder à la caméra. Utilisez l\'import d\'image.', 'error');
    }
}

// Arrêter la caméra
function stopCamera() {
    if (currentStream) {
        currentStream.getTracks().forEach(track => track.stop());
        currentStream = null;
    }
    document.getElementById('camera_container').classList.add('hidden');
    document.getElementById('no_camera').classList.remove('hidden');
}

// Capturer une image
function captureImage() {
    const video = document.getElementById('camera_stream');
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');
    
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    canvas.toBlob(function(blob) {
        handleImageBlob(blob);
    }, 'image/jpeg', 0.9);
    
    stopCamera();
}

// ==================== GESTION DES IMAGES ====================

// Gérer l'upload d'image
function handleImageUpload(input) {
    const file = input.files[0];
    if (!file) return;
    
    if (!file.type.startsWith('image/')) {
        updateScanStatus('❌ Veuillez sélectionner une image valide (JPEG, PNG)', 'error');
        return;
    }
    
    if (file.size > 5 * 1024 * 1024) {
        updateScanStatus('❌ Image trop volumineuse (max 5MB)', 'error');
        return;
    }
    
    handleImageBlob(file);
}

// Traiter l'image
function handleImageBlob(blob) {
    const url = URL.createObjectURL(blob);
    const previewImg = document.getElementById('preview_image');
    
    previewImg.onload = function() {
        URL.revokeObjectURL(url);
    };
    
    previewImg.src = url;
    document.getElementById('image_preview').classList.remove('hidden');
    document.getElementById('no_image').classList.add('hidden');
    
    // Stocker les données pour l'OCR
    currentImageData = blob;
    
    // Lancer l'analyse OCR
    performOCR(blob);
}

// Effacer l'image
function clearImage() {
    document.getElementById('image_preview').classList.add('hidden');
    document.getElementById('no_image').classList.remove('hidden');
    document.getElementById('ocr_results').classList.add('hidden');
    document.getElementById('scan_status').classList.add('hidden');
    currentImageData = null;
    document.getElementById('document_upload').value = '';
}

// Tout effacer
function clearAll() {
    clearImage();
    stopCamera();
    updateScanStatus('🗑️ Toutes les données ont été effacées', 'info');
    setTimeout(() => {
        document.getElementById('scan_status').classList.add('hidden');
    }, 3000);
}

// Rescanner
function retryOCR() {
    if (currentImageData) {
        performOCR(currentImageData);
    }
}

// Correction manuelle
function manualCorrection() {
    updateScanStatus('✏️ Mode correction activé. Modifiez les champs manuellement.', 'info');
}

// ==================== FONCTIONS OCR ====================

// Fonction OCR principale
async function performOCR(imageBlob) {
    updateScanStatus('⏳ Analyse du document en cours...', 'info');
    
    try {
        // UNIQUEMENT OCR côté serveur
        await performOCRServeur(imageBlob);
        
    } catch (serverError) {
        console.log('OCR serveur échoué:', serverError.message);
        
        // En cas d'erreur serveur, essayer OCR client si disponible
        if (isOCRInitialized) {
            await performOCRClient(imageBlob);
        } else {
            // NE PAS utiliser de données de démonstration
            updateScanStatus('❌ Échec de l\'analyse OCR. Essayez avec une autre image.', 'error');
            
            const errorData = { 
                nom: 'Non détecté', 
                prenom: 'Non détecté', 
                cnib: 'Non détecté',
                error: 'OCR indisponible'
            };
            displayOCRResults(errorData);
        }
    }
}

// OCR côté serveur
async function performOCRServeur(imageBlob) {
    try {
        updateScanStatus('🔍 Analyse avec le serveur...', 'info');
        
        const formData = new FormData();
        formData.append('document_image', imageBlob, 'document_' + Date.now() + '.jpg');

        const response = await fetch('{{ route("mobile-money.scan") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        });

        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status}`);
        }

        const result = await response.json();

        if (result.success) {
            displayOCRResults(result.data);
            updateScanStatus('✅ Analyse serveur terminée!', 'success');
        } else {
            throw new Error(result.message || 'Erreur lors du scan');
        }
        
    } catch (error) {
        console.error('Erreur OCR serveur:', error);
        throw new Error('Serveur OCR indisponible');
    }
}

// OCR côté client avec Tesseract.js
async function performOCRClient(imageBlob) {
    if (!isOCRInitialized) {
        updateScanStatus('❌ OCR client non disponible', 'error');
        return;
    }

    try {
        updateScanStatus('🔍 Analyse OCR avancée...', 'info');
        
        const { data: { text } } = await ocrWorker.recognize(imageBlob);
        console.log('📝 Texte extrait:', text);
        
        if (!text || text.trim().length < 10) {
            throw new Error('Texte insuffisant pour l\'analyse');
        }
        
        const extractedData = extractDataFromText(text);
        displayOCRResults(extractedData);
        updateScanStatus('✅ Analyse OCR terminée avec succès!', 'success');
        
    } catch (error) {
        console.error('Erreur OCR client:', error);
        updateScanStatus('❌ Échec de l\'analyse OCR', 'error');
        
        const errorData = { 
            nom: 'Non détecté', 
            prenom: 'Non détecté', 
            cnib: 'Non détecté',
            error: 'OCR échoué'
        };
        displayOCRResults(errorData);
    }
}

// Extraire les données du texte
function extractDataFromText(text) {
    const data = { nom: '', prenom: '', cnib: '' };

    const cleanText = text.toUpperCase().replace(/\s+/g, ' ').trim();
    console.log('📋 Texte nettoyé:', cleanText);

    // Chercher CNIB (format: 1-2 lettres + 6-12 chiffres)
    const cnibMatch = cleanText.match(/[A-Z]{1,2}\d{6,12}/);
    if (cnibMatch) {
        data.cnib = cnibMatch[0];
    }

    // Chercher NOM
    const nomMatch = cleanText.match(/NOM[:\s]*([A-Z][A-Z\s]{2,})/);
    if (nomMatch && nomMatch[1]) {
        data.nom = nomMatch[1].trim().replace(/[^A-Z\s]/g, '');
    }

    // Chercher PRENOM
    const prenomMatch = cleanText.match(/PRENOM[:\s]*([A-Z][A-Z\s]{2,})/);
    if (prenomMatch && prenomMatch[1]) {
        data.prenom = prenomMatch[1].trim().replace(/[^A-Z\s]/g, '');
    }

    // Si pas trouvé, utiliser les premiers mots significatifs
    if (!data.nom || !data.prenom) {
        const words = cleanText.split(' ')
            .filter(word => word.length > 2)
            .filter(word => !word.match(/NOM|PRENOM|IDENTITE|CNI|CARTE|NUMERO/));
        
        if (words.length >= 2) {
            if (!data.nom) data.nom = words[0];
            if (!data.prenom) data.prenom = words[1];
        }
    }

    console.log('📊 Données extraites:', data);
    return data;
}

// ==================== AFFICHAGE DES RÉSULTATS ====================

// Afficher les résultats OCR
function displayOCRResults(data) {
    const resultsDiv = document.getElementById('ocr_results');
    
    let html = `
        <h4 class="font-semibold text-green-600 mb-3 flex items-center">
            <span class="mr-2">✅</span>
            Données extraites du document
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm mb-3">
    `;
    
    // Nom
    const nomValue = data.nom || 'Non détecté';
    const nomClass = data.nom && data.nom !== 'Non détecté' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700';
    html += `
        <div class="p-2 rounded ${nomClass}">
            <span class="font-medium">Nom:</span>
            <span id="ocr_nom" class="ml-1 font-semibold">${nomValue}</span>
        </div>
    `;
    
    // Prénom
    const prenomValue = data.prenom || 'Non détecté';
    const prenomClass = data.prenom && data.prenom !== 'Non détecté' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700';
    html += `
        <div class="p-2 rounded ${prenomClass}">
            <span class="font-medium">Prénom:</span>
            <span id="ocr_prenom" class="ml-1 font-semibold">${prenomValue}</span>
        </div>
    `;
    
    // CNIB
    const cnibValue = data.cnib || 'Non détecté';
    const cnibClass = data.cnib && data.cnib !== 'Non détecté' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700';
    html += `
        <div class="p-2 rounded ${cnibClass}">
            <span class="font-medium">CNIB:</span>
            <span id="ocr_cnib" class="ml-1 font-semibold">${cnibValue}</span>
        </div>
    `;
    
    html += `</div>`;
    
    // Afficher l'erreur si présente
    if (data.error) {
        html += `
            <div class="mt-2 p-2 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
                <strong>⚠️ Erreur:</strong> ${data.error}
            </div>
        `;
    }
    
    html += `
        <div class="mt-3 flex gap-2">
            <button type="button" onclick="applyOCRData()" 
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition duration-200 font-semibold text-sm">
                👆 Appliquer ces données
            </button>
            <button type="button" onclick="manualCorrection()" 
                    class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 transition duration-200 text-sm">
                ✏️ Corriger manuellement
            </button>
        </div>
    `;
    
    resultsDiv.innerHTML = html;
    resultsDiv.classList.remove('hidden');
}

// Appliquer les données OCR
function applyOCRData() {
    const nom = document.getElementById('ocr_nom').textContent;
    const prenom = document.getElementById('ocr_prenom').textContent;
    const cnib = document.getElementById('ocr_cnib').textContent;
    
    if (nom !== 'Non détecté') {
        document.getElementById('nom').value = nom;
    }
    if (prenom !== 'Non détecté') {
        document.getElementById('prenom').value = prenom;
    }
    if (cnib !== 'Non détecté') {
        document.getElementById('cnib').value = cnib;
    }
    
    updateScanStatus('✅ Données appliquées au formulaire! Vérifiez et corrigez si nécessaire.', 'success');
}

// Mettre à jour le statut
function updateScanStatus(message, type = 'info') {
    const statusDiv = document.getElementById('scan_status');
    const colors = {
        info: 'bg-blue-100 text-blue-800 border border-blue-200',
        success: 'bg-green-100 text-green-800 border border-green-200',
        error: 'bg-red-100 text-red-800 border border-red-200',
        warning: 'bg-yellow-100 text-yellow-800 border border-yellow-200'
    };
    
    statusDiv.innerHTML = `<div class="p-3 rounded ${colors[type]}">${message}</div>`;
    statusDiv.classList.remove('hidden');
}

// ==================== FONCTIONS DE RECHERCHE CLIENT ====================

// Rechercher un client par téléphone
async function searchClient() {
    const phoneInput = document.getElementById('search_phone');
    const phone = phoneInput.value.trim();
    
    if (!phone) {
        showAlert('Veuillez entrer un numéro de téléphone', 'error');
        return;
    }
    
    const resultsDiv = document.getElementById('search_results');
    const loadingDiv = document.getElementById('loading');
    const clientFoundDiv = document.getElementById('client_found');
    const clientNotFoundDiv = document.getElementById('client_not_found');
    
    resultsDiv.classList.remove('hidden');
    loadingDiv.classList.remove('hidden');
    clientFoundDiv.classList.add('hidden');
    clientNotFoundDiv.classList.add('hidden');
    
    try {
        const response = await fetch('{{ route("mobile-money.search-client") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ telephone: phone })
        });
        
        const data = await response.json();
        
        loadingDiv.classList.add('hidden');
        
        if (data.success) {
            // Afficher les informations du client
            document.getElementById('found_nom').textContent = data.client.nom;
            document.getElementById('found_prenom').textContent = data.client.prenom;
            document.getElementById('found_cnib').textContent = data.client.cnib || 'Non renseigné';
            document.getElementById('last_transaction_date').textContent = 
                'Dernière transaction: ' + data.client.derniere_transaction;
            
            // Afficher les statistiques
            document.getElementById('total_transactions').textContent = data.statistics.total_transactions;
            document.getElementById('avg_amount').textContent = 
                data.statistics.montant_moyen ? Math.round(data.statistics.montant_moyen).toLocaleString() + ' F' : '0 F';
            document.getElementById('total_commission').textContent = 
                data.statistics.total_commission ? Math.round(data.statistics.total_commission).toLocaleString() + ' F' : '0 F';
            
            clientFoundDiv.classList.remove('hidden');
            showAlert('✅ Client trouvé! Les informations ont été chargées.', 'success');
        } else {
            clientNotFoundDiv.classList.remove('hidden');
        }
        
    } catch (error) {
        console.error('Erreur recherche client:', error);
        loadingDiv.classList.add('hidden');
        showAlert('❌ Erreur lors de la recherche', 'error');
    }
}

// Charger l'historique des transactions du client
async function loadTransactions() {
    const phoneInput = document.getElementById('search_phone');
    const phone = phoneInput.value.trim();
    
    if (!phone) return;
    
    const transactionsList = document.getElementById('transactions_list');
    const recentTransactionsDiv = document.getElementById('recent_transactions');
    
    transactionsList.innerHTML = '<tr><td colspan="4" class="py-4 text-center">Chargement...</td></tr>';
    
    try {
        const params = new URLSearchParams({ telephone: phone });
        const response = await fetch(`{{ route("mobile-money.client-transactions") }}?${params}`);
        const data = await response.json();
        
        if (data.success && data.transactions.length > 0) {
            transactionsList.innerHTML = '';
            
            data.transactions.forEach(transaction => {
                const row = document.createElement('tr');
                row.className = 'border-t hover:bg-gray-50';
                row.innerHTML = `
                    <td class="py-2 px-3">${transaction.date}</td>
                    <td class="py-2 px-3">${transaction.operateur}</td>
                    <td class="py-2 px-3">${transaction.type}</td>
                    <td class="py-2 px-3 font-medium">${transaction.montant}</td>
                `;
                transactionsList.appendChild(row);
            });
            
            recentTransactionsDiv.classList.remove('hidden');
            showAlert('📊 Historique chargé avec succès', 'success');
        } else {
            transactionsList.innerHTML = '<tr><td colspan="4" class="py-4 text-center text-gray-500">Aucune transaction trouvée</td></tr>';
        }
        
    } catch (error) {
        console.error('Erreur chargement transactions:', error);
        transactionsList.innerHTML = '<tr><td colspan="4" class="py-4 text-center text-red-500">Erreur de chargement</td></tr>';
    }
}

// Appliquer les informations du client trouvé au formulaire
function applyFoundClient() {
    const nom = document.getElementById('found_nom').textContent;
    const prenom = document.getElementById('found_prenom').textContent;
    const cnib = document.getElementById('found_cnib').textContent;
    const phone = document.getElementById('search_phone').value.trim();
    
    document.getElementById('nom').value = nom;
    document.getElementById('prenom').value = prenom;
    
    if (cnib !== 'Non renseigné') {
        document.getElementById('cnib').value = cnib;
    }
    
    document.getElementById('telephone').value = phone;
    
    showAlert('✅ Informations du client appliquées au formulaire!', 'success');
    
    // Focus sur le montant pour continuer
    document.getElementById('montant').focus();
    
    // Fermer la recherche
    document.getElementById('search_results').classList.add('hidden');
}

// Effacer la recherche
function clearSearch() {
    document.getElementById('search_results').classList.add('hidden');
    document.getElementById('search_phone').value = '';
    document.getElementById('recent_transactions').classList.add('hidden');
}

// Afficher une alerte
function showAlert(message, type) {
    const colors = {
        success: 'bg-green-100 text-green-800 border border-green-200',
        error: 'bg-red-100 text-red-800 border border-red-200',
        info: 'bg-blue-100 text-blue-800 border border-blue-200'
    };
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg ${colors[type]} transition duration-300 transform translate-x-0`;
    alertDiv.innerHTML = `
        <div class="flex items-center">
            <span class="mr-3">${type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️'}</span>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto-dismiss after 3 seconds
    setTimeout(() => {
        alertDiv.style.transform = 'translateX(100%)';
        setTimeout(() => alertDiv.remove(), 300);
    }, 3000);
}

// Nettoyer à la fermeture
window.addEventListener('beforeunload', function() {
    if (currentStream) {
        stopCamera();
    }
    if (ocrWorker) {
        ocrWorker.terminate();
    }
});
</script>
@endsection