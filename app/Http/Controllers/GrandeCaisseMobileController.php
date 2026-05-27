<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MobileMoneyTransaction;
use App\Models\MobileMoneyStock;
use App\Models\MobileMoneyLiquidity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GrandeCaisseMobileController extends Controller
{
    public function dashboard()
    {
        $grandeCaisse = Auth::user();
        
        if (!$grandeCaisse->isGrandeCaisseMobile()) {
            abort(403, 'Accès non autorisé.');
        }

        // Récupérer les comptes mobile caissiers regroupés
        $comptesMobileCaissiers = User::where('grande_caisse_id', $grandeCaisse->id)
                                     ->where('fonction', 'mobile_caissier')
                                     ->withCount(['mobileMoneyTransactions as transactions_count'])
                                     ->with(['admin'])
                                     ->orderBy('prenom')
                                     ->get();

        return view('grande-caisse.dashboard', compact('comptesMobileCaissiers'));
    }

    public function showCompteDetails($mobileCaissierId)
    {
        $grandeCaisse = Auth::user();
        
        if (!$grandeCaisse->isGrandeCaisseMobile()) {
            abort(403, 'Accès non autorisé.');
        }

        // Vérifier que le mobile caissier appartient bien à cette grande caisse
        $mobileCaissier = User::where('id', $mobileCaissierId)
                             ->where('grande_caisse_id', $grandeCaisse->id)
                             ->where('fonction', 'mobile_caissier')
                             ->first();

        if (!$mobileCaissier) {
            abort(404, 'Mobile caissier non trouvé ou non autorisé.');
        }

        // Statistiques en temps réel
        $transactionsAujourdhui = MobileMoneyTransaction::where('user_id', $mobileCaissierId)
            ->whereDate('created_at', today())
            ->count();

        $commissionsMois = MobileMoneyTransaction::where('user_id', $mobileCaissierId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('commission');

        $transactionsRecentes = MobileMoneyTransaction::where('user_id', $mobileCaissierId)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Calcul des soldes par opérateur en utilisant la même méthode que MobileMoneyController
        $soldesOperateurs = $this->getSoldesOperateursOptimise($mobileCaissier);

        // Historique des commissions (6 derniers mois)
        $historiqueCommissions = $this->getHistoriqueCommissions($mobileCaissierId);

        // Mouvements de stock récents
        $mouvementsStock = MobileMoneyStock::where('user_id', $mobileCaissierId)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Statistiques de commission
        $commissionStats = $this->getCommissionStatsForUserOptimise($mobileCaissier);

        // Liquidité
        $liquidite = $this->getLiquidite($mobileCaissier);

        return view('grande-caisse.compte-details', compact(
            'mobileCaissier',
            'transactionsAujourdhui',
            'commissionsMois',
            'transactionsRecentes',
            'soldesOperateurs',
            'historiqueCommissions',
            'mouvementsStock',
            'commissionStats',
            'liquidite'
        ));
    }

    /**
     * Obtenir les soldes par opérateur - MÊME MÉTHODE QUE MobileMoneyController
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
                $solde = ($retraits + $approvisionnements) - ($remboursements + $depenses+$depots);

                $soldes[$operateur] = [
                    'solde' => $solde,
                    'depots' => $depots,
                    'retraits' => $retraits,
                    'approvisionnements' => $approvisionnements,
                    'remboursements' => $remboursements,
                    'depenses' => $depenses,
                    'nom' => $this->getOperateurNom($operateur)
                ];
            }
            
            return $soldes;
        });
    }

    /**
     * Obtenir l'historique des commissions sur 6 mois
     */
    private function getHistoriqueCommissions($mobileCaissierId)
    {
        $historique = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $mois = $date->format('Y-m');
            
            $commission = MobileMoneyTransaction::where('user_id', $mobileCaissierId)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('commission');
                
            $historique[$mois] = $commission;
        }
        
        return $historique;
    }

    /**
     * Obtenir les statistiques de commission pour un utilisateur - MÊME MÉTHODE QUE MobileMoneyController
     */
    private function getCommissionStatsForUserOptimise($user)
    {
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
    }

    /**
     * Obtenir la liquidité - MÊME MÉTHODE QUE MobileMoneyController
     */
    private function getLiquidite($user)
    {
        $liquidity = MobileMoneyLiquidity::where('user_id', $user->id)->first();
        return $liquidity ? $liquidity->montant : 0;
    }

    /**
     * Obtenir le nom de l'opérateur - MÊME MÉTHODE QUE MobileMoneyController
     */
    private function getOperateurNom($operateur)
    {
        $noms = [
            'orange_money' => 'Orange Money',
            'telecel_money' => 'Telecel Money',
            'moov_money' => 'Moov Money',
            'coris_money' => 'Coris Money'
        ];

        return $noms[$operateur] ?? $operateur;
    }

    /**
     * Obtenir le libellé du type d'opération - MÊME MÉTHODE QUE MobileMoneyController
     */
    private function getTypeOperationLabel($type)
    {
        $labels = [
            'depot' => 'Dépôt',
            'retrait' => 'Retrait'
        ];
        
        return $labels[$type] ?? $type;
    }

    /**
     * Afficher l'historique des transactions d'un mobile caissier
     */
    public function showHistoriqueTransactions($mobileCaissierId, Request $request)
    {
        $grandeCaisse = Auth::user();
        
        if (!$grandeCaisse->isGrandeCaisseMobile()) {
            abort(403, 'Accès non autorisé.');
        }

        // Vérifier que le mobile caissier appartient bien à cette grande caisse
        $mobileCaissier = User::where('id', $mobileCaissierId)
                             ->where('grande_caisse_id', $grandeCaisse->id)
                             ->where('fonction', 'mobile_caissier')
                             ->firstOrFail();

        $query = MobileMoneyTransaction::where('user_id', $mobileCaissierId)
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

        return view('grande-caisse.historique-transactions', compact('transactions', 'mobileCaissier'));
    }

    /**
     * Afficher l'historique des commissions d'un mobile caissier
     */
    public function showHistoriqueCommissions($mobileCaissierId, Request $request)
    {
        $grandeCaisse = Auth::user();
        
        if (!$grandeCaisse->isGrandeCaisseMobile()) {
            abort(403, 'Accès non autorisé.');
        }

        // Vérifier que le mobile caissier appartient bien à cette grande caisse
        $mobileCaissier = User::where('id', $mobileCaissierId)
                             ->where('grande_caisse_id', $grandeCaisse->id)
                             ->where('fonction', 'mobile_caissier')
                             ->firstOrFail();

        // Récupérer les commissions groupées par mois et opérateur
        $commissionsQuery = MobileMoneyTransaction::where('user_id', $mobileCaissierId)
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

        // Calculer les statistiques globales
        $queryGlobal = MobileMoneyTransaction::where('user_id', $mobileCaissierId)
            ->where('commission', '>', 0);
        
        $commissionMois = $queryGlobal->whereMonth('created_at', now()->month)
                                     ->whereYear('created_at', now()->year)
                                     ->sum('commission');
                                     
        $globalTotalCommissions = $commissionMois;
        
        $commissionAujourdhui = $queryGlobal->whereDate('created_at', today())->sum('commission');

        // Statistiques des commissions par opérateur
        $commissionStats = $this->getCommissionStatsForUserOptimise($mobileCaissier);

        return view('grande-caisse.historique-commissions', compact(
            'commissionsMensuelles',
            'globalTotalCommissions',
            'commissionAujourdhui',
            'commissionMois',
            'commissionStats',
            'mobileCaissier'
        ));
    }

    /**
     * Afficher la gestion du stock d'un mobile caissier
     */
    public function showGestionStock($mobileCaissierId)
    {
        $grandeCaisse = Auth::user();
        
        if (!$grandeCaisse->isGrandeCaisseMobile()) {
            abort(403, 'Accès non autorisé.');
        }

        // Vérifier que le mobile caissier appartient bien à cette grande caisse
        $mobileCaissier = User::where('id', $mobileCaissierId)
                             ->where('grande_caisse_id', $grandeCaisse->id)
                             ->where('fonction', 'mobile_caissier')
                             ->firstOrFail();

        // Récupérer les soldes actuels par opérateur
        $soldes = $this->getSoldesOperateursOptimise($mobileCaissier);

        // Récupérer la liquidité
        $liquidite = $this->getLiquidite($mobileCaissier);

        // Calculer le solde total (opérateurs + liquidité)
        $solde_total = collect($soldes)->sum('solde') + $liquidite;

        // Récupérer l'historique des mouvements de stock
        $mouvements = MobileMoneyStock::where('user_id', $mobileCaissierId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('grande-caisse.gestion-stock', compact(
            'soldes', 
            'mouvements', 
            'liquidite', 
            'solde_total',
            'mobileCaissier'
        ));
    }
}