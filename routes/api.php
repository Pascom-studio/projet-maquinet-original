<?php

use App\Http\Controllers\ProductController;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route pour la recherche de produits par code (scan)
Route::get('/products/find-by-code/{code}', function($code) {
    $user = Auth::user();
    
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Non authentifié'
        ], 401);
    }
    
    $product = Product::visibleTo($user)
                     ->where(function($query) use ($code) {
                         $query->where('code_barre', $code)
                               ->orWhere('code_qr', $code)
                               ->orWhere('id', $code);
                     })
                     ->first();

    if ($product) {
        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'designation' => $product->designation,
                'prix' => $product->prix,
                'quantite' => $product->quantite,
                'code_barre' => $product->code_barre
            ]
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'Produit non trouvé'
    ], 404);
})->middleware('auth:sanctum');

// Route pour la recherche de produits par nom
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/products/search', [ProductController::class, 'search']);
});