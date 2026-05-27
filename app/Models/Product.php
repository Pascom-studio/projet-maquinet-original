<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Product extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'designation', 
        'quantite', 
        'prix', 
        'description', 
        'categorie_id',
        'user_id',
        'code_barre', // AJOUT: Code-barres pour le scan
        'code_qr'     // AJOUT: QR code pour le scan
    ];

    /**
     * Relation avec l'utilisateur (propriétaire du produit)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour les produits d'une hiérarchie admin spécifique
     * Inclut l'admin + tous ses sub-users (gérants et caissiers)
     */
    public function scopeForAdminHierarchy($query, $adminId)
    {
        return $query->where(function($q) use ($adminId) {
            $q->where('user_id', $adminId) // Produits de l'admin lui-même
              ->orWhereHas('user', function($subQuery) use ($adminId) {
                  // Produits de tous les sub-users de cet admin
                  $subQuery->where('admin_id', $adminId);
              });
        });
    }

    /**
     * Scope pour filtrer les produits visibles selon les permissions
     */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isAdmin()) {
            // Admin voit ses produits + ceux de toute sa hiérarchie
            return $query->forAdminHierarchy($user->id);
        }

        // Pour les gérants et caissiers, utiliser la même logique
        if ($user->isGerant() || $user->isCaissier()) {
            // Voir tous les produits de la hiérarchie de leur admin
            return $query->forAdminHierarchy($user->admin_id);
        }

        // Par défaut : seulement ses propres produits
        return $query->where('user_id', $user->id);
    }

    /**
     * Vérifier si l'utilisateur peut gérer ce produit
     * CORRECTION : Les gérants peuvent gérer les produits de leur admin
     */
    public function canBeManagedBy(User $user)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            // Admin peut gérer ses produits + ceux de sa hiérarchie
            return $this->user_id === $user->id || 
                   ($this->user && $this->user->admin_id === $user->id);
        }

        if ($user->isGerant()) {
            // CORRECTION : Gérant peut gérer les produits de son admin parent
            // Vérifier si le produit appartient à l'admin du gérant
            if ($this->user_id === $user->admin_id) {
                return true;
            }
            // Vérifier si le produit appartient à un sub-user du même admin
            if ($this->user && $this->user->admin_id === $user->admin_id) {
                return true;
            }
            return false;
        }

        // Caissier ne peut gérer aucun produit
        return false;
    }

    /**
     * Vérifier si l'utilisateur peut vendre ce produit
     */
    public function canBeSoldBy(User $user)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            // Admin peut vendre ses produits + ceux de sa hiérarchie
            return $this->user_id === $user->id || 
                   ($this->user && $this->user->admin_id === $user->id);
        }

        if ($user->isGerant() || $user->isCaissier()) {
            // Gérants et caissiers peuvent vendre tous les produits de la hiérarchie de leur admin
            // Vérifier si le produit appartient à l'admin du caissier/gérant
            if ($this->user_id === $user->admin_id) {
                return true;
            }
            // Vérifier si le produit appartient à un sub-user du même admin
            if ($this->user && $this->user->admin_id === $user->admin_id) {
                return true;
            }
            return false;
        }

        return false;
    }

    /**
     * Scope pour les produits en stock (quantité > 0)
     */
    public function scopeInStock($query)
    {
        return $query->where('quantite', '>', 0);
    }

    /**
     * Scope pour les produits d'une catégorie spécifique
     */
    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('categorie_id', $categoryId);
    }

    /**
     * Scope pour les produits vendables par un utilisateur (pour interface vente)
     */
    public function scopeVendableBy($query, User $user)
    {
        return $query->visibleTo($user)->inStock();
    }

    /**
     * Scope pour rechercher un produit par code (barre ou QR)
     */
    public function scopeFindByCode($query, $code)
    {
        return $query->where('code_barre', $code)
                    ->orWhere('code_qr', $code)
                    ->orWhere('id', $code); // Permet aussi de chercher par ID
    }

    /**
     * Relation avec la catégorie
     */
    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    /**
     * Relation avec les lignes de vente
     */
    public function ligneVentes()
    {
        return $this->hasMany(LigneVente::class);
    }

    /**
     * Relation avec les ventes via les lignes de vente
     */
    public function ventes()
    {
        return $this->hasManyThrough(Vente::class, LigneVente::class, 'product_id', 'id', 'id', 'vente_id');
    }

    /**
     * Relation avec les mouvements de stock
     */
    public function mouvements()
    {
        return $this->hasMany(Mouvement::class);
    }

    /**
     * Accessor pour vérifier si le produit est en stock
     */
    public function getEnStockAttribute()
    {
        return $this->quantite > 0;
    }

    /**
     * Accessor pour calculer la valeur totale du stock pour ce produit
     */
    public function getValeurStockAttribute()
    {
        return $this->quantite * $this->prix;
    }

    /**
     * Met à jour la quantité en stock
     */
    public function updateQuantite($nouvelleQuantite)
    {
        $this->quantite = $nouvelleQuantite;
        return $this->save();
    }

    /**
     * Ajoute de la quantité au stock
     */
    public function ajouterStock($quantite)
    {
        $this->quantite += $quantite;
        return $this->save();
    }

    /**
     * Retire de la quantité du stock
     */
    public function retirerStock($quantite)
    {
        if ($this->quantite >= $quantite) {
            $this->quantite -= $quantite;
            return $this->save();
        }
        return false; // Quantité insuffisante
    }

    /**
     * Accessor pour formater le prix pour l'affichage
     */
    public function getPrixFormateAttribute()
    {
        return number_format($this->prix, 0, ',', ' ') . ' FCFA';
    }

    /**
     * Accessor pour formater la valeur du stock
     */
    public function getValeurStockFormateAttribute()
    {
        return number_format($this->valeur_stock, 0, ',', ' ') . ' FCFA';
    }

    /**
     * Recherche des produits par designation ou description
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where('designation', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('code_barre', 'LIKE', "%{$searchTerm}%") // AJOUT: Recherche par code-barres
                    ->orWhere('code_qr', 'LIKE', "%{$searchTerm}%");   // AJOUT: Recherche par QR code
    }

    /**
     * Récupère les produits populaires (ceux avec le plus de ventes)
     */
    public function scopePopulaires($query, $limit = 10)
    {
        return $query->withCount('ligneVentes')
                    ->orderBy('ligne_ventes_count', 'desc')
                    ->limit($limit);
    }

    /**
     * Récupère les produits avec stock faible
     */
    public function scopeStockFaible($query, $seuil = 5)
    {
        return $query->where('quantite', '<=', $seuil)
                    ->where('quantite', '>', 0)
                    ->orderBy('quantite', 'asc');
    }

    /**
     * Récupère les produits en rupture de stock
     */
    public function scopeRuptureStock($query)
    {
        return $query->where('quantite', '<=', 0);
    }

    /**
     * Vérifie si le produit peut être vendu (en stock et accessible)
     */
    public function peutEtreVendu($quantiteDemandee = 1)
    {
        return $this->en_stock && $this->quantite >= $quantiteDemandee;
    }

    /**
     * Récupère le stock disponible
     */
    public function getStockDisponibleAttribute()
    {
        return $this->quantite;
    }

    /**
     * Historique des ventes du produit
     */
    public function historiqueVentes()
    {
        return $this->ligneVentes()
                   ->with('vente')
                   ->orderBy('created_at', 'desc')
                   ->get();
    }

    /**
     * Statistiques de vente du produit
     */
    public function getStatistiquesVentesAttribute()
    {
        $lignes = $this->ligneVentes;
        
        return [
            'total_ventes' => $lignes->count(),
            'quantite_vendue' => $lignes->sum('quantite'),
            'chiffre_affaires' => $lignes->sum('montant_total'),
            'vente_moyenne' => $lignes->avg('quantite') ?? 0,
        ];
    }

    /**
     * Événements du modèle
     */
    protected static function boot()
    {
        parent::boot();

        // Avant la création, assigner l'utilisateur connecté si user_id n'est pas défini
        static::creating(function ($product) {
            if (auth()->check() && !$product->user_id) {
                $product->user_id = auth()->id();
            }
        });

        // Avant la suppression, vérifier s'il n'y a pas de ventes associées
        static::deleting(function ($product) {
            if ($product->ligneVentes()->count() > 0) {
                throw new \Exception('Impossible de supprimer ce produit car il est associé à des ventes.');
            }
        });
    }

    /**
     * Méthode pour dupliquer un produit
     */
    public function dupliquer($nouvelleDesignation = null)
    {
        $nouveauProduit = $this->replicate();
        $nouveauProduit->designation = $nouvelleDesignation ?: $this->designation . ' (Copie)';
        $nouveauProduit->quantite = 0; // Stock à 0 pour la copie
        $nouveauProduit->code_barre = null; // Code-barres unique
        $nouveauProduit->code_qr = null; // QR code unique
        $nouveauProduit->push();

        return $nouveauProduit;
    }

    /**
     * Méthode pour mettre à jour le prix
     */
    public function mettreAJourPrix($nouveauPrix)
    {
        if ($nouveauPrix <= 0) {
            throw new \Exception('Le prix doit être supérieur à 0.');
        }

        $this->prix = $nouveauPrix;
        return $this->save();
    }

    /**
     * Méthode pour générer un code-barres automatiquement
     */
    public function genererCodeBarre()
    {
        if (empty($this->code_barre)) {
            $this->code_barre = 'PROD-' . str_pad($this->id, 6, '0', STR_PAD_LEFT) . '-' . time();
            $this->save();
        }
        return $this->code_barre;
    }

    /**
     * Méthode pour générer un QR code automatiquement
     */
    public function genererQrCode()
    {
        if (empty($this->code_qr)) {
            $this->code_qr = 'QR-' . $this->id . '-' . uniqid();
            $this->save();
        }
        return $this->code_qr;
    }

    /**
     * Représentation string du produit
     */
    public function __toString()
    {
        return $this->designation . ' - ' . $this->prix_formate . ' (Stock: ' . $this->quantite . ')';
    }
}