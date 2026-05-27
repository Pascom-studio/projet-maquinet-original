<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Export Transactions Mobile Money</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0b5f37; padding-bottom: 10px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th { background-color: #0b5f37; color: white; padding: 8px; text-align: left; }
        .table td { padding: 6px; border: 1px solid #ddd; }
        .summary { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 20px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bg-success { background-color: #d4edda; }
        .bg-info { background-color: #d1ecf1; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport des Transactions Mobile Money</h1>
        <p>Période: {{ $statistiques['periode'] }}</p>
        <p>Filtres: Type: {{ $filtres['type_operation'] }} | Opérateur: {{ $filtres['nature'] }}</p>
    </div>

    <!-- CORRECTION : Vérification de l'existence de $transactions -->
    @php
        $transactions = $transactions ?? collect();
    @endphp

    <!-- Résumé -->
    <div class="summary">
        <h3>Résumé de la période</h3>
        <table width="100%">
            <tr>
                <td width="20%"><strong>Total Transactions:</strong> {{ $statistiques['total_transactions'] }}</td>
                <td width="20%"><strong>Total Dépôts:</strong> {{ number_format($statistiques['total_depots'], 0, ',', ' ') }} FCFA</td>
                <td width="20%"><strong>Total Retraits:</strong> {{ number_format($statistiques['total_retraits'], 0, ',', ' ') }} FCFA</td>
                <td width="20%"><strong>Total Commissions:</strong> {{ number_format($statistiques['total_commissions'], 0, ',', ' ') }} FCFA</td>
                <td width="20%"><strong>Solde Net:</strong> {{ number_format($statistiques['solde_net'], 0, ',', ' ') }} FCFA</td>
            </tr>
        </table>
    </div>

    <!-- Tableau des transactions -->
    @if($transactions->count() > 0)
    <table class="table">
        <thead>
            <tr>
                <th>ID Transaction</th>
                <th>Date/Heure</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Téléphone</th>
                <th>Type</th>
                <th>Opérateur</th>
                <th class="text-right">Montant</th>
                <th class="text-right">Commission</th>
                <th>CNIB</th>
                <th>Caissier</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
            <tr>
                <td>{{ $transaction->id_transaction }}</td>
                <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $transaction->nom }}</td>
                <td>{{ $transaction->prenom }}</td>
                <td>{{ $transaction->telephone }}</td>
                <td>{{ $getTypeOperationLabel($transaction->type_operation) }}</td>
                <td>{{ $getOperateurNom($transaction->nature) }}</td>
                <td class="text-right">{{ number_format($transaction->montant, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($transaction->commission, 0, ',', ' ') }} F</td>
                <td>{{ $transaction->cnib ?? 'N/A' }}</td>
                <td>{{ $transaction->user->name ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totaux détaillés -->
    <div class="summary" style="margin-top: 30px;">
        <h3>Détail des totaux</h3>
        <table width="100%">
            <tr>
                <td width="25%"><strong>Nombre de transactions:</strong> {{ $statistiques['total_transactions'] }}</td>
                <td width="25%"><strong>Volume total des dépôts:</strong> {{ number_format($statistiques['total_depots'], 0, ',', ' ') }} FCFA</td>
                <td width="25%"><strong>Volume total des retraits:</strong> {{ number_format($statistiques['total_retraits'], 0, ',', ' ') }} FCFA</td>
                <td width="25%"><strong>Commissions totales:</strong> {{ number_format($statistiques['total_commissions'], 0, ',', ' ') }} FCFA</td>
            </tr>
        </table>
    </div>
    @else
    <div class="summary" style="background-color: #fff3cd; border: 1px solid #ffeaa7;">
        <h3 style="color: #856404;">Aucune donnée disponible</h3>
        <p style="color: #856404;">Aucune transaction ne correspond aux critères sélectionnés pour la période spécifiée.</p>
    </div>
    @endif

    <div style="margin-top: 40px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 10px;">
        Document généré le {{ now()->format('d/m/Y à H:i') }} - Système Mobile Money
    </div>
</body>
</html>