<?php
// scripts/export/verify_mobile_money_data.php

$laravelRoot = dirname(__DIR__, 2);
require_once $laravelRoot . '/vendor/autoload.php';

$app = require_once $laravelRoot . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

function verifyMobileMoneyData() {
    echo "🔍 Vérification des données Mobile Money...\n\n";
    
    // 1. Total des enregistrements
    $total = DB::table('mobile_money_transactions')->count();
    echo "📊 Total transactions: " . $total . "\n";
    
    // 2. Doublons d'ID transaction
    $duplicates = DB::table('mobile_money_transactions')
        ->select('id_transaction', DB::raw('COUNT(*) as count'))
        ->groupBy('id_transaction')
        ->having('count', '>', 1)
        ->get();
        
    echo "🔍 Doublons ID transaction: " . $duplicates->count() . "\n";
    
    // 3. Données manquantes
    $invalidData = DB::table('mobile_money_transactions')
        ->whereNull('nom')
        ->orWhereNull('prenom')
        ->orWhereNull('telephone')
        ->orWhereNull('type_operation')
        ->orWhereNull('nature')
        ->orWhereNull('montant')
        ->orWhereNull('id_transaction')
        ->count();
        
    echo "❌ Données invalides: " . $invalidData . "\n";
    
    // 4. Montants problématiques
    $negativeAmounts = DB::table('mobile_money_transactions')
        ->where('montant', '<=', 0)
        ->count();
        
    echo "⚠️  Montants négatifs/nuls: " . $negativeAmounts . "\n";
    
    // 5. Statistiques
    $stats = DB::table('mobile_money_transactions')
        ->select(
            'type_operation',
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(montant) as total')
        )
        ->groupBy('type_operation')
        ->get();
        
    echo "\n📈 STATISTIQUES:\n";
    foreach ($stats as $stat) {
        echo "   " . ucfirst($stat->type_operation) . ": " . $stat->count . " trans, " . number_format($stat->total, 0, ',', ' ') . " F\n";
    }
    
    return $total > 0 && $duplicates->count() === 0 && $invalidData === 0;
}

echo "========================================\n";
echo "   VÉRIFICATION DONNÉES - LOCAL\n";
echo "========================================\n";

$isValid = verifyMobileMoneyData();

if ($isValid) {
    echo "\n✅ Données valides - Prêtes pour l'export!\n";
} else {
    echo "\n❌ Problèmes détectés - Corriger avant export.\n";
}