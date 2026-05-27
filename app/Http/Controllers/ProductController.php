<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // SUPPRESSION DU CACHE pour avoir les données en temps réel
        $products = Product::visibleTo($user)
                          ->with(['categorie', 'user'])
                          ->orderBy('created_at', 'desc')
                          ->get();
        
        return view('products.index', compact('products'));
    }

    public function create()
    {
        $user = Auth::user();
        
        // Cache des catégories pour 1 heure (moins critique que les produits)
        $categories = Cache::remember('categories_visible_' . $user->id, 3600, function () use ($user) {
            return Categorie::visibleTo($user)->get();
        });
        
        // Vérification si des catégories existent
        if ($categories->isEmpty()) {
            return redirect()->route('categories.create')
                ->with('warning', 'Vous devez créer au moins une catégorie avant de pouvoir ajouter un produit.');
        }
        
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'designation' => 'required|string|max:255',
            'quantite' => 'required|integer|min:0',
            'prix' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'categorie_id' => 'required|exists:categories,id',
        ]);

        try {
            // Ajouter l'utilisateur connecté comme propriétaire
            $data = $request->all();
            $data['user_id'] = Auth::id();

            Product::create($data);

            // Nettoyer le cache des produits si existant
            Cache::forget('products_visible_' . Auth::id());

            return redirect()->route('products.index')
                ->with('success', 'Produit créé avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la création du produit: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Product $product)
    {
        if (!$product->canBeManagedBy(Auth::user())) {
            abort(403, 'Accès non autorisé à ce produit.');
        }

        $product->load(['categorie', 'user']);
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $user = Auth::user();
        
        if (!$product->canBeManagedBy($user)) {
            abort(403, 'Accès non autorisé à ce produit.');
        }

        // Cache des catégories pour 1 heure
        $categories = Cache::remember('categories_visible_' . $user->id, 3600, function () use ($user) {
            return Categorie::visibleTo($user)->get();
        });
        
        // Vérification si des catégories existent
        if ($categories->isEmpty()) {
            return redirect()->route('categories.create')
                ->with('warning', 'Vous devez créer au moins une catégorie avant de pouvoir modifier un produit.');
        }
        
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $user = Auth::user();
        
        if (!$product->canBeManagedBy($user)) {
            abort(403, 'Accès non autorisé à ce produit.');
        }

        $request->validate([
            'designation' => 'required|string|max:255',
            'quantite' => 'required|integer|min:0',
            'prix' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'categorie_id' => 'required|exists:categories,id',
        ]);

        try {
            $product->update($request->all());

            // Nettoyer le cache des produits si existant
            Cache::forget('products_visible_' . Auth::id());

            return redirect()->route('products.index')
                ->with('success', 'Produit mis à jour avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour du produit: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function search(Request $request)
    {
        $user = Auth::user();
        $query = $request->get('q');

        if (empty($query)) {
            return response()->json([]);
        }

        // Recherche sans cache pour avoir des résultats frais
        $products = Product::visibleTo($user)
            ->where('quantite', '>', 0)
            ->where(function($q) use ($query) {
                $q->where('designation', 'LIKE', "%{$query}%")
                  ->orWhere('code_barre', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'designation', 'prix', 'quantite']);

        return response()->json($products);
    }

    public function destroy(Product $product)
    {
        $user = Auth::user();
        
        if (!$product->canBeManagedBy($user)) {
            abort(403, 'Accès non autorisé à ce produit.');
        }

        try {
            // Vérifier si le produit a des ventes associées
            if ($product->ligneVentes()->exists()) {
                return redirect()->route('products.index')
                    ->with('error', 'Impossible de supprimer ce produit car il est associé à des ventes.');
            }

            $product->delete();

            // Nettoyer le cache des produits si existant
            Cache::forget('products_visible_' . Auth::id());

            return redirect()->route('products.index')
                ->with('success', 'Produit supprimé avec succès.');

        } catch (\Exception $e) {
            return redirect()->route('products.index')
                ->with('error', 'Erreur lors de la suppression du produit: ' . $e->getMessage());
        }
    }

    /**
     * Méthode pour rafraîchir les données des produits (AJAX)
     */
    public function refreshProducts()
    {
        $user = Auth::user();
        
        $products = Product::visibleTo($user)
                          ->with(['categorie', 'user'])
                          ->orderBy('created_at', 'desc')
                          ->get();

        return response()->json([
            'success' => true,
            'products' => $products->map(function($product) {
                return [
                    'id' => $product->id,
                    'designation' => $product->designation,
                    'quantite' => $product->quantite,
                    'prix' => $product->prix,
                    'categorie_nom' => $product->categorie->nom,
                    'stock_status' => $product->quantite == 0 ? 'out-of-stock' : ($product->quantite < 10 ? 'low-stock' : 'in-stock')
                ];
            })
        ]);
    }
}