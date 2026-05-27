<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu de Commande - GestCool</title>
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
        }
        
        .receipt-container {
            max-width: 280px;
            margin: 0 auto;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.2;
        }
        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px dashed #000;
            padding-bottom: 8px;
        }
        .line {
            border-bottom: 1px dashed #ccc;
            margin: 8px 0;
        }
        .item {
            display: flex;
            justify-content: space-between;
            margin: 4px 0;
        }
        .product-item {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            padding: 2px 0;
        }
        .product-name {
            flex: 2;
            text-align: left;
        }
        .product-details {
            flex: 1;
            text-align: right;
        }
        .total {
            font-weight: bold;
            font-size: 15px;
            border-top: 2px solid #000;
            padding-top: 8px;
            margin-top: 8px;
        }
        .text-xs {
            font-size: 11px;
        }
        .text-sm {
            font-size: 12px;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="no-print py-4 text-center">
        <button onclick="window.print()" class="bg-[#0b5f37] text-white px-6 py-2 rounded hover:bg-[#0a4d2c] text-sm">
            🖨️ Imprimer le Reçu
        </button>
        <a href="{{ route('commandes.soldees') }}" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 ml-2 text-sm">
            ← Retour à la Liste
        </a>
    </div>

    <div class="receipt-container bg-white shadow-lg">
        <!-- En-tête -->
        <div class="header">
            <h1 class="text-lg font-bold">🍊 GESTCOOL</h1>
            <p class="text-xs"></p>
            <p class="text-xs">Reçu de Commande</p>
        </div>
        
        <div class="line"></div>
        
        <!-- Informations de la commande -->
        <div class="space-y-1 text-xs">
            <div class="flex justify-between">
                <span><strong>Commande N°:</strong></span>
                <span>{{ $commande->numero_commande ?? 'CMD-' . $commande->id }}</span>
            </div>
            <div class="flex justify-between">
                <span><strong>Date:</strong></span>
                <span>{{ $commande->created_at_formatted }}</span>
            </div>
            <div class="flex justify-between">
                <span><strong>Table:</strong></span>
                <span>
                    @if($commande->table)
                        Table {{ $commande->table->numero }}
                    @else
                        Table inconnue
                    @endif
                </span>
            </div>
            <div class="flex justify-between">
                <span><strong>Hôtesse:</strong></span>
                <span>
                    @if($commande->hotesse)
                        {{ $commande->hotesse->prenom ?? $commande->hotesse->name }}
                    @else
                        Hôtesse inconnue
                    @endif
                </span>
            </div>
            <div class="flex justify-between">
                <span><strong>Caissier:</strong></span>
                <span>
                    @if($commande->user)
                        {{ $commande->user->prenom ?? $commande->user->name }}
                    @else
                        Caissier inconnu
                    @endif
                </span>
            </div>
        </div>
        
        <div class="line"></div>
        
        <!-- En-tête produits -->
        <div class="item text-xs">
            <span><strong>PRODUIT</strong></span>
            <span><strong>TOTAL</strong></span>
        </div>
        
        <!-- Liste des produits -->
        @php
            $produitsData = $produitsData ?? $commande->produits_pour_recu;
        @endphp
        
        @if($produitsData && count($produitsData) > 0)
            @foreach($produitsData as $produit)
            <div class="product-item">
                <div class="product-name">
                    <div class="font-medium">{{ $produit['designation'] }}</div>
                    <div class="text-xs text-gray-600">
                        x{{ $produit['quantite'] }} @ {{ number_format($produit['prix_unitaire'], 0, ',', ' ') }} F
                    </div>
                </div>
                <div class="product-details font-semibold">
                    {{ number_format($produit['total'], 0, ',', ' ') }} F
                </div>
            </div>
            @endforeach
        @else
            <div class="product-item">
                <span>Aucun produit</span>
                <span>0 F</span>
            </div>
        @endif
        
        <div class="line"></div>
        
        <!-- Total -->
        <div class="item total">
            <span>TOTAL</span>
            <span>{{ number_format($commande->montant, 0, ',', ' ') }} FCFA</span>
        </div>

        <!-- Informations de paiement -->
        <div class="space-y-1 mt-3 text-xs">
            <div class="flex justify-between">
                <span><strong>Statut:</strong></span>
                <span class="font-semibold text-green-600">SOLDEÉ</span>
            </div>
            <div class="flex justify-between">
                <span><strong>Méthode paiement:</strong></span>
                <span>{{ ucfirst($commande->methode_paiement ?? 'Non spécifiée') }}</span>
            </div>
            @if($commande->montant_remis)
            <div class="flex justify-between">
                <span><strong>Montant remis:</strong></span>
                <span>{{ number_format($commande->montant_remis, 0, ',', ' ') }} FCFA</span>
            </div>
            @endif
            @if($commande->monnaie_rendue)
            <div class="flex justify-between">
                <span><strong>Monnaie rendue:</strong></span>
                <span>{{ number_format($commande->monnaie_rendue, 0, ',', ' ') }} FCFA</span>
            </div>
            @endif
        </div>
        
        <!-- Notes -->
        @if($commande->notes)
        <div class="mt-3 p-2 bg-gray-100 rounded text-xs">
            <strong>Notes:</strong> {{ $commande->notes }}
        </div>
        @endif
        
        <!-- Pied de page CORRIGÉ : Utilisation des accesseurs du modèle -->
        <div class="text-center mt-4 text-xs">
            <p>Merci pour votre confiance !</p>
            <p><strong>{{ $commande->nom_admin_parent }}</strong></p>
            <p>{{ $commande->titre_admin_parent }}</p>
            <p class="text-gray-600">Soldée le: {{ $commande->updated_at_formatted }}</p>
        </div>
    </div>

    <script>
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
                window.location.href = "{{ route('commandes.soldees') }}";
            }, 1000);
        };
    </script>
</body>
</html>