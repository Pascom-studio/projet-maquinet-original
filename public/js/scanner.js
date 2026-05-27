class ProductScanner {
    constructor() {
        this.html5QrCode = null;
        this.isScanning = false;
        this.init();
    }

    init() {
        // Événements boutons
        document.getElementById('startCamera')?.addEventListener('click', () => this.startCamera());
        document.getElementById('stopCamera')?.addEventListener('click', () => this.stopCamera());
        document.getElementById('manualScan')?.addEventListener('click', () => this.showManualScan());
        document.getElementById('searchByCode')?.addEventListener('click', () => this.searchByCode());
        document.getElementById('manualCode')?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.searchByCode();
        });
    }

    async startCamera() {
        try {
            this.hideManualScan();
            document.getElementById('cameraSection').classList.remove('hidden');
            
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
                (errorMessage) => {}
            );
            
            this.isScanning = true;
            
        } catch (err) {
            console.error("Erreur caméra:", err);
            alert("Impossible d'accéder à la caméra: " + err.message);
        }
    }

    stopCamera() {
        if (this.html5QrCode && this.isScanning) {
            this.html5QrCode.stop().then(() => {
                this.isScanning = false;
                document.getElementById('cameraSection').classList.add('hidden');
            }).catch(err => {
                console.error("Erreur arrêt caméra:", err);
            });
        }
    }

    showManualScan() {
        this.stopCamera();
        document.getElementById('manualSection').classList.remove('hidden');
        document.getElementById('manualCode').focus();
    }

    hideManualScan() {
        document.getElementById('manualSection').classList.add('hidden');
    }

    async onScanSuccess(decodedText) {
        if (!this.isScanning) return;
        
        this.stopCamera();
        await this.addProductByCode(decodedText);
    }

    async searchByCode() {
        const code = document.getElementById('manualCode').value.trim();
        if (!code) {
            alert('Veuillez entrer un code produit');
            return;
        }

        await this.addProductByCode(code);
        document.getElementById('manualCode').value = '';
        this.hideManualScan();
    }

    async addProductByCode(code) {
        try {
            const response = await fetch(`/api/products/find-by-code/${encodeURIComponent(code)}`);
            const data = await response.json();

            if (data.success && data.product) {
                this.addProductToCart(data.product);
            } else {
                alert('Produit non trouvé: ' + code);
            }
        } catch (error) {
            console.error('Erreur recherche produit:', error);
            alert('Erreur lors de la recherche du produit');
        }
    }

    addProductToCart(product) {
        // Vérifier si le produit est déjà dans le panier
        const existingProduct = document.querySelector(`[data-product-id="${product.id}"]`);
        
        if (existingProduct) {
            // Incrémenter la quantité
            const quantityInput = existingProduct.querySelector('.product-quantity');
            quantityInput.value = parseInt(quantityInput.value) + 1;
            this.updateProductTotal(existingProduct);
        } else {
            // Ajouter nouveau produit
            this.addNewProductToCart(product);
        }

        this.updateCartTotal();
    }

    addNewProductToCart(product) {
        const productsContainer = document.getElementById('products-container');
        const productHtml = `
            <div class="product-item border rounded-lg p-4 mb-3" data-product-id="${product.id}">
                <div class="flex justify-between items-center">
                    <div class="flex-1">
                        <h4 class="font-semibold">${product.designation}</h4>
                        <p class="text-gray-600">${product.prix.toLocaleString()} FCFA</p>
                        <p class="text-sm text-gray-500">Stock: ${product.quantite}</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button type="button" class="decrease-quantity bg-gray-200 w-8 h-8 rounded-full">-</button>
                        <input type="number" name="products[${product.id}][quantite]" 
                               value="1" min="1" max="${product.quantite}"
                               class="product-quantity w-16 text-center border rounded" readonly>
                        <button type="button" class="increase-quantity bg-gray-200 w-8 h-8 rounded-full">+</button>
                        <button type="button" class="remove-product text-red-600 ml-2">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <input type="hidden" name="products[${product.id}][product_id]" value="${product.id}">
                <div class="product-total text-right font-semibold mt-2">
                    Total: <span class="total-amount">${product.prix.toLocaleString()}</span> FCFA
                </div>
            </div>
        `;
        
        productsContainer.insertAdjacentHTML('beforeend', productHtml);
        this.attachProductEvents(product.id);
    }

    attachProductEvents(productId) {
        const productElement = document.querySelector(`[data-product-id="${productId}"]`);
        
        productElement.querySelector('.increase-quantity').addEventListener('click', () => {
            const input = productElement.querySelector('.product-quantity');
            const max = parseInt(input.max);
            if (input.value < max) {
                input.value = parseInt(input.value) + 1;
                this.updateProductTotal(productElement);
                this.updateCartTotal();
            }
        });

        productElement.querySelector('.decrease-quantity').addEventListener('click', () => {
            const input = productElement.querySelector('.product-quantity');
            if (input.value > 1) {
                input.value = parseInt(input.value) - 1;
                this.updateProductTotal(productElement);
                this.updateCartTotal();
            }
        });

        productElement.querySelector('.remove-product').addEventListener('click', () => {
            productElement.remove();
            this.updateCartTotal();
        });
    }

    updateProductTotal(productElement) {
        const quantity = parseInt(productElement.querySelector('.product-quantity').value);
        const price = parseFloat(productElement.querySelector('.text-gray-600').textContent.replace(/[^\d]/g, ''));
        const total = quantity * price;
        
        productElement.querySelector('.total-amount').textContent = total.toLocaleString();
    }

    updateCartTotal() {
        // Votre logique existante pour mettre à jour le total du panier
        if (typeof updateCartTotal === 'function') {
            updateCartTotal();
        }
    }
}

// Initialiser le scanner quand la page est chargée
document.addEventListener('DOMContentLoaded', function() {
    new ProductScanner();
});