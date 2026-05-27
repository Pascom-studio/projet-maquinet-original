<?php

namespace App\Http\Controllers;

use App\Models\Mouvement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MouvementController extends Controller
{
    /**
     * Vérifier les permissions pour l'accès aux mouvements
     */
    private function checkPermissions()
    {
        if (!Auth::check() || (Auth::user()->fonction !== 'admin' && Auth::user()->fonction !== 'gerant' && Auth::user()->fonction !== 'super_admin')) {
            abort(403, 'Accès non autorisé. Seuls les Administrateurs et Gérants peuvent accéder aux mouvements.');
        }
    }

    /**
     * Récupérer les produits visibles pour l'utilisateur courant
     */
    private function getVisibleProducts()
    {
        $user = Auth::user();
        return Product::visibleTo($user)->get();
    }

    /**
     * Récupérer les mouvements visibles pour l'utilisateur courant
     */
    private function getVisibleMouvements()
    {
        $user = Auth::user();
        
        // Si l'utilisateur est super_admin, voir tous les mouvements
        if ($user->fonction === 'super_admin') {
            return Mouvement::with(['product', 'user']);
        }
        
        // Pour les admin et gérants, voir seulement les mouvements de leurs produits
        return Mouvement::with(['product', 'user'])
            ->whereHas('product', function($query) use ($user) {
                $query->visibleTo($user);
            });
    }

    public function index()
    {
        $this->checkPermissions();
        
        $mouvements = $this->getVisibleMouvements()
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('mouvements.index', compact('mouvements'));
    }

    public function create()
    {
        $this->checkPermissions();
        $products = $this->getVisibleProducts();
        
        // Vérification si des produits existent
        if ($products->isEmpty()) {
            return redirect()->route('products.create')
                ->with('warning', 'Vous devez créer au moins un produit avant de pouvoir effectuer un mouvement.');
        }
        
        return view('mouvements.create', compact('products'));
    }

    public function store(Request $request)
    {
        $this->checkPermissions();

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantite' => 'required|integer|min:1',
            'prix' => 'required|numeric|min:0',
            'type' => 'required|in:entrée,sortie',
            'raison' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Vérifier que le produit est accessible à l'utilisateur
            $product = Product::visibleTo(Auth::user())->find($request->product_id);
            
            if (!$product) {
                return back()
                    ->with('error', 'Produit non trouvé ou accès non autorisé.')
                    ->withInput();
            }

            // Vérifier le stock pour les sorties
            if ($request->type === 'sortie' && $product->quantite < $request->quantite) {
                return back()
                    ->with('error', 'Stock insuffisant! Stock disponible: ' . $product->quantite)
                    ->withInput();
            }

            // Créer le mouvement
            Mouvement::create([
                'product_id' => $request->product_id,
                'quantite' => $request->quantite,
                'prix' => $request->prix,
                'type' => $request->type,
                'user_id' => Auth::id(),
                'raison' => $request->raison,
            ]);

            // Mettre à jour le stock du produit
            if ($request->type === 'entrée') {
                $product->increment('quantite', $request->quantite);
            } else {
                $product->decrement('quantite', $request->quantite);
            }

            DB::commit();

            return redirect()->route('mouvements.index')
                ->with('success', 'Mouvement enregistré avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Erreur lors de l\'enregistrement: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $this->checkPermissions();
        
        $mouvement = $this->getVisibleMouvements()->findOrFail($id);
        
        return view('mouvements.show', compact('mouvement'));
    }

    public function edit($id)
    {
        $this->checkPermissions();
        
        $mouvement = $this->getVisibleMouvements()->findOrFail($id);
        $products = $this->getVisibleProducts();
        
        // Vérification si des produits existent
        if ($products->isEmpty()) {
            return redirect()->route('products.create')
                ->with('warning', 'Vous devez créer au moins un produit avant de pouvoir modifier un mouvement.');
        }
        
        return view('mouvements.edit', compact('mouvement', 'products'));
    }

    public function update(Request $request, $id)
    {
        $this->checkPermissions();

        $request->validate([
            'quantite' => 'required|integer|min:1',
            'prix' => 'required|numeric|min:0',
            'type' => 'required|in:entrée,sortie',
            'raison' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $mouvement = $this->getVisibleMouvements()->findOrFail($id);
            $product = $mouvement->product;

            // Vérifier que le produit est toujours accessible
            if (!Product::visibleTo(Auth::user())->where('id', $product->id)->exists()) {
                abort(403, 'Accès non autorisé à ce produit.');
            }

            // Annuler l'ancien mouvement sur le stock
            if ($mouvement->type === 'entrée') {
                $product->decrement('quantite', $mouvement->quantite);
            } else {
                $product->increment('quantite', $mouvement->quantite);
            }

            // Vérifier le stock pour les nouvelles sorties
            if ($request->type === 'sortie') {
                $stockApresAnnulation = $product->quantite;
                if ($stockApresAnnulation < $request->quantite) {
                    DB::rollBack();
                    
                    return back()
                        ->with('error', 'Stock insuffisant après annulation! Stock disponible: ' . $stockApresAnnulation)
                        ->withInput();
                }
            }

            // Appliquer le nouveau mouvement
            if ($request->type === 'entrée') {
                $product->increment('quantite', $request->quantite);
            } else {
                $product->decrement('quantite', $request->quantite);
            }

            // Mettre à jour le mouvement
            $mouvement->update([
                'quantite' => $request->quantite,
                'prix' => $request->prix,
                'type' => $request->type,
                'raison' => $request->raison, 
            ]);

            DB::commit();

            return redirect()->route('mouvements.index')
                ->with('success', 'Mouvement modifié avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Erreur lors de la modification: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $this->checkPermissions();

        try {
            DB::beginTransaction();

            $mouvement = $this->getVisibleMouvements()->findOrFail($id);
            $product = $mouvement->product;

            // Vérifier que le produit est toujours accessible
            if (!Product::visibleTo(Auth::user())->where('id', $product->id)->exists()) {
                abort(403, 'Accès non autorisé à ce produit.');
            }

            // Annuler le mouvement sur le stock
            if ($mouvement->type === 'entrée') {
                $product->decrement('quantite', $mouvement->quantite);
            } else {
                $product->increment('quantite', $mouvement->quantite);
            }

            $mouvement->delete();

            DB::commit();

            return redirect()->route('mouvements.index')
                ->with('success', 'Mouvement supprimé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }
}