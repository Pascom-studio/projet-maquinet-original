<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu de Vente - GestCool</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; }
            .receipt-container { 
                box-shadow: none !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 10px !important;
            }
            .mobile-only { display: none !important; }
        }
        
        @media screen and (max-width: 640px) {
            .receipt-container {
                max-width: 100%;
                margin: 10px;
                padding: 15px;
                font-size: 12px;
            }
            .desktop-only { display: none !important; }
            .mobile-only { display: block !important; }
        }
        
        .receipt-container {
            max-width: 300px;
            margin: 0 auto;
            padding: 20px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
        }
        .line {
            border-bottom: 1px dashed #ccc;
            margin: 10px 0;
        }
        .item {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }
        .total {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #000;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        /* Styles mobiles améliorés */
        .mobile-actions {
            position: sticky;
            top: 0;
            background: white;
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            z-index: 50;
        }
        
        .mobile-product {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 8px 0;
            border-bottom: 1px dashed #e5e7eb;
        }
        
        .product-info {
            flex: 1;
            margin-right: 10px;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Actions mobiles -->
    <div class="no-print mobile-actions sm:hidden">
        <div class="flex space-x-2">
            <button onclick="window.print()" class="flex-1 bg-[#0b5f37] text-white px-3 py-2 rounded text-sm hover:bg-[#0a4d2c] text-center">
                🖨️ Imprimer
            </button>
            <a href="{{ route('ventes.show', $vente->id) }}" class="flex-1 bg-gray-500 text-white px-3 py-2 rounded text-sm hover:bg-gray-600 text-center">
                ← Retour
            </a>
        </div>
    </div>

    <!-- Actions desktop -->
    <div class="no-print py-4 text-center hidden sm:block">
        <button onclick="window.print()" class="bg-[#0b5f37] text-white px-6 py-2 rounded hover:bg-[#0a4d2c]">
            🖨️ Imprimer le Reçu
        </button>
        <a href="{{ route('ventes.show', $vente->id) }}" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 ml-2">
            ← Retour aux Détails
        </a>
    </div>

    <div class="receipt-container bg-white shadow-lg">
        <!-- En-tête -->
        <div class="header">
            <h1 class="text-xl font-bold">🍊 GESTCOOL</h1>
            <p class="text-sm"></p>
            <p class="text-xs">Reçu de Vente</p>
        </div>
        
        <div class="line"></div>
        
        <!-- Informations de la vente -->
        <div class="space-y-1 text-sm">
            <div class="flex justify-between">
                <span><strong>Reçu N°:</strong></span>
                <span>{{ $vente->numero_vente ?? 'V-' . $vente->id }}</span>
            </div>
            <div class="flex justify-between">
                <span><strong>Date:</strong></span>
                <span>{{ $vente->created_at_formatted }}</span>
            </div>
            <div class="flex justify-between">
                <span><strong>Caissier:</strong></span>
                <span>{{ $vente->user->prenom ?? $vente->user->name }}</span>
            </div>
        </div>
        
        <div class="line"></div>
        
        <!-- Produits version desktop -->
        <div class="desktop-only">
            <div class="item">
                <span><strong>Produit</strong></span>
                <span><strong>Total</strong></span>
            </div>
            
            @if($vente->lignesVente && $vente->lignesVente->count() > 0)
                @foreach($vente->lignesVente as $ligne)
                <div class="item">
                    <span>
                        {{ $ligne->product->designation ?? 'Produit' }} 
                        x{{ $ligne->quantite }}
                        @if($ligne->prix_unitaire)
                            @ {{ number_format($ligne->prix_unitaire, 0, ',', ' ') }} F
                        @endif
                    </span>
                    <span>{{ number_format($ligne->montant_total, 0, ',', ' ') }} F</span>
                </div>
                @endforeach
            @else
                <div class="item">
                    <span>
                        {{ $vente->designation ?? 'Produit' }} 
                        x{{ $vente->quantite ?? 1 }}
                    </span>
                    <span>{{ number_format($vente->montant_total ?? $vente->montant, 0, ',', ' ') }} F</span>
                </div>
            @endif
        </div>

        <!-- Produits version mobile -->
        <div class="mobile-only">
            <div class="item mb-2">
                <span><strong>Produit</strong></span>
                <span><strong>Total</strong></span>
            </div>
            
            @if($vente->lignesVente && $vente->lignesVente->count() > 0)
                @foreach($vente->lignesVente as $ligne)
                <div class="mobile-product">
                    <div class="product-info">
                        <div class="font-medium">{{ $ligne->product->designation ?? 'Produit' }}</div>
                        <div class="text-xs text-gray-600">
                            x{{ $ligne->quantite }}
                            @ {{ number_format($ligne->prix_unitaire, 0, ',', ' ') }} F
                        </div>
                    </div>
                    <span class="font-semibold">{{ number_format($ligne->montant_total, 0, ',', ' ') }} F</span>
                </div>
                @endforeach
            @else
                <div class="mobile-product">
                    <div class="product-info">
                        <div class="font-medium">{{ $vente->designation ?? 'Produit' }}</div>
                        <div class="text-xs text-gray-600">x{{ $vente->quantite ?? 1 }}</div>
                    </div>
                    <span class="font-semibold">{{ number_format($vente->montant_total ?? $vente->montant, 0, ',', ' ') }} F</span>
                </div>
            @endif
        </div>
        
        <div class="line"></div>
        
        <!-- Total -->
        <div class="item total">
            <span>TOTAL</span>
            <span>{{ number_format($vente->montant_total ?? $vente->montant, 0, ',', ' ') }} FCFA</span>
        </div>

        <!-- AJOUT : Montant remis et monnaie -->
        <div class="space-y-2 mt-3 text-sm">
            <div class="item">
                <span><strong>Montant remis:</strong></span>
                <span>{{ number_format($vente->cash_client ?? 0, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="item">
                <span><strong>Monnaie rendue:</strong></span>
                <span>{{ number_format($vente->monnaie_rendue ?? 0, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>
        
        <!-- Notes -->
        @if($vente->notes)
        <div class="mt-4 p-2 bg-gray-100 rounded text-xs">
            <strong>Notes:</strong> {{ $vente->notes }}
        </div>
        @endif
        
        <!-- Pied de page CORRIGÉ : Utilisation des accesseurs du modèle -->
        <div class="text-center mt-6 text-xs">
            <p>Merci pour votre confiance !</p>
            <p><strong>{{ $vente->nom_admin_parent }}</strong></p>
            <p>{{ $vente->titre_admin_parent }}</p>
            <p>{{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <!-- Instructions mobiles -->
    <div class="no-print sm:hidden mt-4 text-center text-xs text-gray-600 px-4">
        <p>📱 Pour imprimer, utilisez le bouton ci-dessus ou le menu partage de votre appareil</p>
    </div>

    <script>
        // Impression automatique sur desktop seulement
        window.onload = function() {
            if (window.innerWidth > 640) {
                setTimeout(function() {
                    window.print();
                }, 1000);
            }
        };

        // Retour après impression
        window.onafterprint = function() {
            setTimeout(function() {
                window.location.href = "{{ route('ventes.show', $vente->id) }}";
            }, 1000);
        };
        
        // Gestion du redimensionnement
        window.addEventListener('resize', function() {
            if (window.innerWidth <= 640) {
                document.querySelectorAll('.desktop-only').forEach(el => el.style.display = 'none');
                document.querySelectorAll('.mobile-only').forEach(el => el.style.display = 'block');
            } else {
                document.querySelectorAll('.desktop-only').forEach(el => el.style.display = 'block');
                document.querySelectorAll('.mobile-only').forEach(el => el.style.display = 'none');
            }
        });
    </script>
</body>
</html>