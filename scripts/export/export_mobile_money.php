<?php
// scripts/export/export_mobile_money.php

// Remonter d'un niveau pour atteindre la racine Laravel
$laravelRoot = dirname(__DIR__, 2);
require_once $laravelRoot . '/vendor/autoload.php';

$app = require_once $laravelRoot . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

function exportMobileMoneyData() {
    try {
        echo "🔍 Début de l'export des données Mobile Money...\n";
        
        // Récupérer toutes les données
        $transactions = DB::table('mobile_money_transactions')->get();
        
        echo "📊 " . $transactions->count() . " transactions trouvées\n";
        
        if ($transactions->count() === 0) {
            echo "❌ Aucune donnée à exporter\n";
            return false;
        }
        
        // Préparer l'export
        $exportData = [
            'metadata' => [
                'export_date' => now()->toISOString(),
                'total_records' => $transactions->count(),
                'source_database' => config('database.connections.mysql.database'),
                'version' => '1.0'
            ],
            'data' => $transactions->map(function($transaction) {
                return (array) $transaction;
            })->toArray()
        ];
        
        // Sauvegarder en JSON
        $jsonData = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = 'mobile_money_export_' . date('Y-m-d_His') . '.json';
        
        Storage::disk('local')->put('exports/' . $filename, $jsonData);
        
        echo "✅ Export réussi: " . $filename . "\n";
        echo "📁 Emplacement: storage/app/exports/" . $filename . "\n";
        
        // Résumé
        $summary = DB::table('mobile_money_transactions')
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN type_operation = "depot" THEN montant ELSE 0 END) as total_depots'),
                DB::raw('SUM(CASE WHEN type_operation = "retrait" THEN montant ELSE 0 END) as total_retraits')
            )->first();
            
        echo "\n📈 RÉSUMÉ:\n";
        echo "──────────\n";
        echo "Total: " . $summary->total . " transactions\n";
        echo "Dépôts: " . number_format($summary->total_depots, 0, ',', ' ') . " F\n";
        echo "Retraits: " . number_format($summary->total_retraits, 0, ',', ' ') . " F\n";
        echo "Solde net: " . number_format($summary->total_retraits - $summary->total_depots, 0, ',', ' ') . " F\n";
        
        return $filename;
        
    } catch (Exception $e) {
        echo "❌ Erreur: " . $e->getMessage() . "\n";
        return false;
    }
}

// Exécution
echo "========================================\n";
echo "   EXPORT MOBILE MONEY - LOCAL\n";
echo "========================================\n";

$filename = exportMobileMoneyData();

if ($filename) {
    echo "\n🎉 Export terminé avec succès!\n";
    echo "📋 Prochaines étapes:\n";
    echo "   1. Transférer le fichier vers la production\n";
    echo "   2. Exécuter le script d'import en production\n";
} else {
    echo "\n💥 Échec de l'export\n";
}