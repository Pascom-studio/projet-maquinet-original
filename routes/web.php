<?php

use App\Http\Controllers\CaisseController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\VenteController;
use App\Http\Controllers\MouvementController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\VenteMultipleController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ObservationController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\MobileMoneyController;
use App\Http\Controllers\CommercialController;
use App\Http\Controllers\GrandeCaisseMobileController;
use Illuminate\Support\Facades\Route;

// ==================== ROUTES PUBLIQUES ====================
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// ==================== MIDDLEWARE POUR LE THÈME ET AUTH ====================
Route::middleware([\App\Http\Middleware\ThemeMiddleware::class, 'auth'])->group(function () {
    
    // ==================== PAGE D'ACCUEIL ====================
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');

    // ==================== ROUTES ADMIN POUR LE THÈME ====================
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/theme', [ThemeController::class, 'edit'])->name('theme.edit');
        Route::post('/theme', [ThemeController::class, 'update'])->name('theme.update');
        Route::delete('/theme/reset', [ThemeController::class, 'reset'])->name('theme.reset');
        Route::get('/theme/super-admin', [ThemeController::class, 'editSuperAdmin'])->name('theme.super-admin');
    });
    
    // ==================== ROUTES VENTES ====================
    Route::prefix('ventes')->name('ventes.')->group(function () {
        // Routes principales
        Route::get('/', [VenteController::class, 'index'])->name('index');
        Route::get('/create', [VenteController::class, 'create'])->name('create');
        Route::post('/', [VenteController::class, 'store'])->name('store');
        Route::get('/history/overview', [VenteController::class, 'historique'])->name('historique');
        
        // Export des ventes
        Route::get('/export', [VenteController::class, 'showExportForm'])->name('export.form');
        Route::post('/export/download', [VenteController::class, 'downloadVentes'])->name('export');
        
        // Routes avec paramètres
        Route::prefix('{id}')->group(function () {
            Route::get('/', [VenteController::class, 'show'])->name('show');
            Route::get('/edit', [VenteController::class, 'edit'])->name('edit');
            Route::put('/', [VenteController::class, 'update'])->name('update');
            Route::delete('/', [VenteController::class, 'destroy'])->name('destroy');
            Route::get('/receipt', [VenteController::class, 'printReceipt'])->name('receipt');
        });
    });

    // ==================== ROUTES CAISSE ====================
    Route::prefix('caisse')->name('caisse.')->group(function () {
        Route::get('/', [CaisseController::class, 'index'])->name('index');
        Route::post('/ouvrir', [CaisseController::class, 'ouvrir'])->name('ouvrir');
        Route::post('/fermer', [CaisseController::class, 'fermer'])->name('fermer');
        Route::post('/retrait', [CaisseController::class, 'retrait'])->name('retrait');
        Route::post('/approvisionnement', [CaisseController::class, 'approvisionnement'])->name('approvisionnement');
        Route::post('/depense', [CaisseController::class, 'depense'])->name('depense');
    });
    
    // ==================== ROUTES CATÉGORIES ====================
    Route::resource('categories', CategorieController::class);
    
    // ==================== ROUTES PRODUCTS ====================
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/search', [ProductController::class, 'search'])->name('search');
        
        // Route de rafraîchissement AJAX
        Route::get('/refresh', [ProductController::class, 'refreshProducts'])->name('refresh');
        
        Route::prefix('{product}')->group(function () {
            Route::get('/', [ProductController::class, 'show'])->name('show');
            Route::get('/edit', [ProductController::class, 'edit'])->name('edit');
            Route::put('/', [ProductController::class, 'update'])->name('update');
            Route::delete('/', [ProductController::class, 'destroy'])->name('destroy');
        });
    });
    
    // ==================== ROUTES MOUVEMENTS ====================
    Route::resource('mouvements', MouvementController::class);

    // ==================== ROUTES USERS ====================
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        
        // ROUTES POUR LA RECHERCHE ET LES PAIEMENTS
        Route::get('/search', [UserController::class, 'search'])->name('search');
        
        // ROUTES POUR LES PAIEMENTS (AJOUTÉES)
        Route::get('/dashboard-paiements', [UserController::class, 'getAllPaiementsForDashboard'])
            ->name('dashboard-paiements');
        Route::get('/{user}/paiements-annee', [UserController::class, 'getAllPaiementsForUser'])
            ->name('paiements-annee');
        Route::post('/toggle-paiement', [UserController::class, 'togglePaiement'])
            ->name('toggle-paiement');
        Route::get('/{user}/paiement/{annee}/{mois}', [UserController::class, 'getStatutPaiement'])
            ->name('paiement-statut');
        
        // Routes pour l'affectation
        Route::post('/affecter-commercial', [UserController::class, 'affecterCommercial'])->name('affecter-commercial');
        Route::post('/affecter-grande-caisse', [UserController::class, 'affecterGrandeCaisse'])->name('affecter-grande-caisse');
        Route::post('/{user}/update-affectation', [UserController::class, 'updateAffectation'])->name('update-affectation');
        Route::delete('/{user}/retirer-affectation-commercial', [UserController::class, 'retirerAffectationCommercial'])->name('retirer-affectation');
        Route::delete('/{user}/retirer-affectation-grande-caisse', [UserController::class, 'retirerAffectationGrandeCaisse'])->name('retirer-affectation-grande-caisse');
        
        // Routes pour les paiements
        Route::post('/{user}/marquer-paiement', [UserController::class, 'marquerPaiement'])->name('marquer-paiement');
        Route::post('/{user}/annuler-paiement', [UserController::class, 'annulerPaiement'])->name('annuler-paiement');
        
        Route::prefix('{user}')->group(function () {
            Route::get('/', [UserController::class, 'show'])->name('show');
            Route::get('/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/', [UserController::class, 'update'])->name('update');
            Route::delete('/', [UserController::class, 'destroy'])->name('destroy');
            
            // Activation/désactivation
            Route::post('/toggle-activation', [UserController::class, 'toggleActivation'])->name('toggle-activation');
        });
    });
    
    // ==================== ROUTES AUDIT ====================
    Route::prefix('audit')->name('audit.')->group(function () {
        Route::get('/', [AuditController::class, 'index'])->name('index');
        Route::get('/financier', [AuditController::class, 'financier'])->name('financier');
        Route::get('/performance-hotesses', [AuditController::class, 'performanceHotesses'])->name('performance-hotesses');
        
        // Routes pour la caisse
        Route::prefix('caisse')->name('caisse.')->group(function () {
            Route::post('/fermer', [AuditController::class, 'fermerCaisse'])->name('fermer');
            Route::post('/ouvrir', [AuditController::class, 'ouvrirCaisse'])->name('ouvrir');
            Route::get('/solde-actuel', [AuditController::class, 'updateSoldeActuel'])->name('solde-actuel');
            Route::get('/solde-net', [AuditController::class, 'getSoldeNet'])->name('solde-net');
        });
        
        Route::get('/{id}', [AuditController::class, 'show'])->name('show');
    });

    // ==================== ROUTES MOBILE MONEY  ====================
    Route::prefix('mobile-money')->name('mobile-money.')->group(function () {
        // Routes principales
        Route::get('/', [MobileMoneyController::class, 'index'])->name('index');
        Route::get('/create', [MobileMoneyController::class, 'create'])->name('create');
        Route::post('/', [MobileMoneyController::class, 'store'])->name('store');
        
        // Routes pour la gestion des fonds Mobile Money
        Route::get('/gestion', [MobileMoneyController::class, 'gestion'])->name('gestion');
        Route::post('/approvisionner', [MobileMoneyController::class, 'approvisionner'])->name('approvisionner');
        Route::post('/rembourser', [MobileMoneyController::class, 'rembourser'])->name('rembourser');
        Route::post('/ajouter-avoir', [MobileMoneyController::class, 'ajouterAvoir'])->name('ajouter-avoir');
        Route::post('/effectuer-depense', [MobileMoneyController::class, 'effectuerDepense'])->name('effectuer-depense');
        Route::get('/historique-mouvements', [MobileMoneyController::class, 'historiqueMouvements'])->name('historique-mouvements');
        
        // Historique et commissions
        Route::get('/historique', [MobileMoneyController::class, 'historique'])->name('historique');
        Route::get('/historique-commission', [MobileMoneyController::class, 'historiqueCommission'])->name('historique-commission');
        
        // Routes d'export
        Route::get('/export-commission', [MobileMoneyController::class, 'exportCommission'])->name('export-commission');
        Route::get('/export', [MobileMoneyController::class, 'export'])->name('export');
        
        // Routes d'export et scan
        Route::post('/scan', [MobileMoneyController::class, 'scanDocument'])->name('scan');
        
        // NOUVELLES ROUTES POUR LA RECHERCHE CLIENT
        Route::post('/search-client', [MobileMoneyController::class, 'searchClient'])->name('search-client');
        Route::get('/client-transactions', [MobileMoneyController::class, 'getClientTransactions'])->name('client-transactions');
        
        // Routes pour les commissions
        Route::get('/historique-commissions', [MobileMoneyController::class, 'historiqueCommissions'])->name('historique-commissions');
        Route::post('/reset-commissions', [MobileMoneyController::class, 'resetCommissions'])->name('reset-commissions');
        
        // Routes avec paramètres
        Route::prefix('{mobileMoney}')->group(function () {
            Route::get('/', [MobileMoneyController::class, 'show'])->name('show');
            Route::get('/edit', [MobileMoneyController::class, 'edit'])->name('edit');
            Route::put('/', [MobileMoneyController::class, 'update'])->name('update');
            
            // Suppression
            Route::delete('/supprimer', [MobileMoneyController::class, 'supprimer'])->name('supprimer');
        });
    });

    // ==================== ROUTES TABLES ====================
    Route::prefix('tables')->name('tables.')->group(function () {
        Route::get('/', [TableController::class, 'index'])->name('index');
        Route::get('/create', [TableController::class, 'create'])->name('create');
        Route::post('/', [TableController::class, 'store'])->name('store');
        Route::post('/affecter', [TableController::class, 'affecter'])->name('affecter');
        Route::post('/affecter-rapide', [TableController::class, 'affecterRapide'])->name('affecter-rapide');
        
        Route::prefix('{table}')->group(function () {
            Route::get('/', [TableController::class, 'show'])->name('show');
            Route::get('/edit', [TableController::class, 'edit'])->name('edit');
            Route::put('/', [TableController::class, 'update'])->name('update');
            Route::delete('/', [TableController::class, 'destroy'])->name('destroy');
            Route::post('/liberer', [TableController::class, 'liberer'])->name('liberer');
        });
    });
    
    // ==================== ROUTES COMMANDES ====================
    Route::prefix('commandes')->name('commandes.')->group(function () {
        Route::get('/', [CommandeController::class, 'index'])->name('index');
        Route::get('/create', [CommandeController::class, 'create'])->name('create');
        Route::post('/', [CommandeController::class, 'store'])->name('store');
        Route::get('/soldees', [CommandeController::class, 'commandesSoldees'])->name('soldees');
        Route::get('/export', [CommandeController::class, 'showExportForm'])->name('export');
        Route::get('/download-ventes', [CommandeController::class, 'downloadVentes'])->name('download-ventes');
        
        Route::prefix('{commande}')->group(function () {
            Route::get('/', [CommandeController::class, 'show'])->name('show');
            Route::get('/edit', [CommandeController::class, 'edit'])->name('edit');
            Route::put('/', [CommandeController::class, 'update'])->name('update');
            Route::delete('/', [CommandeController::class, 'destroy'])->name('destroy');
            
            // Routes spécifiques aux commandes
            Route::get('/solder', [CommandeController::class, 'solderForm'])->name('solder.form');
            Route::post('/solder', [CommandeController::class, 'solder'])->name('solder');
            Route::get('/historique-actions', [CommandeController::class, 'historiqueActions'])->name('historique-actions');
            Route::post('/ajouter-produit', [CommandeController::class, 'ajouterProduit'])->name('ajouter-produit');
            Route::delete('/produit/{produitId}', [CommandeController::class, 'supprimerProduit'])->name('supprimer-produit');
            Route::get('/receipt', [CommandeController::class, 'showReceipt'])->name('receipt');
        });
    });
    
    // ==================== ROUTES COMMERCIAUX ====================
    Route::middleware(['auth'])->prefix('commercial')->name('commercial.')->group(function () {
        Route::get('/dashboard', [CommercialController::class, 'dashboard'])->name('dashboard');
        Route::get('/search', [CommercialController::class, 'searchMobileCaissier'])->name('search');
    });
    
    // ==================== ROUTES GRANDE CAISSE MOBILE ====================
    Route::middleware(['auth'])->prefix('grande-caisse')->name('grande-caisse.')->group(function () {
        Route::get('/dashboard', [GrandeCaisseMobileController::class, 'dashboard'])->name('dashboard');
        Route::get('/compte/{mobileCaissierId}', [GrandeCaisseMobileController::class, 'showCompteDetails'])->name('compte-details');
        
        // Routes en lecture seule pour les historiques
        Route::get('/historique-transactions/{mobileCaissierId}', [GrandeCaisseMobileController::class, 'showHistoriqueTransactions'])->name('historique-transactions');
        Route::get('/historique-commissions/{mobileCaissierId}', [GrandeCaisseMobileController::class, 'showHistoriqueCommissions'])->name('historique-commissions');
        Route::get('/gestion-stock/{mobileCaissierId}', [GrandeCaisseMobileController::class, 'showGestionStock'])->name('gestion-stock');
    });
    
    // ==================== ROUTES OBSERVATIONS ====================
    Route::prefix('observations')->name('observations.')->group(function () {
        Route::get('/', [ObservationController::class, 'index'])->name('index');
        Route::get('/mes-observations', [ObservationController::class, 'mesObservations'])->name('mes-observations');
        Route::get('/create/{hotesse}', [ObservationController::class, 'create'])->name('create');
        Route::post('/', [ObservationController::class, 'store'])->name('store');
        
        Route::prefix('{observation}')->group(function () {
            Route::get('/', [ObservationController::class, 'show'])->name('show');
            Route::delete('/', [ObservationController::class, 'destroy'])->name('destroy');
            Route::post('/marquer-lu', [ObservationController::class, 'marquerCommeLu'])->name('marquer-lu');
            
            // Routes pour les commentaires
            Route::prefix('commentaires')->name('commentaires.')->group(function () {
                Route::post('/', [ObservationController::class, 'ajouterCommentaire'])->name('store');
                Route::delete('/{commentaire}', [ObservationController::class, 'supprimerCommentaire'])->name('destroy');
            });
        });
    });
});

// ==================== ROUTES D'AUTHENTIFICATION ====================
require __DIR__.'/auth.php';