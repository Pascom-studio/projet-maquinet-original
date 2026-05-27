<?php

namespace App\Http\Controllers;

use App\Models\MobileMoneyTransaction;
use App\Models\MobileMoneyCommission;
use App\Models\MobileMoneyStock;
use App\Models\MobileMoneyLiquidity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use App\Services\RealOCRService;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MobileMoneyTransactionsExport;
use App\Exports\MobileMoneyCommissionsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class MobileMoneyController extends Controller
{
    /**
     * Afficher l'historique des transactions
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Vérification d'accès
        if (!$user->isCaissier() && !$user->isMobileCaissier()) {
            abort(403, 'Accès réservé aux caissiers.');
        }
        
        $query = MobileMoneyTransaction::with('user')
            ->where(function($q) use ($user) {
                if ($user->isCaissier() || $user->isMobileCaissier()) {
                    $q->where('user_id', $user->id);
                } elseif ($user->isAdmin() || $user->isGerant()) {
                    $q->where('admin_id', $user->isAdmin() ? $user->id : $user->admin_id);
                }
            });

        // Filtres
        if ($request->filled('type_operation')) {
            $query->where('type_operation', $request->type_operation);
        }

        if ($request->filled('nature')) {
            $query->where('nature', $request->nature);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        // Utiliser le cache pour les statistiques (optimisation de la nouvelle version)
        $statistiques = $this->getStatistiquesAvecCache($user);

        return view('transactions.index', compact('transactions', 'statistiques'));
    }

    /**
     * Afficher l'historique des transactions
     */
    public function historique(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isMobileCaissier()) {
            abort(403, 'Accès non autorisé.');
        }

        $query = MobileMoneyTransaction::where('user_id', $user->id)
            ->with(['user'])
            ->orderBy('created_at', 'desc');

        // Appliquer les filtres
        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        if ($request->filled('nature')) {
            $query->where('nature', $request->nature);
        }

        if ($request->filled('type_operation')) {
            $query->where('type_operation', $request->type_operation);
        }

        $transactions = $query->paginate(20);

        return view('transactions.historique', compact('transactions'));
    }

    /**
     * Afficher l'historique des commissions PAR MOIS ET PAR OPERATEUR POUR L'UTILISATEUR CONNECTÉ
     */
    public function historiqueCommission(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isMobileCaissier()) {
            abort(403, 'Accès non autorisé.');
        }

        // Récupérer les commissions groupées par mois et opérateur POUR L'UTILISATEUR CONNECTÉ
        $commissionsQuery = MobileMoneyTransaction::where('user_id', $user->id)
            ->where('commission', '>', 0)
            ->select([
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as periode"),
                'nature as operateur',
                'type_operation',
                DB::raw('COUNT(*) as nombre_transactions'),
                DB::raw('SUM(commission) as commission_totale'),
                DB::raw('SUM(montant) as montant_total')
            ])
            ->groupBy('periode', 'operateur', 'type_operation')
            ->orderBy('periode', 'desc')
            ->orderBy('operateur', 'asc');

        // Appliquer les filtres
        if ($request->filled('mois')) {
            $commissionsQuery->whereMonth('created_at', $request->mois);
        }
        
        if ($request->filled('annee')) {
            $commissionsQuery->whereYear('created_at', $request->annee);
        }
        
        if ($request->filled('operateur')) {
            $commissionsQuery->where('nature', $request->operateur);
        }
        
        if ($request->filled('type_operation')) {
            $commissionsQuery->where('type_operation', $request->type_operation);
        }
        
        $commissionsMensuelles = $commissionsQuery->paginate(20);

        // Calculer les statistiques globales UNIQUEMENT pour le mois en cours
        $queryGlobal = MobileMoneyTransaction::where('user_id', $user->id)
            ->where('commission', '>', 0);
        
        // Commission totale du mois en cours uniquement
        $commissionMois = $queryGlobal->whereMonth('created_at', now()->month)
                                     ->whereYear('created_at', now()->year)
                                     ->sum('commission');
                                     
        $globalTotalCommissions = $commissionMois;
        
        $commissionAujourdhui = $queryGlobal->whereDate('created_at', today())->sum('commission');

        // Statistiques des commissions par opérateur POUR L'UTILISATEUR CONNECTÉ
        $commissionStats = $this->getCommissionStatsForUserOptimise($user);

        return view('transactions.historique-commission', compact(
            'commissionsMensuelles',
            'globalTotalCommissions',
            'commissionAujourdhui',
            'commissionMois',
            'commissionStats'
        ));
    }

    /**
     * Exporter les commissions avec le nouveau format (groupé par mois/opérateur) POUR L'UTILISATEUR CONNECTÉ
     */
    public function exportCommission(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isMobileCaissier()) {
            abort(403, 'Accès non autorisé.');
        }

        // Utiliser les paramètres GET
        $mois = $request->get('mois');
        $annee = $request->get('annee');
        $operateur = $request->get('operateur');
        $type_operation = $request->get('type_operation');
        $format = $request->get('format', 'excel');

        try {
            // Récupérer les commissions groupées POUR L'UTILISATEUR CONNECTÉ
            $commissionsQuery = MobileMoneyTransaction::where('user_id', $user->id)
                ->where('commission', '>', 0)
                ->select([
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m') as periode"),
                    'nature as operateur',
                    'type_operation',
                    DB::raw('COUNT(*) as nombre_transactions'),
                    DB::raw('SUM(commission) as commission_totale'),
                    DB::raw('SUM(montant) as montant_total')
                ])
                ->groupBy('periode', 'operateur', 'type_operation')
                ->orderBy('periode', 'desc')
                ->orderBy('operateur', 'asc');

            // Appliquer les filtres
            if (!empty($mois)) {
                $commissionsQuery->whereMonth('created_at', $mois);
            }

            if (!empty($annee)) {
                $commissionsQuery->whereYear('created_at', $annee);
            }

            if (!empty($operateur)) {
                $commissionsQuery->where('nature', $operateur);
            }

            if (!empty($type_operation)) {
                $commissionsQuery->where('type_operation', $type_operation);
            }

            $commissions = $commissionsQuery->get();

            $statistiques = $this->getCommissionExportStatisticsGrouped($user, $mois, $annee, $operateur, $type_operation);
            $filtres = [
                'mois' => $mois ? \DateTime::createFromFormat('!m', $mois)->format('F') : 'Tous',
                'annee' => $annee ?? 'Toutes',
                'operateur' => $operateur ? $this->getOperateurNom($operateur) : 'Tous',
                'type_operation' => $type_operation ? $this->getTypeOperationLabel($type_operation) : 'Tous'
            ];

            switch ($format) {
                case 'csv':
                    return $this->exportCommissionGroupedToCsv($commissions, $statistiques, $filtres);
                
                case 'excel':
                    return $this->exportCommissionGroupedToExcel($commissions, $statistiques, $filtres);
                
                case 'pdf':
                    return $this->exportCommissionGroupedToPdf($commissions, $statistiques, $filtres);
                
                default:
                    return back()->with('error', 'Format d\'export non supporté.');
            }

        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'export des commissions groupées: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'export: ' . $e->getMessage());
        }
    }

    /**
     * Obtenir les statistiques pour l'export des commissions groupées POUR L'UTILISATEUR CONNECTÉ
     */
    private function getCommissionExportStatisticsGrouped($user, $mois, $annee, $operateur, $type_operation)
    {
        $query = MobileMoneyTransaction::where('user_id', $user->id)
            ->where('commission', '>', 0);

        if (!empty($mois)) {
            $query->whereMonth('created_at', $mois);
        }

        if (!empty($annee)) {
            $query->whereYear('created_at', $annee);
        }

        if (!empty($operateur)) {
            $query->where('nature', $operateur);
        }

        if (!empty($type_operation)) {
            $query->where('type_operation', $type_operation);
        }

        $total_commissions = $query->sum('commission');
        $total_transactions = $query->count();
        $commission_moyenne = $total_transactions > 0 ? $total_commissions / $total_transactions : 0;

        // Par opérateur POUR L'UTILISATEUR CONNECTÉ - OPTIMISÉ
        $commissions_par_operateur = $this->getCommissionsParOperateurOptimise($query);

        $periode = '';
        if ($mois && $annee) {
            $periode = \DateTime::createFromFormat('!m', $mois)->format('F') . ' ' . $annee;
        } elseif ($annee) {
            $periode = 'Année ' . $annee;
        } else {
            $periode = 'Toutes périodes';
        }

        return [
            'total_commissions' => $total_commissions,
            'total_transactions' => $total_transactions,
            'commission_moyenne' => $commission_moyenne,
            'commissions_par_operateur' => $commissions_par_operateur,
            'periode' => $periode
        ];
    }

    /**
     * Méthode optimisée pour les commissions par opérateur
     */
    private function getCommissionsParOperateurOptimise($query)
    {
        $operateurs = ['orange_money', 'telecel_money', 'moov_money', 'coris_money'];
        $commissions_par_operateur = [];
        $total_commissions = $query->sum('commission');

        foreach ($operateurs as $op) {
            $queryOperateur = (clone $query)->where('nature', $op);
            $commission_operateur = $queryOperateur->sum('commission');
            $transactions_operateur = $queryOperateur->count();
            
            $commissions_par_operateur[$op] = [
                'commission' => $commission_operateur,
                'transactions' => $transactions_operateur,
                'pourcentage' => $total_commissions > 0 ? ($commission_operateur / $total_commissions) * 100 : 0
            ];
        }

        return $commissions_par_operateur;
    }

    /**
     * Exporter les commissions groupées en CSV POUR L'UTILISATEUR CONNECTÉ
     */
    private function exportCommissionGroupedToCsv($commissions, $statistiques, $filtres)
    {
        $fileName = 'commissions_groupées_mobile_money_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function() use ($commissions, $statistiques, $filtres) {
            $file = fopen('php://output', 'w');
            
            // En-tête
            fputcsv($file, [
                'Période', 'Opérateur', 'Type Opération', 'Nombre Transactions', 
                'Montant Total (FCFA)', 'Commission Totale (FCFA)', 'Commission Moyenne (FCFA)'
            ], ';');

            // Données groupées
            foreach ($commissions as $commission) {
                $commissionMoyenne = $commission->nombre_transactions > 0 
                    ? $commission->commission_totale / $commission->nombre_transactions 
                    : 0;
                
                fputcsv($file, [
                    \Carbon\Carbon::createFromFormat('Y-m', $commission->periode)->format('F Y'),
                    $this->getOperateurNom($commission->operateur),
                    $this->getTypeOperationLabel($commission->type_operation),
                    $commission->nombre_transactions,
                    number_format($commission->montant_total, 2, ',', ' '),
                    number_format($commission->commission_totale, 2, ',', ' '),
                    number_format($commissionMoyenne, 2, ',', ' ')
                ], ';');
            }

            // Résumé détaillé
            fputcsv($file, [], ';');
            fputcsv($file, ['RAPPORT DES COMMISSIONS - SYNTHÈSE GROUPÉE'], ';');
            fputcsv($file, ['Période:', $statistiques['periode']], ';');
            fputcsv($file, ['Filtre Mois:', $filtres['mois']], ';');
            fputcsv($file, ['Filtre Année:', $filtres['annee']], ';');
            fputcsv($file, ['Filtre Opérateur:', $filtres['operateur']], ';');
            fputcsv($file, ['Filtre Type Opération:', $filtres['type_operation']], ';');
            fputcsv($file, [], ';');
            
            fputcsv($file, ['STATISTIQUES GÉNÉRALES'], ';');
            fputcsv($file, ['Total Transactions:', $statistiques['total_transactions']], ';');
            fputcsv($file, ['Total Commissions:', number_format($statistiques['total_commissions'], 2, ',', ' ') . ' FCFA'], ';');
            fputcsv($file, ['Commission Moyenne:', number_format($statistiques['commission_moyenne'], 2, ',', ' ') . ' FCFA'], ';');
            fputcsv($file, [], ';');
            
            fputcsv($file, ['RÉPARTITION PAR OPÉRATEUR'], ';');
            foreach ($statistiques['commissions_par_operateur'] as $operateur => $data) {
                if ($data['transactions'] > 0) {
                    fputcsv($file, [
                        $this->getOperateurNom($operateur) . ':',
                        number_format($data['commission'], 2, ',', ' ') . ' FCFA',
                        '(' . $data['transactions'] . ' transactions - ' . number_format($data['pourcentage'], 1, ',', ' ') . '%)'
                    ], ';');
                }
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Exporter les commissions groupées en Excel
     */
    private function exportCommissionGroupedToExcel($commissions, $statistiques, $filtres)
    {
        $fileName = 'commissions_groupées_mobile_money_' . date('Y-m-d_His') . '.xlsx';
        
        $data = [
            'commissions' => $commissions,
            'statistiques' => $statistiques,
            'filtres' => $filtres,
            'getTypeOperationLabel' => [$this, 'getTypeOperationLabel'],
            'getOperateurNom' => [$this, 'getOperateurNom']
        ];

        return Excel::download(new MobileMoneyCommissionsExport($data), $fileName);
    }

    /**
     * Exporter les commissions groupées en PDF
     */
    private function exportCommissionGroupedToPdf($commissions, $statistiques, $filtres)
    {
        $fileName = 'commissions_groupées_mobile_money_' . date('Y-m-d_His') . '.pdf';
        
        $commissions = $commissions ?? collect();
        
        $data = [
            'commissions' => $commissions,
            'statistiques' => $statistiques,
            'filtres' => $filtres,
            'getTypeOperationLabel' => [$this, 'getTypeOperationLabel'],
            'getOperateurNom' => [$this, 'getOperateurNom']
        ];

        $pdf = PDF::loadView('transactions.exports.commissions-grouped-pdf', $data)
              ->setPaper('a4', 'landscape')
              ->setOptions([
                  'defaultFont' => 'sans-serif',
                  'isHtml5ParserEnabled' => true,
                  'isRemoteEnabled' => true
              ]);

        return $pdf->download($fileName);
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $user = Auth::user();
        if (!$user->isCaissier() && !$user->isMobileCaissier()) {
            abort(403, 'Accès réservé aux caissiers.');
        }
        
        // Utiliser la version optimisée
        $commissionStats = $this->getCommissionStatsForUserOptimise($user);
        
        return view('transactions.create', compact('commissionStats'));
    }

    /**
     * Générer un ID de transaction unique
     */
    private function generateUniqueTransactionId()
    {
        $maxRetries = 5;
        $retryCount = 0;
        
        do {
            // Générer un ID unique avec timestamp et random
            $id = 'MM' . now()->format('YmdHis') . random_int(100000, 999999);
            
            // Vérifier s'il n'existe pas déjà
            $exists = MobileMoneyTransaction::where('id_transaction', $id)->exists();
            
            if (!$exists) {
                return $id;
            }
            
            $retryCount++;
            usleep(100000); // Attendre 100ms avant de réessayer
            
        } while ($retryCount < $maxRetries);
        
        // Si échec après plusieurs tentatives, utiliser UUID
        return 'MM' . Str::uuid()->toString();
    }

 /**
 * Enregistrer une nouvelle transaction - VERSION OPTIMISÉE (1.2s au lieu de 2s)
 */
public function store(Request $request)
{
    $user = Auth::user();
    if (!$user->isCaissier() && !$user->isMobileCaissier()) {
        abort(403, 'Accès réservé aux caissiers.');
    }
    
    // ✅ OPTIMISATION 1: Validation minimaliste
    $validated = $request->validate([
        'nom' => 'required|string|max:100', // Réduit de 255 à 100
        'prenom' => 'required|string|max:100',
        'cnib' => 'nullable|string|max:20',
        'telephone' => 'required|string|max:15',
        'type_operation' => 'required|in:depot,retrait',
        'nature' => 'required|in:orange_money,telecel_money,moov_money,coris_money',
        'montant' => 'required|numeric|min:0.01|max:10000000', // Ajout max
    ]);

    // ✅ OPTIMISATION 2: Génération ID plus rapide
    $uniqueTransactionId = 'MM' . now()->format('YmdHis') . random_int(1000, 9999);
    
    DB::beginTransaction();

    try {
        // ✅ OPTIMISATION 3: Calcul commission optimisé (évite méthodes séparées)
        $commissionBrute = $this->calculerCommissionOptimisee(
            $validated['montant'],
            $validated['nature'],
            $validated['type_operation']
        );
        
        // Taxes 15.3% - calcul direct
        $commissionNet = $commissionBrute * 0.847; // (100% - 15.3%) = 84.7%
        $taxes = $commissionBrute * 0.153;

        // ✅ OPTIMISATION 4: INSERT unique avec tous les champs
        $transactionData = [
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'cnib' => $validated['cnib'] ?? null,
            'telephone' => $validated['telephone'],
            'type_operation' => $validated['type_operation'],
            'nature' => $validated['nature'],
            'montant' => $validated['montant'],
            'id_transaction' => $uniqueTransactionId,
            'user_id' => $user->id,
            'admin_id' => $user->admin_id ?? $user->id,
            'statut' => 'actif',
            'commission' => round($commissionNet, 2),
            'commission_brute' => round($commissionBrute, 2),
            'taxes' => round($taxes, 2),
            'created_at' => now(),
            'updated_at' => now()
        ];

        // ✅ OPTIMISATION 5: Insert direct sans Eloquent overhead
        DB::table('mobile_money_transactions')->insert($transactionData);
        
        // ✅ OPTIMISATION 6: Commission mensuelle - upsert atomic
        $this->updateCommissionMensuelleOptimisee(
            $user->admin_id ?? $user->id,
            $validated['nature'],
            round($commissionNet, 2),
            round($commissionBrute, 2),
            round($taxes, 2)
        );

        // ✅ OPTIMISATION 7: Liquidité - update atomic
        $this->updateLiquiditeOptimisee($user->id, $validated['type_operation'], $validated['montant']);

        // ✅ OPTIMISATION 8: Cache asynchrone (via queue si possible, sinon après commit)
        DB::commit();
        
        // Nettoyage cache après succès (non bloquant)
        $this->clearStatsCacheAsync($user);

        return redirect()->route('mobile-money.index')
                         ->with('success', 'Transaction Mobile Money enregistrée! Commission: ' . 
                                number_format($commissionNet, 2) . ' FCFA');

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Transaction Mobile Money échouée: ' . $e->getMessage(), [
            'user_id' => $user->id,
            'montant' => $validated['montant'] ?? null
        ]);
        
        return back()->with('error', 'Erreur lors de l\'enregistrement.')->withInput();
    }
}

/**
 * ✅ Calcul commission ultra-optimisé (évite appels multiples)
 */
private function calculerCommissionOptimisee($montant, $operateur, $typeOperation)
{
    // Tableau de taux pré-calculés (incluant taxes pour Coris Money)
    static $taux = [
        'orange_money' => ['depot' => 0.003, 'retrait' => 0.004], // 0.3% et 0.4%
        'telecel_money' => ['depot' => 0.003, 'retrait' => 0.004],
        'moov_money' => ['depot' => 0.004, 'retrait' => 0.005],
        'coris_money' => [
            'depot' => 0.0034, // 0.4% * 0.85 (après 15% déduction)
            'retrait' => 0.00425 // 0.5% * 0.85
        ]
    ];

    return $montant * ($taux[$operateur][$typeOperation] ?? 0);
}

/**
 * ✅ Update commission mensuelle optimisée (single query)
 */
private function updateCommissionMensuelleOptimisee($adminId, $operateur, $commissionNet, $commissionBrute, $taxes)
{
    $moisEnCours = now()->format('Y-m');
    
    DB::table('mobile_money_commissions')->updateOrInsert(
        [
            'admin_id' => $adminId,
            'operateur' => $operateur,
            'mois' => $moisEnCours
        ],
        [
            'commission_total' => DB::raw("COALESCE(commission_total, 0) + $commissionBrute"),
            'commission_nette' => DB::raw("COALESCE(commission_nette, 0) + $commissionNet"),
            'taxes_total' => DB::raw("COALESCE(taxes_total, 0) + $taxes"),
            'nombre_transactions' => DB::raw("COALESCE(nombre_transactions, 0) + 1"),
            'updated_at' => now()
        ]
    );
}

/**
 * ✅ Update liquidité optimisée (atomic)
 */
private function updateLiquiditeOptimisee($userId, $typeOperation, $montant)
{
    $operator = $typeOperation === 'depot' ? '+' : '-';
    
    DB::table('mobile_money_liquidities')->updateOrInsert(
        ['user_id' => $userId],
        [
            'montant' => DB::raw("COALESCE(montant, 0) $operator $montant"),
            'updated_at' => now()
        ]
    );
}

/**
 * ✅ Nettoyage cache non-bloquant
 */
private function clearStatsCacheAsync($user)
{
    // Utiliser une queue si disponible, sinon faire après réponse
    $cacheKey = 'mobile_stats_' . $user->id . '_' . today()->format('Ymd');
    $commissionCacheKey = 'commission_stats_' . $user->id . '_' . now()->format('Ym');
    
    // Libérer après réponse (ne bloque pas l'utilisateur)
    register_shutdown_function(function() use ($cacheKey, $commissionCacheKey) {
        try {
            Cache::forget($cacheKey);
            Cache::forget($commissionCacheKey);
        } catch (\Exception $e) {
            // Silently fail - le cache sera régénéré au prochain accès
        }
    });
}


/**
 * OPTIMISATION: Vérification rapide d'accès
 */
private function checkAccessQuick($user)
{
    return $user->isCaissier() || $user->isMobileCaissier();
}

/**
 * OPTIMISATION: Validation rapide des données
 */
private function validateTransactionData(Request $request)
{
    return $request->validate([
        'nom' => 'required|string|max:100',
        'prenom' => 'required|string|max:100',
        'telephone' => 'required|string|max:15',
        'type_operation' => 'required|in:depot,retrait',
        'nature' => 'required|in:orange_money,telecel_money,moov_money,coris_money',
        'montant' => 'required|numeric|min:500|max:1000000', // Plage réaliste
    ], [
        'montant.min' => 'Le montant minimum est 500 FCFA',
        'montant.max' => 'Le montant maximum est 1,000,000 FCFA'
    ]);
}

/**
 * OPTIMISATION: Génération ID transaction ultra-rapide
 */
private function generateTransactionIdQuick()
{
    return 'MM' . time() . random_int(1000, 9999);
}

    /**
     * Mettre à jour la liquidité selon le type d'opération
     */
    private function updateLiquidite($user, $typeOperation, $montant)
    {
        $liquidity = MobileMoneyLiquidity::firstOrCreate(
            ['user_id' => $user->id],
            ['montant' => 0]
        );

        if ($typeOperation === 'depot') {
            // Pour un dépôt, la liquidité augmente
            $liquidity->increment('montant', $montant);
        } elseif ($typeOperation === 'retrait') {
            // Pour un retrait, la liquidité diminue
            $liquidity->decrement('montant', $montant);
        }

        $liquidity->save();
    }

    /**
     * Obtenir le solde de liquidité pour un utilisateur
     */
    private function getLiquidite($user)
    {
        $liquidity = MobileMoneyLiquidity::where('user_id', $user->id)->first();
        return $liquidity ? $liquidity->montant : 0;
    }

    /**
     * Calculer la commission selon les règles spécifiées (AVANT taxes)
     */
    private function calculerCommission($montant, $operateur, $typeOperation)
    {
        // Règles spéciales pour Coris Money
        if ($operateur === 'coris_money') {
            return $this->calculerCommissionCorisMoney($montant, $typeOperation);
        }

        // Règles standard pour les autres opérateurs
        $taux = $this->getTauxCommission($operateur, $typeOperation);
        return ($montant * $taux / 100);
    }

    /**
     * Calcul spécifique pour Coris Money selon la formule: 
     * Depot: (montant * 0.4%) - 15% du résultat
     * Retrait: (montant * 0.5%) - 15% du résultat
     */
    private function calculerCommissionCorisMoney($montant, $typeOperation)
    {
        if ($typeOperation === 'depot') {
            // Formule dépôt: (montant * 0.4%) - 15% du résultat
            $commissionBase = $montant * 0.004; // 0.4%
            $deduction = $commissionBase * 0.15; // 15% du résultat
            return $commissionBase - $deduction;
        } else {
            // Formule retrait: (montant * 0.5%) - 15% du résultat
            $commissionBase = $montant * 0.005; // 0.5%
            $deduction = $commissionBase * 0.15; // 15% du résultat
            return $commissionBase - $deduction;
        }
    }

    /**
     * Soustraire les taxes de 15,3% de la commission
     */
    private function soustraireTaxes($commissionBrute)
    {
        $tauxTaxes = 15.3;
        $montantTaxes = $commissionBrute * ($tauxTaxes / 100);
        return $commissionBrute - $montantTaxes;
    }

    /**
     * Obtenir les taux de commission
     */
    private function getTauxCommission($operateur, $typeOperation)
    {
        $taux = [
            'orange_money' => [
                'depot' => 0.3,
                'retrait' => 0.4
            ],
            'telecel_money' => [
                'depot' => 0.3,
                'retrait' => 0.4
            ],
            'moov_money' => [
                'depot' => 0.4,
                'retrait' => 0.5
            ],
            'coris_money' => [
                'depot' => 0.4, // Taux de base pour Coris Money
                'retrait' => 0.5 // Taux de base pour Coris Money
            ]
        ];

        return $taux[$operateur][$typeOperation] ?? 0;
    }

    /**
     * Mettre à jour les commissions mensuelles POUR L'UTILISATEUR
     */
    private function updateCommissionMensuelle($user, $operateur, $commissionNet, $commissionBrute)
    {
        $moisEnCours = now()->format('Y-m');
        $adminId = $user->admin_id ?? $user->id;

        $commissionMensuelle = MobileMoneyCommission::firstOrCreate(
            [
                'admin_id' => $adminId,
                'operateur' => $operateur,
                'mois' => $moisEnCours
            ],
            [
                'commission_total' => 0,
                'commission_nette' => 0,
                'taxes_total' => 0,
                'nombre_transactions' => 0
            ]
        );

        $taxes = $commissionBrute - $commissionNet;

        $commissionMensuelle->increment('commission_total', $commissionBrute);
        $commissionMensuelle->increment('commission_nette', $commissionNet);
        $commissionMensuelle->increment('taxes_total', $taxes);
        $commissionMensuelle->increment('nombre_transactions');
        $commissionMensuelle->save();
    }

    /**
     * Déduire les commissions lors de la suppression POUR L'UTILISATEUR
     */
    private function deduireCommissionMensuelle($adminId, $operateur, $mois, $commissionNet, $commissionBrute, $taxes)
    {
        $commissionMensuelle = MobileMoneyCommission::where([
            'admin_id' => $adminId,
            'operateur' => $operateur,
            'mois' => $mois
        ])->first();

        if ($commissionMensuelle) {
            $commissionMensuelle->decrement('commission_total', $commissionBrute);
            $commissionMensuelle->decrement('commission_nette', $commissionNet);
            $commissionMensuelle->decrement('taxes_total', $taxes);
            $commissionMensuelle->decrement('nombre_transactions');
            
            // Si le nombre de transactions arrive à 0, supprimer l'enregistrement
            if ($commissionMensuelle->nombre_transactions <= 0) {
                $commissionMensuelle->delete();
            } else {
                $commissionMensuelle->save();
            }
        }
    }

    /**
     * Afficher les statistiques de commission POUR L'UTILISATEUR CONNECTÉ
     */
    public function getCommissionStats()
    {
        $user = Auth::user();
        return $this->getCommissionStatsForUserOptimise($user);
    }

    /**
     * NOUVELLE MÉTHODE OPTIMISÉE : Obtenir les statistiques de commission pour un utilisateur
     */
    private function getCommissionStatsForUserOptimise($user)
    {
        $cacheKey = 'commission_stats_' . $user->id . '_' . now()->format('Ym');
        
        return Cache::remember($cacheKey, 300, function() use ($user) { // Cache 5 minutes
            $stats = [];
            $operateurs = ['orange_money', 'telecel_money', 'moov_money', 'coris_money'];

            // Une seule requête pour tous les opérateurs
            $commissionData = MobileMoneyTransaction::where('user_id', $user->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->select([
                    'nature',
                    DB::raw('SUM(commission) as commission_nette'),
                    DB::raw('SUM(commission_brute) as commission_brute'),
                    DB::raw('SUM(taxes) as taxes'),
                    DB::raw('COUNT(*) as transactions')
                ])
                ->groupBy('nature')
                ->get()
                ->keyBy('nature');

            foreach ($operateurs as $operateur) {
                $data = $commissionData[$operateur] ?? null;
                $stats[$operateur] = [
                    'commission' => $data->commission_nette ?? 0,
                    'commission_nette' => $data->commission_nette ?? 0,
                    'commission_brute' => $data->commission_brute ?? 0,
                    'taxes' => $data->taxes ?? 0,
                    'transactions' => $data->transactions ?? 0,
                    'operateur_nom' => $this->getOperateurNom($operateur)
                ];
            }

            uasort($stats, function($a, $b) {
                return $b['commission_nette'] <=> $a['commission_nette'];
            });

            return $stats;
        });
    }

    /**
     * MÉTHODES STATIQUES POUR LA VUE - CORRECTION DE L'ERREUR
     */

    /**
     * Méthode statique pour obtenir les statistiques de commission d'un utilisateur
     */
    public static function getCommissionStatsForUserStatic($user)
    {
        $operateurs = ['orange_money', 'telecel_money', 'moov_money', 'coris_money'];
        $stats = [];

        foreach ($operateurs as $operateur) {
            $commissionData = MobileMoneyTransaction::where('user_id', $user->id)
                ->where('nature', $operateur)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->select([
                    DB::raw('SUM(commission) as commission_nette'),
                    DB::raw('SUM(commission_brute) as commission_brute'),
                    DB::raw('SUM(taxes) as taxes'),
                    DB::raw('COUNT(*) as transactions')
                ])
                ->first();

            $stats[$operateur] = [
                'commission' => $commissionData->commission_nette ?? 0,
                'commission_nette' => $commissionData->commission_nette ?? 0,
                'commission_brute' => $commissionData->commission_brute ?? 0,
                'taxes' => $commissionData->taxes ?? 0,
                'transactions' => $commissionData->transactions ?? 0,
                'operateur_nom' => self::getOperateurNomStatic($operateur)
            ];
        }

        uasort($stats, function($a, $b) {
            return $b['commission_nette'] <=> $a['commission_nette'];
        });

        return $stats;
    }

    /**
     * Méthode statique pour obtenir la commission d'un opérateur spécifique
     */
    public static function getCommissionForOperateurStatic($userId, $operateur)
    {
        return MobileMoneyTransaction::where('user_id', $userId)
            ->where('nature', $operateur)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('commission');
    }

    /**
     * Méthode statique pour obtenir le nombre de transactions d'un opérateur
     */
    public static function getTransactionsCountForOperateurStatic($userId, $operateur)
    {
        return MobileMoneyTransaction::where('user_id', $userId)
            ->where('nature', $operateur)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    /**
     * Méthode statique pour obtenir le nom de l'opérateur
     */
    public static function getOperateurNomStatic($operateur)
    {
        $noms = [
            'orange_money' => 'Orange Money',
            'telecel_money' => 'Telecel Money',
            'moov_money' => 'Moov Money',
            'coris_money' => 'Coris Money',
            'liquidite' => 'Liquidité'
        ];

        return $noms[$operateur] ?? $operateur;
    }

    /**
     * Méthode statique pour obtenir le libellé du type d'opération
     */
    public static function getTypeOperationLabelStatic($type)
    {
        $labels = [
            'depot' => 'Dépôt',
            'retrait' => 'Retrait'
        ];
        
        return $labels[$type] ?? $type;
    }

    /**
     * Obtenir le nom de l'opérateur
     */
    public function getOperateurNom($operateur)
    {
        return self::getOperateurNomStatic($operateur);
    }

    /**
     * Remise à zéro des commissions
     */
    public function resetCommissions()
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isGerant() && !$user->isSuperAdmin()) {
            abort(403, 'Accès non autorisé.');
        }

        try {
            $moisPrecedent = now()->subMonth()->format('Y-m');
            $moisEnCours = now()->format('Y-m');
            
            \Log::info("🔁 Remise à zéro des commissions demandée par l'utilisateur: {$user->id}");

            return redirect()->route('mobile-money.historique-commissions')
                             ->with('success', 'Les commissions sont gérées automatiquement par mois. Aucune action nécessaire.');

        } catch (\Exception $e) {
            \Log::error('❌ Erreur lors de la remise à zéro des commissions: ' . $e->getMessage());
            return redirect()->route('mobile-money.historique-commissions')
                             ->with('error', 'Erreur lors de la remise à zéro: ' . $e->getMessage());
        }
    }

    /**
     * Historique des commissions (pour admin/gerant)
     */
    public function historiqueCommissions(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isGerant() && !$user->isSuperAdmin()) {
            abort(403, 'Accès non autorisé.');
        }

        $adminId = $user->isAdmin() ? $user->id : $user->admin_id;

        $query = MobileMoneyCommission::where('admin_id', $adminId)
            ->orderBy('mois', 'desc');

        if ($request->filled('operateur')) {
            $query->where('operateur', $request->operateur);
        }

        if ($request->filled('annee')) {
            $query->where('mois', 'like', $request->annee . '-%');
        }

        $commissions = $query->paginate(12);

        $totalCommissionNette = $commissions->sum('commission_nette');
        $totalCommissionBrute = $commissions->sum('commission_total');
        $totalTaxes = $commissions->sum('taxes_total');
        $totalTransactions = $commissions->sum('nombre_transactions');

        return view('transactions.historique-commissions', compact(
            'commissions', 
            'totalCommissionNette',
            'totalCommissionBrute',
            'totalTaxes',
            'totalTransactions'
        ));
    }

    /**
     * Afficher une transaction spécifique
     */
    public function show(MobileMoneyTransaction $mobileMoney)
    {
        $user = Auth::user();
        if (!$user->isCaissier() && !$user->isMobileCaissier()) {
            abort(403, 'Accès réservé aux caissiers.');
        }
        
        $this->checkAuthorization($mobileMoney);

        return view('transactions.show', compact('mobileMoney'));
    }

    /**
     * Supprimer définitivement une transaction et déduire les commissions
     */
    public function supprimer(MobileMoneyTransaction $mobileMoney)
    {
        $user = Auth::user();
        if (!$user->isCaissier() && !$user->isMobileCaissier()) {
            abort(403, 'Accès réservé aux caissiers.');
        }
        
        $this->checkAuthorization($mobileMoney);

        DB::beginTransaction();

        try {
            // Sauvegarder les données avant suppression pour la déduction des commissions
            $commissionNet = $mobileMoney->commission;
            $commissionBrute = $mobileMoney->commission_brute;
            $taxes = $mobileMoney->taxes;
            $operateur = $mobileMoney->nature;
            $transactionUser = $mobileMoney->user;
            $adminId = $transactionUser->admin_id ?? $transactionUser->id;
            $moisTransaction = $mobileMoney->created_at->format('Y-m');

            // Déduire les commissions des statistiques mensuelles
            $this->deduireCommissionMensuelle($adminId, $operateur, $moisTransaction, $commissionNet, $commissionBrute, $taxes);

            // Déduire la liquidité selon le type d'opération
            if ($mobileMoney->type_operation === 'depot') {
                $this->updateLiquidite($transactionUser, 'retrait', $mobileMoney->montant); // Inverse l'opération
            } elseif ($mobileMoney->type_operation === 'retrait') {
                $this->updateLiquidite($transactionUser, 'depot', $mobileMoney->montant); // Inverse l'opération
            }

            // Supprimer définitivement la transaction
            $mobileMoney->delete();

            // Nettoyer le cache des statistiques (optimisation nouvelle version)
            $this->clearStatsCache($user);

            DB::commit();

            return redirect()->route('mobile-money.index')
                             ->with('success', 'Transaction supprimée définitivement avec succès! Commission déduite: ' . number_format($commissionNet, 2) . ' FCFA');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(MobileMoneyTransaction $mobileMoney)
    {
        $user = Auth::user();
        if (!$user->isCaissier() && !$user->isMobileCaissier()) {
            abort(403, 'Accès réservé aux caissiers.');
        }
        
        $this->checkAuthorization($mobileMoney);

        return view('transactions.edit', compact('mobileMoney'));
    }

    /**
     * Mettre à jour une transaction
     */
    public function update(Request $request, MobileMoneyTransaction $mobileMoney)
    {
        $user = Auth::user();
        if (!$user->isCaissier() && !$user->isMobileCaissier()) {
            abort(403, 'Accès réservé aux caissiers.');
        }
        
        $this->checkAuthorization($mobileMoney);

        if (!$mobileMoney->peut_etre_modifie) {
            return back()->with('error', 'Cette transaction ne peut plus être modifiée (délai de 24h dépassé).');
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'cnib' => 'nullable|string|max:255',
            'telephone' => 'required|string|max:20',
            'type_operation' => 'required|in:depot,retrait',
            'nature' => 'required|in:orange_money,telecel_money,moov_money,coris_money',
            'montant' => 'required|numeric|min:0.01',
            'id_transaction' => 'required|string|max:255|unique:mobile_money_transactions,id_transaction,' . $mobileMoney->id
        ]);

        DB::beginTransaction();

        try {
            // Récupérer les anciennes valeurs pour la déduction
            $ancienneCommissionNet = $mobileMoney->commission;
            $ancienneCommissionBrute = $mobileMoney->commission_brute;
            $anciennesTaxes = $mobileMoney->taxes;
            $ancienOperateur = $mobileMoney->nature;
            $ancienTypeOperation = $mobileMoney->type_operation;
            $ancienMontant = $mobileMoney->montant;
            $transactionUser = $mobileMoney->user;
            $adminId = $transactionUser->admin_id ?? $transactionUser->id;
            $moisTransaction = $mobileMoney->created_at->format('Y-m');

            // Déduire les anciennes commissions
            $this->deduireCommissionMensuelle($adminId, $ancienOperateur, $moisTransaction, $ancienneCommissionNet, $ancienneCommissionBrute, $anciennesTaxes);

            // Déduire l'ancienne liquidité
            if ($ancienTypeOperation === 'depot') {
                $this->updateLiquidite($transactionUser, 'retrait', $ancienMontant);
            } elseif ($ancienTypeOperation === 'retrait') {
                $this->updateLiquidite($transactionUser, 'depot', $ancienMontant);
            }

            // Calculer les nouvelles commissions
            $nouvelleCommissionBrute = $this->calculerCommission(
                $validated['montant'],
                $validated['nature'],
                $validated['type_operation']
            );

            $nouvelleCommissionNet = $this->soustraireTaxes($nouvelleCommissionBrute);
            $nouvellesTaxes = $nouvelleCommissionBrute - $nouvelleCommissionNet;

            // Mettre à jour la transaction
            $mobileMoney->update(array_merge($validated, [
                'commission' => $nouvelleCommissionNet,
                'commission_brute' => $nouvelleCommissionBrute,
                'taxes' => $nouvellesTaxes
            ]));

            // Ajouter les nouvelles commissions
            $this->updateCommissionMensuelle($user, $validated['nature'], $nouvelleCommissionNet, $nouvelleCommissionBrute);

            // Ajouter la nouvelle liquidité
            $this->updateLiquidite($transactionUser, $validated['type_operation'], $validated['montant']);

            // Nettoyer le cache des statistiques (optimisation nouvelle version)
            $this->clearStatsCache($user);

            DB::commit();

            return redirect()->route('mobile-money.show', $mobileMoney)
                             ->with('success', 'Transaction mise à jour avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Scanner un document d'identité avec OCR - VERSION ORIGINALE FONCTIONNELLE
     */
    public function scanDocument(Request $request)
    {
        $imagePath = null;
        $fullPath = null;
        
        try {
            \Log::info('=== 🆕 SCAN AVEC REAL OCR SERVICE ===');

            $request->validate([
                'document_image' => 'required|image|mimes:jpeg,png,jpg|max:5120'
            ]);

            $image = $request->file('document_image');
            
            // RÉTABLISSEMENT DU CODE ORIGINAL
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                \File::makeDirectory($tempDir, 0755, true);
                \Log::info('📁 Dossier temp créé: ' . $tempDir);
            }
            
            $fileName = 'ocr_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $fullPath = $tempDir . '/' . $fileName;
            
            $image->move($tempDir, $fileName);
            
            \Log::info('📸 Image sauvegardée:', [
                'nom_original' => $image->getClientOriginalName(),
                'chemin_complet' => $fullPath,
                'taille' => filesize($fullPath),
                'existe' => file_exists($fullPath) ? 'OUI' : 'NON'
            ]);

            if (!file_exists($fullPath)) {
                throw new \Exception("Échec de la sauvegarde du fichier: " . $fullPath);
            }

            $ocrService = new RealOCRService();
            $extractedData = $ocrService->processImage($fullPath);
            
            \Log::info('🎯 VRAIES DONNÉES OCR EXTRAITES:', $extractedData);

            // NETTOYAGE ORIGINAL
            if (file_exists($fullPath)) {
                unlink($fullPath);
                \Log::info('🧹 Fichier temporaire supprimé: ' . $fullPath);
            }
            
            return response()->json([
                'success' => true,
                'data' => $extractedData,
                'service' => 'RealOCRService',
                'timestamp' => now()->toString()
            ]);

        } catch (\Exception $e) {
            \Log::error('💥 ERREUR SCAN COMPLÈTE: ' . $e->getMessage());
            
            // NETTOYAGE EN CAS D'ERREUR
            if ($fullPath && file_exists($fullPath)) {
                unlink($fullPath);
                \Log::info('🧹 Fichier nettoyé après erreur: ' . $fullPath);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du scan: ' . $e->getMessage(),
                'data' => [
                    'nom' => 'Erreur',
                    'prenom' => 'Scan', 
                    'cnib' => 'Non lu',
                    'service' => 'RealOCRService',
                    'error' => $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Afficher la page d'export des transactions
     */
    public function showExport(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isCaissier() && !$user->isMobileCaissier()) {
            abort(403, 'Accès réservé aux caissiers.');
        }

        $dates = [
            'debut' => now()->startOfMonth()->format('Y-m-d'),
            'fin' => now()->endOfMonth()->format('Y-m-d')
        ];

        $statistiques = $this->getExportStatistics($user, $dates['debut'], $dates['fin']);

        return view('transactions.export', compact('statistiques', 'dates'));
    }

    /**
     * Exporter les transactions - VERSION COMPLÈTEMENT CORRIGÉE (GET)
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isCaissier() && !$user->isMobileCaissier()) {
            abort(403, 'Accès réservé aux caissiers.');
        }

        // CORRECTION : Utiliser input() pour plus de fiabilité
        $date_debut = $request->input('date_debut', now()->startOfMonth()->format('Y-m-d'));
        $date_fin = $request->input('date_fin', now()->endOfMonth()->format('Y-m-d'));
        $type_operation = $request->input('type_operation');
        $nature = $request->input('nature');
        $format = $request->input('format', 'excel');

        \Log::info('📊 Export Mobile Money demandé:', [
            'user' => $user->id,
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'format' => $format,
            'type_operation' => $type_operation,
            'nature' => $nature
        ]);

        try {
            $query = MobileMoneyTransaction::with('user')
                ->where('statut', MobileMoneyTransaction::STATUT_ACTIF)
                ->whereBetween('created_at', [
                    $date_debut . ' 00:00:00',
                    $date_fin . ' 23:59:59'
                ])
                ->where(function($q) use ($user) {
                    if ($user->isCaissier() || $user->isMobileCaissier()) {
                        $q->where('user_id', $user->id);
                    } elseif ($user->isAdmin() || $user->isGerant()) {
                        $q->where('admin_id', $user->isAdmin() ? $user->id : $user->admin_id);
                    }
                });

            if (!empty($type_operation)) {
                $query->where('type_operation', $type_operation);
            }

            if (!empty($nature)) {
                $query->where('nature', $nature);
            }

            $transactions = $query->orderBy('created_at', 'desc')->get();

            \Log::info('📈 Transactions trouvées pour export:', ['count' => $transactions->count()]);

            $statistiques = $this->getExportStatistics($user, $date_debut, $date_fin);
            $filtres = [
                'type_operation' => $type_operation ? $this->getTypeOperationLabel($type_operation) : 'Tous',
                'nature' => $nature ? $this->getOperateurNom($nature) : 'Tous'
            ];

            \Log::info('📋 Préparation export format:', ['format' => $format]);

            switch ($format) {
                case 'csv':
                    return $this->exportToCsv($transactions, $statistiques, $filtres);
                
                case 'excel':
                    return $this->exportToExcel($transactions, $statistiques, $filtres);
                
                case 'pdf':
                    return $this->exportToPdf($transactions, $statistiques, $filtres);
                
                default:
                    \Log::error('❌ Format non supporté:', ['format' => $format]);
                    return back()->with('error', 'Format d\'export non supporté: ' . $format);
            }

        } catch (\Exception $e) {
            \Log::error('💥 Erreur lors de l\'export Mobile Money: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Erreur lors de l\'export: ' . $e->getMessage());
        }
    }

    /**
     * Obtenir les statistiques pour l'export
     */
    private function getExportStatistics($user, $dateDebut, $dateFin)
    {
        $query = MobileMoneyTransaction::where('statut', MobileMoneyTransaction::STATUT_ACTIF)
            ->whereBetween('created_at', [$dateDebut . ' 00:00:00', $dateFin . ' 23:59:59'])
            ->where(function($q) use ($user) {
                if ($user->isCaissier() || $user->isMobileCaissier()) {
                    $q->where('user_id', $user->id);
                } elseif ($user->isAdmin() || $user->isGerant()) {
                    $q->where('admin_id', $user->isAdmin() ? $user->id : $user->admin_id);
                }
            });

        // OPTIMISATION : Une seule requête pour toutes les statistiques
        $stats = $query->select([
            DB::raw('COUNT(*) as total_transactions'),
            DB::raw('SUM(CASE WHEN type_operation = "depot" THEN montant ELSE 0 END) as total_depots'),
            DB::raw('SUM(CASE WHEN type_operation = "retrait" THEN montant ELSE 0 END) as total_retraits'),
            DB::raw('SUM(commission) as total_commissions')
        ])->first();

        return [
            'total_transactions' => $stats->total_transactions ?? 0,
            'total_depots' => $stats->total_depots ?? 0,
            'total_retraits' => $stats->total_retraits ?? 0,
            'total_commissions' => $stats->total_commissions ?? 0,
            'solde_net' => ($stats->total_retraits ?? 0) - ($stats->total_depots ?? 0),
            'periode' => \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($dateFin)->format('d/m/Y')
        ];
    }

    /**
     * Exporter en CSV
     */
    private function exportToCsv($transactions, $statistiques, $filtres)
    {
        $fileName = 'transactions_mobile_money_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function() use ($transactions, $statistiques, $filtres) {
            $file = fopen('php://output', 'w');
            
            // En-tête
            fputcsv($file, [
                'ID Transaction', 'Date', 'Heure', 'Nom', 'Prénom', 'Téléphone',
                'Type Opération', 'Opérateur', 'Montant (FCFA)', 'Commission (FCFA)',
                'CNIB', 'Statut', 'Caissier'
            ], ';');

            // Données
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->id_transaction,
                    $transaction->created_at->format('d/m/Y'),
                    $transaction->created_at->format('H:i'),
                    $transaction->nom,
                    $transaction->prenom,
                    $transaction->telephone,
                    $this->getTypeOperationLabel($transaction->type_operation),
                    $this->getOperateurNom($transaction->nature),
                    number_format($transaction->montant, 2, ',', ' '),
                    number_format($transaction->commission, 2, ',', ' '),
                    $transaction->cnib ?? 'N/A',
                    $transaction->statut,
                    $transaction->user->name ?? 'N/A'
                ], ';');
            }

            // Résumé
            fputcsv($file, [], ';');
            fputcsv($file, ['RÉSUMÉ DE LA PÉRIODE'], ';');
            fputcsv($file, ['Période:', $statistiques['periode']], ';');
            fputcsv($file, ['Filtre Type:', $filtres['type_operation']], ';');
            fputcsv($file, ['Filtre Opérateur:', $filtres['nature']], ';');
            fputcsv($file, ['Total Transactions:', $statistiques['total_transactions']], ';');
            fputcsv($file, ['Total Dépôts:', number_format($statistiques['total_depots'], 2, ',', ' ') . ' FCFA'], ';');
            fputcsv($file, ['Total Retraits:', number_format($statistiques['total_retraits'], 2, ',', ' ') . ' FCFA'], ';');
            fputcsv($file, ['Total Commissions:', number_format($statistiques['total_commissions'], 2, ',', ' ') . ' FCFA'], ';');
            fputcsv($file, ['Solde Net:', number_format($statistiques['solde_net'], 2, ',', ' ') . ' FCFA'], ';');

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Exporter en Excel
     */
    private function exportToExcel($transactions, $statistiques, $filtres)
    {
        $fileName = 'transactions_mobile_money_' . date('Y-m-d_His') . '.xlsx';
        
        $data = [
            'transactions' => $transactions,
            'statistiques' => $statistiques,
            'filtres' => $filtres,
            'getTypeOperationLabel' => [$this, 'getTypeOperationLabel'],
            'getOperateurNom' => [$this, 'getOperateurNom']
        ];

        return Excel::download(new MobileMoneyTransactionsExport($data), $fileName);
    }

    /**
     * Exporter en PDF
     */
    private function exportToPdf($transactions, $statistiques, $filtres)
    {
        $fileName = 'transactions_mobile_money_' . date('Y-m-d_His') . '.pdf';
        
        $transactions = $transactions ?? collect();
        
        $data = [
            'transactions' => $transactions,
            'statistiques' => $statistiques,
            'filtres' => $filtres,
            'getTypeOperationLabel' => [$this, 'getTypeOperationLabel'],
            'getOperateurNom' => [$this, 'getOperateurNom']
        ];

        $pdf = PDF::loadView('transactions.exports.pdf', $data)
              ->setPaper('a4', 'landscape')
              ->setOptions([
                  'defaultFont' => 'sans-serif',
                  'isHtml5ParserEnabled' => true,
                  'isRemoteEnabled' => true
              ]);

        return $pdf->download($fileName);
    }

    /**
     * Obtenir le libellé du type d'opération
     */
    public function getTypeOperationLabel($type)
    {
        $labels = [
            'depot' => 'Dépôt',
            'retrait' => 'Retrait'
        ];
        
        return $labels[$type] ?? $type;
    }

    /**
     * Vérifier l'autorisation pour une transaction spécifique
     */
    private function checkAuthorization(MobileMoneyTransaction $mobileMoney)
    {
        $user = Auth::user();

        if (($user->isCaissier() || $user->isMobileCaissier()) && $mobileMoney->user_id !== $user->id) {
            abort(403, 'Accès non autorisé à cette transaction.');
        }

        if (($user->isAdmin() || $user->isGerant()) && $mobileMoney->admin_id !== ($user->isAdmin() ? $user->id : $user->admin_id)) {
            abort(403, 'Accès non autorisé à cette transaction.');
        }
    }

    /**
     * NOUVELLE MÉTHODE : Obtenir les statistiques avec cache
     */
    private function getStatistiquesAvecCache($user)
    {
        $cacheKey = 'mobile_stats_' . $user->id . '_' . today()->format('Ymd');
        
        return Cache::remember($cacheKey, 300, function() use ($user) { // 5 minutes
            return $this->getStatistiques($user);
        });
    }

    /**
     * NOUVELLE MÉTHODE : Nettoyer le cache des statistiques
     */
    private function clearStatsCache($user)
    {
        $cacheKey = 'mobile_stats_' . $user->id . '_' . today()->format('Ymd');
        Cache::forget($cacheKey);
        
        $commissionCacheKey = 'commission_stats_' . $user->id . '_' . now()->format('Ym');
        Cache::forget($commissionCacheKey);
    }

    /**
     * Obtenir les statistiques pour index.blade.php - SOLDE = (RETRAIT + APPRO) - REMBOURSEMENT
     */
    private function getStatistiques($user)
    {
        $query = MobileMoneyTransaction::where('statut', MobileMoneyTransaction::STATUT_ACTIF)
            ->where(function($q) use ($user) {
                if ($user->isCaissier() || $user->isMobileCaissier()) {
                    $q->where('user_id', $user->id);
                } elseif ($user->isAdmin() || $user->isGerant()) {
                    $q->where('admin_id', $user->isAdmin() ? $user->id : $user->admin_id);
                }
            });

        // OPTIMISATION : Une seule requête pour les totaux
        $totals = $query->select([
            DB::raw('COUNT(*) as total_count'),
            DB::raw('SUM(CASE WHEN type_operation = "depot" THEN montant ELSE 0 END) as total_depots'),
            DB::raw('SUM(CASE WHEN type_operation = "retrait" THEN montant ELSE 0 END) as total_retraits'),
            DB::raw('COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_count')
        ])->first();

        // OPTIMISATION : Soldes optimisés
        $soldes = $this->getSoldesOperateursOptimise($user);

        $solde_orange = $soldes['orange_money']['solde'] ?? 0;
        $solde_telecel = $soldes['telecel_money']['solde'] ?? 0;
        $solde_moov = $soldes['moov_money']['solde'] ?? 0;
        $solde_coris = $soldes['coris_money']['solde'] ?? 0;

        // Calculer le solde total des opérateurs
        $solde_operateurs = $solde_orange + $solde_telecel + $solde_moov + $solde_coris;

        // Récupérer la liquidité
        $liquidite = $this->getLiquidite($user);

        // Nouveau solde net = solde opérateurs + liquidité
        $solde_net = $solde_operateurs + $liquidite;

        return [
            'total_depots' => $totals->total_depots ?? 0,
            'total_retraits' => $totals->total_retraits ?? 0,
            'nombre_transactions' => $totals->total_count ?? 0,
            'transactions_ajourdhui' => $totals->today_count ?? 0,
            'solde_net' => $solde_net,
            'solde_orange_money' => $solde_orange,
            'solde_telecel_money' => $solde_telecel,
            'solde_moov_money' => $solde_moov,
            'solde_coris_money' => $solde_coris,
            'liquidite' => $liquidite,
            'solde_operateurs' => $solde_operateurs
        ];
    }

    /**
     * MÉTHODE COMPLÈTE POUR LA GESTION - Retourne toutes les données nécessaires
     */
    private function getSoldesOperateursOptimise($user)
    {
        return DB::transaction(function () use ($user) {
            $operateurs = ['orange_money', 'telecel_money', 'moov_money', 'coris_money'];
            
            // Récupérer toutes les données en une fois
            $stocksData = MobileMoneyStock::where('user_id', $user->id)
                ->where('statut', 'termine')
                ->select('operateur', 'type_mouvement', DB::raw('SUM(montant) as total'))
                ->groupBy('operateur', 'type_mouvement')
                ->get();
                
            $transactionsData = MobileMoneyTransaction::where('user_id', $user->id)
                ->where('statut', 'actif')
                ->select('nature', 'type_operation', DB::raw('SUM(montant) as total'))
                ->groupBy('nature', 'type_operation')
                ->get();
            
            $soldes = [];
            foreach ($operateurs as $operateur) {
                // Calculer les différentes valeurs
                $approvisionnements = $stocksData->where('operateur', $operateur)
                                               ->where('type_mouvement', 'approvisionnement')
                                               ->sum('total') ?? 0;
                                               
                $remboursements = $stocksData->where('operateur', $operateur)
                                           ->where('type_mouvement', 'remboursement')
                                           ->sum('total') ?? 0;
                
                // CORRECTION : Ajouter les dépenses
                $depenses = $stocksData->where('operateur', $operateur)
                                     ->where('type_mouvement', 'depense')
                                     ->sum('total') ?? 0;
                                       
                $depots = $transactionsData->where('nature', $operateur)
                                         ->where('type_operation', 'depot')
                                         ->sum('total') ?? 0;
                                         
                $retraits = $transactionsData->where('nature', $operateur)
                                           ->where('type_operation', 'retrait')
                                           ->sum('total') ?? 0;
                
                // NOUVELLE FORMULE: (RETRAIT + APPROVISIONNEMENT) - (REMBOURSEMENT + DEPENSES)
                $solde = ($retraits + $approvisionnements) - ($remboursements + $depenses +$depots);

                $soldes[$operateur] = [
                    'solde' => $solde,
                    'depots' => $depots,
                    'retraits' => $retraits,
                    'approvisionnements' => $approvisionnements,
                    'remboursements' => $remboursements,
                    'depenses' => $depenses, // Nouveau champ pour afficher les dépenses
                    'nom' => $this->getOperateurNom($operateur)
                ];
            }
            
            return $soldes;
        });
    }

    /**
     * Afficher la page de gestion du stock avec liquidité
     */
    public function gestion()
    {
        $user = Auth::user();
        
        if (!$user->isCaissier() && !$user->isMobileCaissier() && !$user->isAdmin() && !$user->isGerant()) {
            abort(403, 'Accès non autorisé.');
        }

        // Récupérer les soldes actuels par opérateur POUR L'UTILISATEUR CONNECTÉ
        $soldes = $this->getSoldesOperateursOptimise($user);

        // Récupérer la liquidité
        $liquidite = $this->getLiquidite($user);

        // Calculer le solde total (opérateurs + liquidité)
        $solde_total = collect($soldes)->sum('solde') + $liquidite;

        // Récupérer l'historique des mouvements de stock
        $mouvements = MobileMoneyStock::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('transactions.gestion', compact('soldes', 'mouvements', 'liquidite', 'solde_total'));
    }

    /**
     * Traiter un approvisionnement
     */
    public function approvisionner(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isCaissier() && !$user->isMobileCaissier() && !$user->isAdmin() && !$user->isGerant()) {
            abort(403, 'Accès non autorisé.');
        }

        $validated = $request->validate([
            'operateur' => 'required|in:orange_money,telecel_money,moov_money,coris_money',
            'montant' => 'required|numeric|min:0.01',
            'reference' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();

        try {
            // Créer le mouvement d'approvisionnement
            $mouvement = MobileMoneyStock::create([
                'user_id' => $user->id,
                'admin_id' => $user->admin_id ?? $user->id,
                'operateur' => $validated['operateur'],
                'type_mouvement' => 'approvisionnement',
                'montant' => $validated['montant'],
                'reference' => $validated['reference'],
                'notes' => $validated['notes'],
                'statut' => 'termine'
            ]);

            // Nettoyer le cache des statistiques
            $this->clearStatsCache($user);

            DB::commit();

            return redirect()->route('mobile-money.gestion')
                             ->with('success', 'Approvisionnement de ' . number_format($validated['montant'], 2) . ' FCFA effectué avec succès pour ' . $this->getOperateurNom($validated['operateur']));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'approvisionnement: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Traiter un remboursement
     */
    public function rembourser(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isCaissier() && !$user->isMobileCaissier() && !$user->isAdmin() && !$user->isGerant()) {
            abort(403, 'Accès non autorisé.');
        }

        $validated = $request->validate([
            'operateur' => 'required|in:orange_money,telecel_money,moov_money,coris_money,liquidite',
            'montant' => 'required|numeric|min:0.01',
            'reference' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();

        try {
            // Vérifier que le solde est suffisant
            if ($validated['operateur'] === 'liquidite') {
                // Pour la liquidité, vérifier le solde de liquidité
                $soldeLiquidite = $this->getLiquidite($user);
                if ($soldeLiquidite < $validated['montant']) {
                    return back()->with('error', 'Solde de liquidité insuffisant. Solde disponible: ' . number_format($soldeLiquidite, 2) . ' FCFA')->withInput();
                }
            } else {
                // Pour les opérateurs, vérifier le solde de l'opérateur
                $soldes = $this->getSoldesOperateursOptimise($user);
                $soldeOperateur = $soldes[$validated['operateur']]['solde'] ?? 0;

                if ($soldeOperateur < $validated['montant']) {
                    return back()->with('error', 'Solde insuffisant pour ' . $this->getOperateurNom($validated['operateur']) . '. Solde disponible: ' . number_format($soldeOperateur, 2) . ' FCFA')->withInput();
                }
            }

            // Créer le mouvement de remboursement
            $mouvement = MobileMoneyStock::create([
                'user_id' => $user->id,
                'admin_id' => $user->admin_id ?? $user->id,
                'operateur' => $validated['operateur'],
                'type_mouvement' => 'remboursement',
                'montant' => $validated['montant'],
                'reference' => $validated['reference'],
                'notes' => $validated['notes'],
                'statut' => 'termine'
            ]);

            // Mettre à jour la liquidité si c'est un remboursement de liquidité
            if ($validated['operateur'] === 'liquidite') {
                $liquidity = MobileMoneyLiquidity::firstOrCreate(
                    ['user_id' => $user->id],
                    ['montant' => 0]
                );
                $liquidity->decrement('montant', $validated['montant']);
                $liquidity->save();
            }

            // Nettoyer le cache des statistiques
            $this->clearStatsCache($user);

            DB::commit();

            return redirect()->route('mobile-money.gestion')
                             ->with('success', 'Remboursement de ' . number_format($validated['montant'], 2) . ' FCFA effectué avec succès pour ' . $this->getOperateurNom($validated['operateur']));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors du remboursement: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Ajouter un avoir (augmente la liquidité)
     */
    public function ajouterAvoir(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isCaissier() && !$user->isMobileCaissier() && !$user->isAdmin() && !$user->isGerant()) {
            abort(403, 'Accès non autorisé.');
        }

        $validated = $request->validate([
            'montant' => 'required|numeric|min:0.01',
            'reference' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();

        try {
            // Créer le mouvement d'avoir
            $mouvement = MobileMoneyStock::create([
                'user_id' => $user->id,
                'admin_id' => $user->admin_id ?? $user->id,
                'operateur' => 'liquidite',
                'type_mouvement' => 'avoir',
                'montant' => $validated['montant'],
                'reference' => $validated['reference'],
                'notes' => $validated['notes'],
                'statut' => 'termine'
            ]);

            // Augmenter la liquidité
            $liquidity = MobileMoneyLiquidity::firstOrCreate(
                ['user_id' => $user->id],
                ['montant' => 0]
            );
            $liquidity->increment('montant', $validated['montant']);
            $liquidity->save();

            // Nettoyer le cache des statistiques
            $this->clearStatsCache($user);

            DB::commit();

            return redirect()->route('mobile-money.gestion')
                             ->with('success', 'Avoir de ' . number_format($validated['montant'], 2) . ' FCFA ajouté avec succès! Liquidité augmentée.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'ajout de l\'avoir: ' . $e->getMessage())->withInput();
        }
    }

/**
 * Rechercher les informations client par téléphone
 */
public function searchClient(Request $request)
{
    $user = Auth::user();
    
    if (!$user->isCaissier() && !$user->isMobileCaissier()) {
        return response()->json(['error' => 'Accès non autorisé'], 403);
    }

    $request->validate([
        'telephone' => 'required|string'
    ]);

    // Nettoyer le numéro de téléphone (supprimer espaces et caractères spéciaux)
    $telephone = preg_replace('/[^0-9]/', '', $request->telephone);
    
    // Chercher la dernière transaction avec ce numéro
    $lastTransaction = MobileMoneyTransaction::where('user_id', $user->id)
        ->whereRaw("REPLACE(REPLACE(REPLACE(telephone, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$telephone}%"])
        ->orderBy('created_at', 'desc')
        ->first();

    if ($lastTransaction) {
        return response()->json([
            'success' => true,
            'client' => [
                'nom' => $lastTransaction->nom,
                'prenom' => $lastTransaction->prenom,
                'cnib' => $lastTransaction->cnib,
                'telephone' => $lastTransaction->telephone,
                'derniere_transaction' => $lastTransaction->created_at->format('d/m/Y H:i'),
                'dernier_operateur' => $this->getOperateurNom($lastTransaction->nature)
            ],
            'statistics' => [
                'total_transactions' => MobileMoneyTransaction::where('user_id', $user->id)
                    ->whereRaw("REPLACE(REPLACE(REPLACE(telephone, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$telephone}%"])
                    ->count(),
                'montant_moyen' => MobileMoneyTransaction::where('user_id', $user->id)
                    ->whereRaw("REPLACE(REPLACE(REPLACE(telephone, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$telephone}%"])
                    ->avg('montant'),
                'total_commission' => MobileMoneyTransaction::where('user_id', $user->id)
                    ->whereRaw("REPLACE(REPLACE(REPLACE(telephone, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$telephone}%"])
                    ->sum('commission')
            ]
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'Aucune transaction trouvée pour ce numéro'
    ]);
}

/**
 * Rechercher toutes les transactions d'un client
 */
public function getClientTransactions(Request $request)
{
    $user = Auth::user();
    
    if (!$user->isCaissier() && !$user->isMobileCaissier()) {
        return response()->json(['error' => 'Accès non autorisé'], 403);
    }

    $request->validate([
        'telephone' => 'required|string'
    ]);

    $telephone = preg_replace('/[^0-9]/', '', $request->telephone);
    
    $transactions = MobileMoneyTransaction::where('user_id', $user->id)
        ->whereRaw("REPLACE(REPLACE(REPLACE(telephone, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$telephone}%"])
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get()
        ->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'date' => $transaction->created_at->format('d/m/Y H:i'),
                'operateur' => $this->getOperateurNom($transaction->nature),
                'type' => $this->getTypeOperationLabel($transaction->type_operation),
                'montant' => number_format($transaction->montant, 0, ',', ' ') . ' FCFA',
                'commission' => number_format($transaction->commission, 0, ',', ' ') . ' FCFA',
                'id_transaction' => $transaction->id_transaction
            ];
        });

    return response()->json([
        'success' => true,
        'transactions' => $transactions
    ]);
}

    /**
     * Effectuer une dépense (réduit le solde d'un opérateur ou de la liquidité)
     */
    public function effectuerDepense(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isCaissier() && !$user->isMobileCaissier() && !$user->isAdmin() && !$user->isGerant()) {
            abort(403, 'Accès non autorisé.');
        }

        $validated = $request->validate([
            'operateur' => 'required|in:orange_money,telecel_money,moov_money,coris_money,liquidite',
            'montant' => 'required|numeric|min:0.01',
            'reference' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();

        try {
            // Vérifier que le solde est suffisant
            if ($validated['operateur'] === 'liquidite') {
                // Pour la liquidité, vérifier le solde de liquidité
                $soldeLiquidite = $this->getLiquidite($user);
                if ($soldeLiquidite < $validated['montant']) {
                    return back()->with('error', 'Solde de liquidité insuffisant. Solde disponible: ' . number_format($soldeLiquidite, 2) . ' FCFA')->withInput();
                }
                
                // Réduire la liquidité
                $liquidity = MobileMoneyLiquidity::firstOrCreate(
                    ['user_id' => $user->id],
                    ['montant' => 0]
                );
                $liquidity->decrement('montant', $validated['montant']);
                $liquidity->save();
            } else {
                // Pour les opérateurs, vérifier le solde de l'opérateur
                $soldes = $this->getSoldesOperateursOptimise($user);
                $soldeOperateur = $soldes[$validated['operateur']]['solde'] ?? 0;

                if ($soldeOperateur < $validated['montant']) {
                    return back()->with('error', 'Solde insuffisant pour ' . $this->getOperateurNom($validated['operateur']) . '. Solde disponible: ' . number_format($soldeOperateur, 2) . ' FCFA')->withInput();
                }
            }

            // Créer le mouvement de dépense
            $mouvement = MobileMoneyStock::create([
                'user_id' => $user->id,
                'admin_id' => $user->admin_id ?? $user->id,
                'operateur' => $validated['operateur'],
                'type_mouvement' => 'depense',
                'montant' => $validated['montant'],
                'reference' => $validated['reference'],
                'notes' => $validated['notes'],
                'statut' => 'termine'
            ]);

            // Nettoyer le cache des statistiques
            $this->clearStatsCache($user);

            DB::commit();

            return redirect()->route('mobile-money.gestion')
                             ->with('success', 'Dépense de ' . number_format($validated['montant'], 2) . ' FCFA effectuée avec succès sur ' . $this->getOperateurNom($validated['operateur']));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la dépense: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Afficher l'historique complet des mouvements
     */
    public function historiqueMouvements(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isCaissier() && !$user->isMobileCaissier() && !$user->isAdmin() && !$user->isGerant()) {
            abort(403, 'Accès non autorisé.');
        }

        $query = MobileMoneyStock::with(['user', 'admin'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Filtres
        if ($request->filled('type_mouvement')) {
            $query->where('type_mouvement', $request->type_mouvement);
        }

        if ($request->filled('operateur')) {
            $query->where('operateur', $request->operateur);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $mouvements = $query->paginate(20);

        // Statistiques
        $statistiques = $this->getStatistiquesMouvements($user, $request);

        return view('transactions.historique-mouvements', compact('mouvements', 'statistiques'));
    }

    /**
     * Obtenir les statistiques pour les mouvements
     */
    private function getStatistiquesMouvements($user, $request)
    {
        $query = MobileMoneyStock::where('user_id', $user->id)
            ->where('statut', 'termine');

        // Appliquer les mêmes filtres
        if ($request->filled('type_mouvement')) {
            $query->where('type_mouvement', $request->type_mouvement);
        }

        if ($request->filled('operateur')) {
            $query->where('operateur', $request->operateur);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $totalMouvements = $query->count();
        $totalMontant = $query->sum('montant');

        // Par type de mouvement
        $parType = $query->select('type_mouvement', DB::raw('COUNT(*) as count, SUM(montant) as total'))
            ->groupBy('type_mouvement')
            ->get()
            ->keyBy('type_mouvement');

        // Par opérateur
        $parOperateur = $query->select('operateur', DB::raw('COUNT(*) as count, SUM(montant) as total'))
            ->groupBy('operateur')
            ->get()
            ->keyBy('operateur');

        return [
            'total_mouvements' => $totalMouvements,
            'total_montant' => $totalMontant,
            'par_type' => $parType,
            'par_operateur' => $parOperateur,
            'periode' => $request->date_debut && $request->date_fin 
                ? \Carbon\Carbon::parse($request->date_debut)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($request->date_fin)->format('d/m/Y')
                : 'Toutes périodes'
        ];
    }

    /**
     * Obtenir le solde net d'un opérateur (pour API/Ajax)
     */
    public function getSoldeOperateur(Request $request)
    {
        $user = Auth::user();
        $operateur = $request->get('operateur');

        if (!$operateur) {
            return response()->json(['error' => 'Opérateur non spécifié'], 400);
        }

        if ($operateur === 'liquidite') {
            $solde = $this->getLiquidite($user);
        } else {
            $soldes = $this->getSoldesOperateursOptimise($user);
            $solde = $soldes[$operateur]['solde'] ?? 0;
        }

        return response()->json([
            'solde' => $solde,
            'solde_formate' => number_format($solde, 2) . ' FCFA',
            'operateur_nom' => $this->getOperateurNom($operateur)
        ]);
    }

    /**
     * Calculer le solde net par nature
     */
    private function getSoldeNetParNature($user, $nature)
    {
        $query = MobileMoneyTransaction::where('statut', MobileMoneyTransaction::STATUT_ACTIF)
            ->where('nature', $nature)
            ->where(function($q) use ($user) {
                if ($user->isCaissier() || $user->isMobileCaissier()) {
                    $q->where('user_id', $user->id);
                } elseif ($user->isAdmin() || $user->isGerant()) {
                    $q->where('admin_id', $user->isAdmin() ? $user->id : $user->admin_id);
                }
            });

        $retraits = (clone $query)->where('type_operation', 'retrait')->sum('montant');
        $depots = (clone $query)->where('type_operation', 'depot')->sum('montant');

        return $retraits - $depots;
    }
}