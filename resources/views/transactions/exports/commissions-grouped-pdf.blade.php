<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Export Commissions Groupées Mobile Money</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #8c52ff; padding-bottom: 10px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th { background-color: #8c52ff; color: white; padding: 8px; text-align: left; }
        .table td { padding: 6px; border: 1px solid #ddd; }
        .summary { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 20px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bg-info { background-color: #e8f4fd; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport des Commissions Mobile Money - Groupé par Mois/Opérateur</h1>
        <p>Période: {{ $statistiques['periode'] ?? 'Non spécifiée' }}</p>
        <p>Filtres: Opérateur: {{ $filtres['operateur'] ?? 'Tous' }} | Type: {{ $filtres['type_operation'] ?? 'Tous' }}</p>
    </div>

    @php
        $commissions = $commissions ?? collect();
    @endphp

    @if($commissions->count() > 0)
    <!-- Tableau des commissions groupées -->
    <table class="table">
        <thead>
            <tr>
                <th>Période</th>
                <th>Opérateur</th>
                <th>Type Opération</th>
                <th>Nombre Transactions</th>
                <th class="text-right">Montant Total</th>
                <th class="text-right">Commission Totale</th>
                <th class="text-right">Commission Moyenne</th>
            </tr>
        </thead>
        <tbody>
            @foreach($commissions as $commission)
            @php
                $commissionMoyenne = $commission->nombre_transactions > 0 
                    ? $commission->commission_totale / $commission->nombre_transactions 
                    : 0;
            @endphp
            <tr>
                <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $commission->periode)->format('F Y') }}</td>
                <td>{{ $getOperateurNom($commission->operateur) }}</td>
                <td>{{ $getTypeOperationLabel($commission->type_operation) }}</td>
                <td>{{ $commission->nombre_transactions }}</td>
                <td class="text-right">{{ number_format($commission->montant_total, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($commission->commission_totale, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($commissionMoyenne, 0, ',', ' ') }} F</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Statistiques générales -->
    <div class="summary bg-info">
        <h3>Statistiques Générales</h3>
        <table width="100%">
            <tr>
                <td width="25%"><strong>Total Transactions:</strong> {{ $statistiques['total_transactions'] ?? 0 }}</td>
                <td width="25%"><strong>Total Commissions:</strong> {{ number_format($statistiques['total_commissions'] ?? 0, 0, ',', ' ') }} FCFA</td>
                <td width="25%"><strong>Commission Moyenne:</strong> {{ number_format($statistiques['commission_moyenne'] ?? 0, 0, ',', ' ') }} FCFA</td>
            </tr>
        </table>
    </div>

    <!-- Répartition par opérateur -->
    <div class="summary">
        <h3>Répartition par Opérateur</h3>
        <table width="100%">
            @foreach($statistiques['commissions_par_operateur'] ?? [] as $operateur => $data)
            @if($data['transactions'] > 0)
            <tr>
                <td width="40%"><strong>{{ $getOperateurNom($operateur) }}:</strong></td>
                <td width="30%">{{ number_format($data['commission'], 0, ',', ' ') }} FCFA</td>
                <td width="30%">({{ $data['transactions'] }} transactions - {{ number_format($data['pourcentage'], 1, ',', ' ') }}%)</td>
            </tr>
            @endif
            @endforeach
        </table>
    </div>
    @else
    <div class="summary" style="background-color: #fff3cd; border: 1px solid #ffeaa7;">
        <h3 style="color: #856404;">Aucune donnée disponible</h3>
        <p style="color: #856404;">Aucune commission ne correspond aux critères sélectionnés pour la période spécifiée.</p>
    </div>
    @endif

    <div style="margin-top: 40px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 10px;">
        Document généré le {{ now()->format('d/m/Y à H:i') }} - Système Mobile Money
    </div>
</body>
</html>