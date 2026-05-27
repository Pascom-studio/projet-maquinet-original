@extends('layouts.app')

@section('content')
<div class="py-4 sm:py-6">
    <div class="max-w-7xl mx-auto px-2 sm:px-0">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#0b5f37] mb-4 sm:mb-6">Nouvelle Vente</h1>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <form id="vente-multiple-form" action="{{ route('ventes.store') }}" method="POST">
                @csrf
                
                <!-- Champ caché pour monnaie -->
                <input type="hidden" name="monnaie" id="monnaie-hidden" value="0">
                
                <!-- Stocker les produits disponibles dans un champ caché -->
                <input type="hidden" id="available-products" value="{{ json_encode($products->map(function($product) {
                    return [
                        'id' => $product->id,
                        'designation' => $product->designation,
                        'prix' => $product->prix,
                        'quantite' => $product->quantite,
                        'code_barre' => $product->code_barre
                    ];
                })) }}">
                
                <!-- SECTION SCAN ET RECHERCHE DE PRODUITS -->
                <div class="border-b pb-4 sm:pb-6 mb-4 sm:mb-6">
                    <h3 class="text-lg font-semibold text-[#0b5f37] mb-3 sm:mb-4">🛒 Ajouter un produit</h3>
                    
                    <!-- Boutons de scan -->
                    <div class="flex flex-wrap gap-2 mb-4">
                        <button type="button" id="startCamera" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center text-sm">
                            <i class="fas fa-camera mr-2"></i> Scanner caméra
                        </button>
                        <button type="button" id="manualScan" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center text-sm">
                            <i class="fas fa-keyboard mr-2"></i> Saisir code
                        </button>
                    </div>

                    <!-- Zone caméra AVEC FALLBACK -->
                    <div id="cameraSection" class="hidden mb-4">
                        <div id="cameraFallback" class="hidden p-4 bg-yellow-50 border border-yellow-200 rounded mb-2">
                            <p class="text-yellow-800 text-sm">
                                <strong>Scanner basique activé:</strong> Prenez une photo du code-barres, puis analysez-la.
                            </p>
                        </div>
                        <div id="reader" class="w-full max-w-md mx-auto border-2 border-dashed border-gray-300 rounded-lg p-4" style="min-height: 300px;">
                            <div id="cameraPreview" class="w-full h-full flex items-center justify-center text-gray-500">
                                <div class="text-center">
                                    <div class="text-4xl mb-2">📷</div>
                                    <p>Initialisation de la caméra...</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-2">
                            <button type="button" id="stopCamera" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 text-sm">
                                Arrêter caméra
                            </button>
                            <button type="button" id="captureImage" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 text-sm hidden">
                                📸 Capturer l'image
                            </button>
                            <input type="file" id="imageUpload" accept="image/*" class="hidden">
                            <button type="button" id="uploadImage" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 text-sm hidden">
                                📁 Uploader image
                            </button>
                        </div>
                    </div>

                    <!-- Champ scan manuel -->
                    <div id="manualSection" class="hidden mb-4">
                        <div class="flex space-x-2">
                            <input type="text" id="manualCode" placeholder="Entrez le code-barres ou QR code" 
                                   class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37] text-sm">
                            <button type="button" id="searchByCode" class="bg-[#0b5f37] text-white px-4 py-2 rounded-lg hover:bg-[#0a4f2d] text-sm">
                                Rechercher
                            </button>
                        </div>
                    </div>

                    <!-- Recherche par nom -->
                    <div class="space-y-3 sm:space-y-0 sm:grid sm:grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 mb-3 sm:mb-4">
                        <div class="relative">
                            <label for="product_search" class="block text-sm font-medium text-gray-700">Rechercher par nom</label>
                            <input type="text" id="product_search" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm sm:text-base focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37]"
                                   placeholder="Tapez le nom du produit..."
                                   autocomplete="off">
                            <div id="search-results" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg hidden max-h-60 overflow-y-auto"></div>
                        </div>
                        <div class="flex items-end">
                            <button type="button" id="ajouter-produit" 
                                    class="bg-[#0b5f37] text-white px-3 sm:px-4 py-2 rounded hover:bg-[#0a4d2c] w-full text-sm sm:text-base">
                                ➕ Ajouter
                            </button>
                        </div>
                    </div>
                    <div id="product-info" class="text-sm text-gray-600"></div>
                </div>

                <!-- LISTE DES PRODUITS AJOUTÉS -->
                <div class="mb-4 sm:mb-6">
                    <h3 class="text-lg font-semibold text-[#0b5f37] mb-3 sm:mb-4">📋 Produits ajoutés</h3>
                    <div id="produits-list" class="space-y-3 sm:space-y-4 min-h-20">
                        <!-- Message quand aucun produit -->
                        <div id="aucun-produit-message" class="text-center py-6 sm:py-8 text-gray-500 border-2 border-dashed border-gray-300 rounded-lg">
                            <p class="text-sm sm:text-base">Aucun produit ajouté</p>
                            <p class="text-xs sm:text-sm mt-1">Ajoutez des produits ci-dessus</p>
                        </div>
                    </div>
                </div>

                <!-- Section Totaux et Paiement -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                    <!-- Totaux -->
                    <div class="bg-gray-50 p-3 sm:p-4 rounded">
                        <h3 class="text-lg font-semibold text-[#0b5f37] mb-3 sm:mb-4">📊 Totaux</h3>
                        <div class="space-y-2 sm:space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm sm:text-base text-gray-700">Sous-total:</span>
                                <span id="sous-total" class="text-base sm:text-lg font-semibold">0 FCFA</span>
                            </div>
                            <div class="flex justify-between items-center border-t pt-2">
                                <span class="text-sm sm:text-base font-medium text-gray-700">Total à payer:</span>
                                <span id="total-montant" class="text-xl sm:text-2xl font-bold text-[#0b5f37]">0 FCFA</span>
                            </div>
                        </div>
                    </div>

                    <!-- Paiement et Monnaie -->
                    <div class="bg-blue-50 p-3 sm:p-4 rounded border border-blue-200">
                        <h3 class="text-lg font-semibold text-[#0b5f37] mb-3 sm:mb-4">💰 Paiement</h3>
                        <div class="space-y-3 sm:space-y-4">
                            <div>
                                <label for="cash_client" class="block text-sm font-medium text-gray-700">
                                    Montant remis *
                                </label>
                                <input type="number" id="cash_client" name="cash_client" min="0" step="1"
                                       class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm sm:text-base focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37]"
                                       placeholder="Montant remis" required
                                       value="{{ old('cash_client') }}">
                                @error('cash_client')
                                    <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div id="monnaie-section" class="hidden">
                                <div class="flex justify-between items-center p-2 sm:p-3 bg-green-50 rounded border border-green-200">
                                    <span class="font-semibold text-green-800 text-sm sm:text-base">Monnaie à rendre:</span>
                                    <span id="monnaie" class="text-lg sm:text-xl font-bold text-green-800">0 FCFA</span>
                                </div>
                            </div>

                            <div id="montant-insuffisant" class="hidden">
                                <div class="flex justify-between items-center p-2 sm:p-3 bg-red-50 rounded border border-red-200">
                                    <span class="font-semibold text-red-800 text-sm sm:text-base">Montant insuffisant:</span>
                                    <span id="manquant" class="text-lg sm:text-xl font-bold text-red-800">0 FCFA</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-4 sm:mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes (optionnel)</label>
                    <textarea name="notes" id="notes" rows="2"
                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm sm:text-base focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37]"
                        placeholder="Notes supplémentaires...">{{ old('notes') }}</textarea>
                </div>

                <!-- Boutons d'action -->
                <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 mt-4 sm:mt-6">
                    <a href="{{ route('ventes.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm sm:text-base text-center">
                        Annuler
                    </a>
                    <button type="submit" id="submit-btn" 
                            class="bg-[#0b5f37] text-white px-4 sm:px-6 py-2 rounded hover:bg-[#0a4d2c] font-semibold opacity-50 cursor-not-allowed text-sm sm:text-base"
                            disabled>
                        💰 Enregistrer la Vente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Template pour un produit ajouté -->
<template id="produit-template">
    <div class="produit-item bg-gray-50 p-3 sm:p-4 rounded border" data-product-id="">
        <div class="flex justify-between items-center">
            <div class="flex-1 min-w-0">
                <h4 class="font-semibold text-sm sm:text-base produit-designation truncate"></h4>
                <div class="text-xs sm:text-sm text-gray-600 mt-1">
                    <span class="produit-prix"></span> FCFA x 
                    <input type="number" class="produit-quantite-input w-16 text-center border border-gray-300 rounded px-1 py-1 text-sm" min="1" value="1">
                    = 
                    <span class="font-semibold produit-montant"></span> FCFA
                </div>
                <div class="text-xs text-gray-500 mt-1">
                    Stock disponible: <span class="stock-disponible font-medium"></span>
                </div>
            </div>
            <div class="flex items-center space-x-1 sm:space-x-2 ml-2">
                <button type="button" class="supprimer-produit bg-red-600 text-white px-2 py-1 rounded text-xs hover:bg-red-700">
                    ❌
                </button>
            </div>
        </div>
    </div>
</template>

<!-- Inclure HTML5 QR Code Scanner -->
<script src="https://unpkg.com/html5-qrcode/minified/html5-qrcode.min.js"></script>

<script>
// SCANNER AVEC FALLBACK COMPLET - FONCTIONNEL
class ProductScanner {
    constructor() {
        this.html5QrCode = null;
        this.isScanning = false;
        this.usingFallback = false;
        this.stream = null;
        this.init();
    }

    init() {
        console.log('🚀 Initialisation du scanner...');
        
        // Événements boutons
        this.setupEventListeners();
    }

    setupEventListeners() {
        const startCameraBtn = document.getElementById('startCamera');
        const stopCameraBtn = document.getElementById('stopCamera');
        const manualScanBtn = document.getElementById('manualScan');
        const searchByCodeBtn = document.getElementById('searchByCode');
        const manualCodeInput = document.getElementById('manualCode');
        const captureImageBtn = document.getElementById('captureImage');
        const uploadImageBtn = document.getElementById('uploadImage');
        const imageUploadInput = document.getElementById('imageUpload');

        if (startCameraBtn) {
            startCameraBtn.addEventListener('click', () => this.startCamera());
        }
        if (stopCameraBtn) {
            stopCameraBtn.addEventListener('click', () => this.stopCamera());
        }
        if (manualScanBtn) {
            manualScanBtn.addEventListener('click', () => this.showManualScan());
        }
        if (searchByCodeBtn) {
            searchByCodeBtn.addEventListener('click', () => this.searchByCode());
        }
        if (manualCodeInput) {
            manualCodeInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') this.searchByCode();
            });
        }
        if (captureImageBtn) {
            captureImageBtn.addEventListener('click', () => this.captureImage());
        }
        if (uploadImageBtn) {
            uploadImageBtn.addEventListener('click', () => imageUploadInput.click());
        }
        if (imageUploadInput) {
            imageUploadInput.addEventListener('change', (e) => this.handleImageUpload(e));
        }
    }

    async startCamera() {
        console.log('📷 Démarrage de la caméra...');
        
        try {
            // Cacher la saisie manuelle et afficher la caméra
            this.hideManualScan();
            this.showCameraSection();

            // Essayer d'abord avec HTML5 QR Code si disponible
            if (await this.tryHtml5QrCode()) {
                return;
            }

            // Fallback: utiliser l'API native de caméra
            console.log('🔄 Utilisation du fallback caméra native...');
            await this.startNativeCamera();

        } catch (err) {
            console.error('❌ Erreur caméra:', err);
            this.showCameraError(err);
        }
    }

    async tryHtml5QrCode() {
        // Vérifier si la bibliothèque est disponible
        if (typeof Html5Qrcode === 'undefined') {
            console.log('📚 Bibliothèque HTML5 QR Code non disponible');
            
            // Essayer de la charger dynamiquement
            try {
                await this.loadHtml5QrCode();
            } catch (loadError) {
                console.log('❌ Impossible de charger la bibliothèque:', loadError);
                return false;
            }
        }

        try {
            this.html5QrCode = new Html5Qrcode("reader");
            
            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };

            await this.html5QrCode.start(
                { facingMode: "environment" },
                config,
                (decodedText) => this.onScanSuccess(decodedText),
                (errorMessage) => console.log("Scan en cours...")
            );
            
            this.isScanning = true;
            this.usingFallback = false;
            console.log('✅ Scanner HTML5 QR Code démarré');
            return true;
            
        } catch (err) {
            console.log('❌ Scanner HTML5 QR Code échoué:', err);
            return false;
        }
    }

    loadHtml5QrCode() {
        return new Promise((resolve, reject) => {
            if (typeof Html5Qrcode !== 'undefined') {
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = 'https://unpkg.com/html5-qrcode/minified/html5-qrcode.min.js';
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    async startNativeCamera() {
        try {
            // Vérifier la disponibilité de l'API media
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error("L'accès à la caméra n'est pas supporté par ce navigateur");
            }

            // Demander l'accès à la caméra
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: "environment",
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            });

            // Afficher le flux vidéo
            const cameraPreview = document.getElementById('cameraPreview');
            if (cameraPreview) {
                const video = document.createElement('video');
                video.srcObject = this.stream;
                video.autoplay = true;
                video.playsInline = true;
                video.style.width = '100%';
                video.style.height = '100%';
                video.style.objectFit = 'cover';
                
                cameraPreview.innerHTML = '';
                cameraPreview.appendChild(video);
            }

            // Activer le mode fallback
            this.usingFallback = true;
            this.showFallbackUI();
            console.log('✅ Caméra native démarrée');

        } catch (err) {
            throw new Error("Impossible d'accéder à la caméra: " + err.message);
        }
    }

    showFallbackUI() {
        // Afficher le message de fallback
        const fallbackMsg = document.getElementById('cameraFallback');
        if (fallbackMsg) {
            fallbackMsg.classList.remove('hidden');
        }

        // Afficher les boutons de capture
        const captureBtn = document.getElementById('captureImage');
        const uploadBtn = document.getElementById('uploadImage');
        if (captureBtn) captureBtn.classList.remove('hidden');
        if (uploadBtn) uploadBtn.classList.remove('hidden');
    }

    hideFallbackUI() {
        const fallbackMsg = document.getElementById('cameraFallback');
        const captureBtn = document.getElementById('captureImage');
        const uploadBtn = document.getElementById('uploadImage');
        
        if (fallbackMsg) fallbackMsg.classList.add('hidden');
        if (captureBtn) captureBtn.classList.add('hidden');
        if (uploadBtn) uploadBtn.classList.add('hidden');
    }

    captureImage() {
        if (!this.stream) return;

        const cameraPreview = document.getElementById('cameraPreview');
        const video = cameraPreview?.querySelector('video');
        if (!video) return;

        // Créer un canvas pour capturer l'image
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Convertir en blob et traiter
        canvas.toBlob((blob) => {
            this.processImageBlob(blob, 'capture');
        }, 'image/jpeg', 0.8);
    }

    handleImageUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            alert('Veuillez sélectionner une image valide');
            return;
        }

        this.processImageBlob(file, 'upload');
    }

    processImageBlob(blob, source) {
        console.log(`🔍 Analyse de l'image (${source})...`);
        
        // Pour l'instant, on va simuler un scan manuel
        // Dans une vraie implémentation, vous utiliseriez une API de reconnaissance de code-barres
        alert(`📸 Image ${source === 'capture' ? 'capturée' : 'téléchargée'}!\n\nPour le moment, veuillez saisir manuellement le code-barres.`);
        
        // Rediriger vers la saisie manuelle
        this.stopCamera();
        this.showManualScan();
    }

    showCameraSection() {
        const cameraSection = document.getElementById('cameraSection');
        if (cameraSection) {
            cameraSection.classList.remove('hidden');
        }
    }

    hideCameraSection() {
        const cameraSection = document.getElementById('cameraSection');
        if (cameraSection) {
            cameraSection.classList.add('hidden');
        }
    }

    showManualScan() {
        this.stopCamera();
        const manualSection = document.getElementById('manualSection');
        const manualCodeInput = document.getElementById('manualCode');
        
        if (manualSection) {
            manualSection.classList.remove('hidden');
        }
        if (manualCodeInput) {
            manualCodeInput.focus();
        }
    }

    hideManualScan() {
        const manualSection = document.getElementById('manualSection');
        if (manualSection) {
            manualSection.classList.add('hidden');
        }
    }

    stopCamera() {
        console.log('🛑 Arrêt de la caméra...');
        
        // Arrêter HTML5 QR Code
        if (this.html5QrCode && this.isScanning) {
            this.html5QrCode.stop().then(() => {
                console.log('✅ Scanner HTML5 arrêté');
            }).catch(err => {
                console.error('❌ Erreur arrêt scanner HTML5:', err);
            });
        }

        // Arrêter le flux média
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }

        this.isScanning = false;
        this.usingFallback = false;
        this.hideCameraSection();
        this.hideFallbackUI();

        // Réinitialiser l'interface
        const cameraPreview = document.getElementById('cameraPreview');
        if (cameraPreview) {
            cameraPreview.innerHTML = `
                <div class="text-center">
                    <div class="text-4xl mb-2">📷</div>
                    <p>Initialisation de la caméra...</p>
                </div>
            `;
        }
    }

    async onScanSuccess(decodedText) {
        if (!this.isScanning) return;
        
        console.log("✅ Code scanné:", decodedText);
        this.stopCamera();
        await this.addProductByCode(decodedText);
    }

    async searchByCode() {
        const manualCodeInput = document.getElementById('manualCode');
        if (!manualCodeInput) return;
        
        const code = manualCodeInput.value.trim();
        if (!code) {
            alert('Veuillez entrer un code produit');
            return;
        }

        await this.addProductByCode(code);
        manualCodeInput.value = '';
        this.hideManualScan();
    }

    async addProductByCode(code) {
        console.log('🔍 Recherche du produit avec code:', code);
        
        try {
            // Recherche dans les produits disponibles
            const availableProductsInput = document.getElementById('available-products');
            if (availableProductsInput) {
                try {
                    const availableProducts = JSON.parse(availableProductsInput.value);
                    
                    // Chercher par code-barres exact
                    let product = availableProducts.find(p => 
                        p.code_barre && p.code_barre.toString() === code.toString()
                    );
                    
                    // Si pas trouvé, chercher par désignation
                    if (!product) {
                        product = availableProducts.find(p => 
                            p.designation.toLowerCase().includes(code.toLowerCase())
                        );
                    }
                    
                    if (product) {
                        console.log('✅ Produit trouvé:', product.designation);
                        this.selectProductFromScan(product);
                        return;
                    }
                } catch (e) {
                    console.error('Erreur parsing produits:', e);
                }
            }

            // Si pas trouvé localement
            alert('Produit non trouvé: ' + code + '\nVérifiez le code et réessayez.');

        } catch (error) {
            console.error('Erreur recherche produit:', error);
            alert('Erreur lors de la recherche du produit: ' + code);
        }
    }

    selectProductFromScan(product) {
        // Ajouter directement le produit avec quantité 1
        ajouterProduitDirect(product);
    }

    showCameraError(error) {
        let message = "Impossible d'accéder à la caméra. ";
        
        if (error.message.includes('permission') || error.name === 'NotAllowedError') {
            message += "Veuillez autoriser l'accès à la caméra dans les paramètres de votre navigateur.";
        } else if (error.message.includes('found') || error.name === 'NotFoundError') {
            message += "Aucune caméra n'a été détectée.";
        } else if (error.message.includes('support') || error.name === 'NotSupportedError') {
            message += "Votre navigateur ne supporte pas l'accès caméra.";
        } else {
            message += error.message || "Veuillez utiliser la saisie manuelle.";
        }
        
        alert(message);
        this.stopCamera();
        
        // Proposer la saisie manuelle
        setTimeout(() => {
            this.showManualScan();
        }, 1000);
    }
}

// CODE MODIFIÉ POUR L'AJOUT DIRECT AU CLIC AVEC QUANTITÉ ÉDITABLE
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initialisation de l\'application...');
    
    // Vérifier la compatibilité caméra
    const hasCamera = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
    console.log('📷 Caméra disponible:', hasCamera);
    
    // Initialiser le scanner
    const startCameraBtn = document.getElementById('startCamera');
    if (startCameraBtn) {
        new ProductScanner();
        console.log('✅ Scanner initialisé');
    }
    
    // ⚠️ CODE MODIFIÉ POUR L'AJOUT DIRECT AU CLIC AVEC QUANTITÉ ÉDITABLE ⚠️
    const produitsList = document.getElementById('produits-list');
    const aucunProduitMessage = document.getElementById('aucun-produit-message');
    const productSearch = document.getElementById('product_search');
    const searchResults = document.getElementById('search-results');
    const ajouterBtn = document.getElementById('ajouter-produit');
    const totalMontant = document.getElementById('total-montant');
    const sousTotal = document.getElementById('sous-total');
    const submitBtn = document.getElementById('submit-btn');
    const productInfo = document.getElementById('product-info');
    const produitTemplate = document.getElementById('produit-template');
    const cashClientInput = document.getElementById('cash_client');
    const monnaieSection = document.getElementById('monnaie-section');
    const monnaieElement = document.getElementById('monnaie');
    const montantInsuffisantSection = document.getElementById('montant-insuffisant');
    const manquantElement = document.getElementById('manquant');
    const monnaieHidden = document.getElementById('monnaie-hidden');
    const form = document.getElementById('vente-multiple-form');
    const availableProductsInput = document.getElementById('available-products');
    
    // Charger les produits disponibles depuis le champ caché
    let availableProducts = [];
    try {
        availableProducts = JSON.parse(availableProductsInput.value);
        console.log('📦 Produits chargés:', availableProducts.length);
    } catch (e) {
        console.error('Erreur parsing produits:', e);
    }
    
    let produits = [];
    let total = 0;
    let selectedProduct = null;
    let searchTimeout = null;

    // Fonction de recherche des produits
    function searchProducts(query) {
        if (query.length < 2) {
            searchResults.classList.add('hidden');
            return;
        }

        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        searchTimeout = setTimeout(() => {
            const results = availableProducts.filter(product => 
                product.designation.toLowerCase().includes(query.toLowerCase()) ||
                (product.code_barre && product.code_barre.toLowerCase().includes(query.toLowerCase()))
            );
            
            displaySearchResults(results);
        }, 200);
    }

    // Afficher les résultats de recherche - MODIFIÉE POUR AJOUT DIRECT
    function displaySearchResults(products) {
        searchResults.innerHTML = '';
        
        if (products.length === 0) {
            searchResults.innerHTML = '<div class="p-3 text-gray-500">Aucun produit trouvé</div>';
            searchResults.classList.remove('hidden');
            return;
        }

        products.forEach(product => {
            const div = document.createElement('div');
            div.className = 'p-3 border-b border-gray-200 hover:bg-gray-100 cursor-pointer product-result';
            div.setAttribute('data-product-id', product.id);
            
            // Vérifier si le produit est déjà dans la vente
            const isAlreadyAdded = produits.some(p => p.productId === product.id);
            
            div.innerHTML = `
                <div class="flex justify-between items-center">
                    <div class="flex-1">
                        <div class="font-semibold text-gray-800">${product.designation}</div>
                        <div class="text-sm text-gray-600 mt-1">
                            <span class="font-medium">Prix:</span> ${new Intl.NumberFormat().format(product.prix)} FCFA | 
                            <span class="font-medium">Stock:</span> ${product.quantite}
                            ${product.code_barre ? `| <span class="font-medium">Code:</span> ${product.code_barre}` : ''}
                        </div>
                    </div>
                    ${isAlreadyAdded ? 
                        '<span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded ml-2">Déjà ajouté</span>' : 
                        '<span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded ml-2">Cliquez pour ajouter</span>'
                    }
                </div>
            `;
            
            // MODIFICATION : Ajouter directement au clic si pas déjà ajouté
            div.addEventListener('click', () => {
                if (isAlreadyAdded) {
                    alert('Ce produit est déjà dans la vente');
                    return;
                }
                ajouterProduitDirect(product);
            });
            
            searchResults.appendChild(div);
        });
        
        searchResults.classList.remove('hidden');
    }

    // NOUVELLE FONCTION : Ajouter directement le produit avec quantité 1
    function ajouterProduitDirect(product) {
        // Vérifier le stock
        if (product.quantite <= 0) {
            alert(`❌ Stock insuffisant!\nStock disponible: ${product.quantite} unités`);
            return;
        }

        // Ajouter directement le produit avec quantité 1
        const existingIndex = produits.findIndex(p => p.productId === product.id);
        if (existingIndex !== -1) {
            // Si le produit existe déjà, augmenter la quantité de 1
            const nouvelleQuantite = produits[existingIndex].quantite + 1;
            
            if (nouvelleQuantite > product.quantite) {
                alert(`❌ Stock insuffisant!\nStock disponible: ${product.quantite} unités\nQuantité totale demandée: ${nouvelleQuantite}`);
                return;
            }
            
            produits[existingIndex].quantite = nouvelleQuantite;
            produits[existingIndex].montant = produits[existingIndex].quantite * product.prix;
            mettreAJourAffichageProduit(existingIndex);
        } else {
            const produit = {
                productId: product.id,
                designation: product.designation,
                prix: product.prix,
                quantite: 1, // Toujours quantité 1 par défaut
                montant: product.prix * 1,
                stock: product.quantite
            };
            produits.push(produit);
            afficherProduit(produit, produits.length - 1);
        }

        updateTotal();
        updateAucunProduitMessage();
        
        // Réinitialiser la recherche
        productSearch.value = '';
        searchResults.classList.add('hidden');
        selectedProduct = null;
        
        // Focus sur la recherche pour le prochain produit
        productSearch.focus();
    }

    // Fonction pour afficher un produit dans la liste
    function afficherProduit(produit, index) {
        const clone = produitTemplate.content.cloneNode(true);
        const item = clone.querySelector('.produit-item');
        
        item.setAttribute('data-product-id', produit.productId);
        item.setAttribute('data-index', index);
        
        item.querySelector('.produit-designation').textContent = produit.designation;
        item.querySelector('.produit-prix').textContent = new Intl.NumberFormat().format(produit.prix);
        item.querySelector('.produit-quantite-input').value = produit.quantite;
        item.querySelector('.produit-montant').textContent = new Intl.NumberFormat().format(produit.montant);
        item.querySelector('.stock-disponible').textContent = produit.stock;
        
        // Événement pour modifier la quantité
        const quantiteInput = item.querySelector('.produit-quantite-input');
        quantiteInput.addEventListener('input', function() {
            const nouvelleQuantite = parseInt(this.value) || 1;
            const stock = produit.stock;
            
            if (nouvelleQuantite < 1) {
                this.value = 1;
                return;
            }
            
            if (nouvelleQuantite > stock) {
                this.value = stock;
                alert(`⚠️ Quantité limitée à ${stock} (stock disponible)`);
                return;
            }
            
            produit.quantite = nouvelleQuantite;
            produit.montant = produit.prix * nouvelleQuantite;
            item.querySelector('.produit-montant').textContent = new Intl.NumberFormat().format(produit.montant);
            updateTotal();
        });
        
        quantiteInput.addEventListener('blur', function() {
            if (!this.value || parseInt(this.value) < 1) {
                this.value = 1;
                produit.quantite = 1;
                produit.montant = produit.prix;
                item.querySelector('.produit-montant').textContent = new Intl.NumberFormat().format(produit.montant);
                updateTotal();
            }
        });

        // Bouton supprimer
        item.querySelector('.supprimer-produit').addEventListener('click', function() {
            if (confirm(`Supprimer ${produit.designation} de la vente ?`)) {
                produits.splice(index, 1);
                item.remove();
                reindexerProduits();
                updateTotal();
                updateAucunProduitMessage();
            }
        });

        produitsList.appendChild(clone);
    }

    function mettreAJourAffichageProduit(index) {
        const produit = produits[index];
        const item = document.querySelector(`.produit-item[data-index="${index}"]`);
        
        if (item) {
            item.querySelector('.produit-quantite-input').value = produit.quantite;
            item.querySelector('.produit-montant').textContent = new Intl.NumberFormat().format(produit.montant);
        }
    }

    function reindexerProduits() {
        const items = produitsList.querySelectorAll('.produit-item');
        items.forEach((item, index) => {
            item.setAttribute('data-index', index);
        });
    }

    function updateTotal() {
        total = produits.reduce((sum, produit) => sum + produit.montant, 0);
        sousTotal.textContent = new Intl.NumberFormat().format(total) + ' FCFA';
        totalMontant.textContent = new Intl.NumberFormat().format(total) + ' FCFA';
        
        updateMonnaie();
        updateSubmitButton();
        updateHiddenFields();
    }

    function updateHiddenFields() {
        const oldFields = form.querySelectorAll('input[name^="products"]');
        oldFields.forEach(field => field.remove());

        produits.forEach((produit, index) => {
            const productIdField = document.createElement('input');
            productIdField.type = 'hidden';
            productIdField.name = `products[${index}][product_id]`;
            productIdField.value = produit.productId;

            const quantiteField = document.createElement('input');
            quantiteField.type = 'hidden';
            quantiteField.name = `products[${index}][quantite]`;
            quantiteField.value = produit.quantite;

            form.appendChild(productIdField);
            form.appendChild(quantiteField);
        });
    }

    function updateSubmitButton() {
        const cashClient = parseFloat(cashClientInput.value) || 0;
        const peutEnregistrer = produits.length > 0 && cashClient >= total;
        
        submitBtn.disabled = !peutEnregistrer;
        submitBtn.classList.toggle('opacity-50', !peutEnregistrer);
        submitBtn.classList.toggle('cursor-not-allowed', !peutEnregistrer);
        submitBtn.classList.toggle('hover:bg-[#0a4d2c]', peutEnregistrer);
        
        if (produits.length > 0 && cashClient < total) {
            submitBtn.title = 'Montant insuffisant';
        } else {
            submitBtn.title = '';
        }
    }

    function updateAucunProduitMessage() {
        if (produits.length === 0) {
            aucunProduitMessage.classList.remove('hidden');
        } else {
            aucunProduitMessage.classList.add('hidden');
        }
    }

    function updateMonnaie() {
        const cashClient = parseFloat(cashClientInput.value) || 0;
        const difference = cashClient - total;
        
        monnaieSection.classList.add('hidden');
        montantInsuffisantSection.classList.add('hidden');
        
        if (cashClient > 0 && total > 0) {
            if (difference >= 0) {
                monnaieSection.classList.remove('hidden');
                monnaieElement.textContent = new Intl.NumberFormat().format(difference) + ' FCFA';
                monnaieHidden.value = difference;
            } else {
                montantInsuffisantSection.classList.remove('hidden');
                manquantElement.textContent = new Intl.NumberFormat().format(Math.abs(difference)) + ' FCFA';
                monnaieHidden.value = 0;
            }
        } else {
            monnaieHidden.value = 0;
        }
        
        updateSubmitButton();
    }

    // Événements
    productSearch.addEventListener('input', function() {
        searchProducts(this.value);
    });

    productSearch.addEventListener('focus', function() {
        if (this.value.length >= 2) {
            searchProducts(this.value);
        }
    });

    ajouterBtn.addEventListener('click', function() {
        if (!selectedProduct) {
            alert('Veuillez sélectionner un produit en premier');
            productSearch.focus();
            return;
        }
        ajouterProduitDirect(selectedProduct);
    });

    cashClientInput.addEventListener('input', updateMonnaie);

    document.addEventListener('click', function(e) {
        if (!productSearch.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.add('hidden');
        }
    });

    cashClientInput.addEventListener('blur', function() {
        if (this.value) {
            this.value = parseFloat(this.value).toFixed(0);
            updateMonnaie();
        }
    });

    form.addEventListener('submit', function(e) {
        if (produits.length === 0) {
            e.preventDefault();
            alert('Veuillez ajouter au moins un produit avant d\'enregistrer la vente.');
            productSearch.focus();
            return;
        }

        const cashClient = parseFloat(cashClientInput.value) || 0;
        if (cashClient < total) {
            e.preventDefault();
            alert(`Montant insuffisant!\nTotal à payer: ${new Intl.NumberFormat().format(total)} FCFA\nMontant remis: ${new Intl.NumberFormat().format(cashClient)} FCFA\nManquant: ${new Intl.NumberFormat().format(total - cashClient)} FCFA`);
            cashClientInput.focus();
            return;
        }
    });

    updateAucunProduitMessage();
    productSearch.focus();
    
    console.log('✅ Application initialisée avec succès');
});

// Code de secours si la bibliothèque échoue
window.addEventListener('error', function(e) {
    if (e.message.includes('Html5Qrcode') || e.filename.includes('html5-qrcode')) {
        console.warn('⚠️ Bibliothèque HTML5 QR Code non disponible');
    }
});
</script>

<style>
/* Styles améliorés pour le scanner */
#cameraPreview {
    background: #f8f9fa;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 300px;
}

#cameraPreview video {
    border-radius: 8px;
    max-width: 100%;
    max-height: 400px;
}

.hidden {
    display: none !important;
}

/* Styles responsives */
@media (max-width: 768px) {
    input, select, textarea {
        font-size: 16px !important;
    }
    
    #reader {
        min-height: 250px;
    }
}

/* Animation de chargement */
@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.loading {
    animation: pulse 2s infinite;
}

/* Styles préservés - ORIGINAL */
@media (max-width: 640px) {
    .min-w-0 {
        min-width: 0;
    }
}

#search-results {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f7fafc;
}

#search-results::-webkit-scrollbar {
    width: 6px;
}

#search-results::-webkit-scrollbar-track {
    background: #f7fafc;
}

#search-results::-webkit-scrollbar-thumb {
    background-color: #cbd5e0;
    border-radius: 3px;
}

.product-result {
    transition: all 0.2s ease;
}

.product-result:hover {
    background-color: #e6f0ff !important;
}

/* Style pour l'input de quantité dans la liste */
.produit-quantite-input {
    border: 1px solid #d1d5db;
    border-radius: 4px;
    text-align: center;
    font-size: 0.875rem;
}

.produit-quantite-input:focus {
    outline: none;
    ring: 2px;
    ring-color: #0b5f37;
    border-color: #0b5f37;
}
</style>
@endsection