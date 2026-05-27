<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reçu de Vente - {{ $vente->numero_vente }}</title>
    <style>
        body { 
            font-family: 'Courier New', monospace; 
            font-size: 12px; 
            width: 80mm; 
            margin: 0; 
            padding: 5px;
            line-height: 1.2;
        }
        .header { 
            text-align: center; 
            margin-bottom: 8px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }
        .line { 
            border-bottom: 1px dashed #000; 
            margin: 4px 0; 
        }
        .item { 
            display: flex; 
            justify-content: space-between; 
            margin: 2px 0; 
            font-size: 11px;
        }
        .item-name {
            flex: 2;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .item-details {
            flex: 1;
            text-align: right;
        }
        .total { 
            font-weight: bold; 
            margin-top: 8px;
            border-top: 2px solid #000;
            padding-top: 4px;
        }
        .footer { 
            text-align: center; 
            margin-top: 8px; 
            font-size: 10px;
            border-top: 1px dashed #000;
            padding-top: 8px;
        }
        .thank-you {
            text-align: center;
            margin: 6px 0;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin: 2px 0; font-size: 14px;">GESTCOOL</h2>
        <p style="margin: 2px 0; font-size: 10px;">Système de Gestion de Stock</p>
        <p style="margin: 2px 0; font-size: 10px;">{{ config('app.url') }}</p>
    </div>
    
    <div class="line"></div>
    
    <p style="margin: 2px 0;"><strong>Reçu N°:</strong> {{ $vente->numero_vente }}</p>
    <p style="margin: 2px 0;"><strong>Date:</strong> {{ $vente->created_at->format('d/m/Y H:i') }}</p>
    <p style="margin: 2px 0;"><strong>Caissier:</strong> {{ $vente->user->prenom }}</p>
    
    <div class="line"></div>
    
    <div class="item" style="font-weight: bold;">
        <span class="item-name">PRODUIT</span>
        <span class="item-details">MONTANT</span>
    </div>
    
    @foreach($vente->lignes as $ligne)
    <div class="item">
        <span class="item-name">
            {{ substr($ligne->product->designation, 0, 20) }}{{ strlen($ligne->product->designation) > 20 ? '...' : '' }}
        </span>
        <span class="item-details">
            {{ number_format($ligne->montant_ligne, 0, ',', ' ') }}
        </span>
    </div>
    <div class="item" style="font-size: 10px; color: #666;">
        <span class="item-name">
            {{ $ligne->quantite }} x {{ number_format($ligne->prix_unitaire, 0, ',', ' ') }}
        </span>
        <span class="item-details"></span>
    </div>
    @endforeach
    
    <div class="line"></div>
    
    <div class="item total">
        <span>TOTAL:</span>
        <span>{{ number_format($vente->montant_total, 0, ',', ' ') }} FCFA</span>
    </div>

    <div class="thank-you">
        Merci de votre visite !
    </div>
    
    <div class="footer">
        <p style="margin: 2px 0;">Reçu émis électroniquement</p>
        <p style="margin: 2px 0;">Pas de valeur fiscale</p>
    </div>

    <script>
        // Impression automatique
        window.onload = function() {
            window.print();
            setTimeout(function() {
                window.close();
            }, 500);
        };
    </script>
</body>
</html>