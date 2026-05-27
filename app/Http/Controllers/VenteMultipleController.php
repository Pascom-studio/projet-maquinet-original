<?php

namespace App\Http\Controllers;

use App\Models\VenteMultiple;
use App\Models\LigneVente;
use App\Models\Product;
use App\Models\Caisse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VenteMultipleController extends Controller
{
    public function create()
    {
        // Vérifier si la caisse est ouverte (pour les caissiers)
        if (Auth::user()->isCaissier()) {
            $caisse_ouverte = Caisse::where('user_id', Auth::id())
                                    ->where('statut', 'ouverte')
                                    ->exists();

            if (!$caisse_ouverte) {
                return redirect()->route('caisse.index')
                    ->with('error', 'Veuillez ouvrir votre caisse avant d\'effectuer des ventes.');
            }
        }

        $products = Product::with('categorie')->get();
        return view('vente-multiple.create', compact('products'));
    }

    public function store(Request $request)
    {
        // Vérifier si la caisse est ouverte (pour les caissiers)
        if (Auth::user()->isCaissier()) {
            $caisse = Caisse::where('user_id', Auth::id())
                            ->where('statut', 'ouverte')
                            ->first();

            if (!$caisse) {
                return redirect()->route('caisse.index')
                    ->with('error', 'Caisse fermée. Veuillez ouvrir votre caisse.');
            }
        }

        $request->validate([
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantite' => 'required|integer|min:1',
        ]);

        // Vérifier les stocks et calculer le montant total
        $montant_total = 0;
        $lignes = [];

        foreach ($request->products as $ligne) {
            $product = Product::findOrFail($ligne['product_id']);

            // Vérifier le stock
            if ($product->quantite < $ligne['quantite']) {
                return back()->with('error', "Stock insuffisant pour {$product->designation}! Stock disponible: {$product->quantite}");
            }

            $prix_unitaire = $product->prix;
            $montant_ligne = $prix_unitaire * $ligne['quantite'];
            $montant_total += $montant_ligne;

            $lignes[] = [
                'product_id' => $ligne['product_id'],
                'quantite' => $ligne['quantite'],
                'prix_unitaire' => $prix_unitaire,
                'montant_ligne' => $montant_ligne,
                'product' => $product
            ];
        }

        // Créer la vente multiple
        $venteMultiple = VenteMultiple::create([
            'numero_vente' => VenteMultiple::genererNumeroVente(),
            'user_id' => Auth::id(),
            'caisse_id' => isset($caisse) ? $caisse->id : null,
            'montant_total' => $montant_total,
        ]);

        // Créer les lignes de vente et mettre à jour les stocks
        foreach ($lignes as $ligne) {
            LigneVente::create([
                'vente_multiple_id' => $venteMultiple->id,
                'product_id' => $ligne['product_id'],
                'quantite' => $ligne['quantite'],
                'prix_unitaire' => $ligne['prix_unitaire'],
                'montant_ligne' => $ligne['montant_ligne'],
            ]);

            // Mettre à jour le stock
            $ligne['product']->decrement('quantite', $ligne['quantite']);
        }

        // Mettre à jour le total des ventes de la caisse
        if (isset($caisse)) {
            $caisse->increment('total_ventes', $montant_total);
        }

        return redirect()->route('ventes-multiples.show', $venteMultiple->id)
            ->with('success', 'Vente enregistrée avec succès! Numéro: ' . $venteMultiple->numero_vente);
    }

    public function show($id)
    {
        $vente = VenteMultiple::with(['lignes.product', 'user'])->findOrFail($id);
        return view('vente-multiple.show', compact('vente'));
    }

    public function index(Request $request)
    {
        $query = VenteMultiple::with(['user', 'lignes']);
        
        // Filtrage par date
        if ($request->has('date_debut') && $request->date_debut) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }
        
        if ($request->has('date_fin') && $request->date_fin) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }
        
        // Filtrage par utilisateur (pour les caissiers)
        if (Auth::user()->isCaissier()) {
            $query->where('user_id', Auth::id());
        }
        
        // Filtrage par utilisateur spécifique (pour gérant/admin)
        if ($request->has('user_id') && $request->user_id && (Auth::user()->isGerant() || Auth::user()->isAdmin())) {
            $query->where('user_id', $request->user_id);
        }

        $ventes = $query->orderBy('created_at', 'desc')->get();
        $users = Auth::user()->isCaissier() ? [] : \App\Models\User::all();
        
        return view('vente-multiple.index', compact('ventes', 'users'));
    }
}