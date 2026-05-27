<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Categorie extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'nom',
        'description', 
        'user_id'
    ];

    /**
     * Relation avec l'utilisateur (créateur de la catégorie)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec les produits
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Scope pour l'unicité du nom par scope admin
     */
    public function scopeUniqueForAdmin($query, $nom, User $user)
    {
        if ($user->isSuperAdmin()) {
            // Le super admin voit tout, donc unicité globale
            return $query->where('nom', $nom);
        }

        if ($user->isAdmin()) {
            // Admin : unicité dans son scope (ses catégories + celles de ses sub-users)
            return $query->where('nom', $nom)
                        ->where(function($q) use ($user) {
                            $q->where('user_id', $user->id)
                              ->orWhereHas('user', function($subQ) use ($user) {
                                  $subQ->where('admin_id', $user->id);
                              });
                        });
        }

        if ($user->isGerant() || $user->isCaissier()) {
            // Gérants/Caissiers : unicité dans le scope de leur admin parent
            return $query->where('nom', $nom)
                        ->whereHas('user', function($q) use ($user) {
                            $q->where('admin_id', $user->admin_id)
                              ->orWhere('id', $user->admin_id);
                        });
        }

        // Par défaut : unicité pour l'utilisateur lui-même
        return $query->where('nom', $nom)
                    ->where('user_id', $user->id);
    }

    /**
     * Vérifier si un nom est unique pour le scope admin de l'utilisateur
     */
    public static function isNameUniqueForUser($nom, User $user, $ignoreId = null)
    {
        $query = self::uniqueForAdmin($nom, $user);
        
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }
        
        return !$query->exists();
    }

    /**
     * Scope pour filtrer les catégories visibles selon les permissions
     */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isAdmin()) {
            // Admin voit ses catégories + celles de ses sub-users
            return $query->where('user_id', $user->id)
                        ->orWhereHas('user', function($q) use ($user) {
                            $q->where('admin_id', $user->id);
                        });
        }

        if ($user->isGerant() || $user->isCaissier()) {
            // Gérants et caissiers voient les catégories de leur admin parent
            return $query->whereHas('user', function($q) use ($user) {
                $q->where('admin_id', $user->admin_id)
                  ->orWhere('id', $user->admin_id); // + les catégories de l'admin lui-même
            });
        }

        // Par défaut : seulement ses propres catégories
        return $query->where('user_id', $user->id);
    }

    /**
     * Vérifier si l'utilisateur peut gérer cette catégorie
     */
    public function canBeManagedBy(User $user)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            // Admin peut gérer ses catégories + celles de ses sub-users
            return $this->user_id === $user->id || 
                   ($this->user && $this->user->admin_id === $user->id);
        }

        if ($user->isGerant()) {
            // Gérant peut gérer les catégories de son admin parent
            if ($this->user_id === $user->admin_id) {
                return true;
            }
            // Vérifier si la catégorie appartient à un sub-user du même admin
            if ($this->user && $this->user->admin_id === $user->admin_id) {
                return true;
            }
            return false;
        }

        // Caissier ne peut gérer aucune catégorie
        return false;
    }

    /**
     * Vérifier si la catégorie peut être supprimée
     */
    public function canBeDeleted()
    {
        return $this->products()->count() === 0;
    }

    /**
     * Accessor pour le nombre de produits
     */
    public function getNombreProduitsAttribute()
    {
        return $this->products()->count();
    }

    /**
     * Accessor pour vérifier si la catégorie est utilisée
     */
    public function getEstUtiliseeAttribute()
    {
        return $this->nombre_produits > 0;
    }

    /**
     * Recherche des catégories par nom
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where('nom', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('description', 'LIKE', "%{$searchTerm}%");
    }

    /**
     * Récupère les catégories populaires (avec le plus de produits)
     */
    public function scopePopulaires($query, $limit = 10)
    {
        return $query->withCount('products')
                    ->orderBy('products_count', 'desc')
                    ->limit($limit);
    }

    /**
     * Événements du modèle
     */
    protected static function boot()
    {
        parent::boot();

        // Avant la suppression, vérifier s'il n'y a pas de produits associés
        static::deleting(function ($categorie) {
            if ($categorie->products()->count() > 0) {
                throw new \Exception('Impossible de supprimer cette catégorie car elle est associée à des produits.');
            }
        });
    }

    /**
     * Représentation string de la catégorie
     */
    public function __toString()
    {
        return $this->nom . ($this->est_utilisee ? ' (' . $this->nombre_produits . ' produits)' : '');
    }
}