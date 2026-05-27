<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Commande extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_commande',
        'user_id',
        'hotesse_id',
        'table_id',
        'montant',
        'statut',
        'notes',
        'description',
        'admin_id',
        'methode_paiement',
        'montant_remis',
        'monnaie_rendue'
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'montant_remis' => 'decimal:2',
        'monnaie_rendue' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $appends = [
        'peut_etre_modifiee',
        'montant_formatted',
        'statut_formatted',
        'created_at_formatted',
        'nombre_produits',
        'liste_produits',
        'nom_admin_parent',
        'titre_admin_parent'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($commande) {
            if (!$commande->numero_commande) {
                $commande->numero_commande = self::genererNumeroCommande();
            }
            if (!$commande->admin_id && auth()->check()) {
                $user = auth()->user();
                if ($user->isAdmin()) {
                    $commande->admin_id = $user->id;
                } elseif ($user->admin_id) {
                    $commande->admin_id = $user->admin_id;
                }
            }
        });

        static::created(function ($commande) {
            // Calculer automatiquement la monnaie rendue si pas définie
            if ($commande->montant_remis && !$commande->monnaie_rendue) {
                $commande->update([
                    'monnaie_rendue' => $commande->montant_remis - $commande->montant
                ]);
            }
        });
    }

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function hotesse()
    {
        return $this->belongsTo(User::class, 'hotesse_id');
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function actions()
    {
        return $this->hasMany(CommandeAction::class);
    }

    // Relation avec les produits de la commande
    public function produits()
    {
        return $this->hasMany(CommandeProduit::class, 'commande_id');
    }

    // Relation avec les produits via la table pivot
    public function products()
    {
        return $this->belongsToMany(Product::class, 'commande_produits', 'commande_id', 'produit_id')
                    ->withPivot('quantite', 'prix_unitaire', 'prix_total')
                    ->withTimestamps();
    }

    /**
     * NOUVELLE RELATION : Récupère l'admin parent responsable
     */
    public function adminParent()
    {
        return $this->hasOneThrough(
            User::class,
            User::class,
            'id', // Clé étrangère sur la table users (caissier)
            'id', // Clé étrangère sur la table users (admin)
            'user_id', // Clé locale sur commandes
            'admin_id' // Clé intermédiaire sur users
        )->where(function($query) {
            $query->where('fonction', 'admin')
                  ->orWhere('fonction', 'super_admin');
        });
    }

    /**
     * Accesseur pour l'admin parent avec logique robuste
     */
    public function getAdminParentAttribute()
    {
        $currentUser = $this->user;
        
        if (!$currentUser) {
            return $this->getFallbackAdmin();
        }

        // Logique hiérarchique améliorée
        $adminParent = null;

        // 1. Priorité: admin_id direct de l'utilisateur
        if ($currentUser->admin_id) {
            $adminParent = User::find($currentUser->admin_id);
            if ($adminParent && ($adminParent->isAdmin() || $adminParent->isSuperAdmin())) {
                return $adminParent;
            }
        }

        // 2. L'utilisateur est lui-même admin
        if ($currentUser->isAdmin() || $currentUser->isSuperAdmin()) {
            return $currentUser;
        }

        // 3. Recherche via parent_id
        if ($currentUser->parent_id) {
            $adminParent = User::find($currentUser->parent_id);
            if ($adminParent && ($adminParent->isAdmin() || $adminParent->isSuperAdmin())) {
                return $adminParent;
            }
        }

        // 4. Utiliser l'admin_id de la commande
        if ($this->admin_id) {
            $adminParent = User::find($this->admin_id);
            if ($adminParent && ($adminParent->isAdmin() || $adminParent->isSuperAdmin())) {
                return $adminParent;
            }
        }

        // 5. Solution de secours
        return $this->getFallbackAdmin();
    }

    /**
     * Méthode de secours pour trouver un admin
     */
    private function getFallbackAdmin()
    {
        return User::where('fonction', 'admin')
            ->orWhere('fonction', 'super_admin')
            ->orderBy('created_at', 'asc')
            ->first();
    }

    /**
     * Récupérer le nom complet de l'admin parent pour les reçus
     */
    public function getNomAdminParentAttribute()
    {
        $admin = $this->admin_parent;
        return $admin ? ($admin->prenom . ' ' . $admin->nom) : 'GestCool';
    }

    /**
     * Récupérer le titre de l'admin parent
     */
    public function getTitreAdminParentAttribute()
    {
        $admin = $this->admin_parent;
        
        if (!$admin) {
            return 'Système de Gestion';
        }

        switch ($admin->fonction) {
            case 'super_admin':
                return 'Super Administrateur';
            case 'admin':
                return 'Administrateur';
            case 'gerant':
                return 'Gérant';
            default:
                return 'Responsable';
        }
    }

    // Scopes pour la visibilité
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isAdmin()) {
            return $query->where('admin_id', $user->id);
        }

        if ($user->isGerant()) {
            return $query->where('admin_id', $user->admin_id);
        }

        if ($user->isCaissier()) {
            return $query->where('user_id', $user->id);
        }

        if ($user->isHotesse()) {
            return $query->where('hotesse_id', $user->id);
        }

        return $query;
    }

    // Commandes non soldées
    public function scopeNonSoldees($query)
    {
        return $query->where('statut', '!=', 'soldée');
    }

    // Commandes soldées
    public function scopeSoldees($query)
    {
        return $query->where('statut', 'soldée');
    }

    // Commandes en cours
    public function scopeEnCours($query)
    {
        return $query->where('statut', 'en cours');
    }

    // Générer un numéro de commande unique
    public static function genererNumeroCommande()
    {
        $date = now()->format('Ymd');
        $lastCommande = static::where('numero_commande', 'like', "CMD{$date}%")->latest()->first();
        
        $sequence = $lastCommande ? (int)substr($lastCommande->numero_commande, -4) + 1 : 1;
        
        return "CMD{$date}" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    // Calculer le montant total basé sur les produits
    public function calculerMontantTotal()
    {
        return $this->produits->sum('prix_total');
    }

    // Mettre à jour le montant total
    public function mettreAJourMontantTotal()
    {
        $nouveauMontant = $this->calculerMontantTotal();
        $this->update(['montant' => $nouveauMontant]);
        return $nouveauMontant;
    }

    // Vérifier si la commande peut être modifiée (24 heures)
    public function getPeutEtreModifieeAttribute()
    {
        if (!$this->created_at) {
            return false;
        }
        return $this->created_at->diffInHours(now()) <= 24 && $this->statut !== 'soldée';
    }

    // Vérifier les permissions de modification
    public function peutEtreModifieePar(User $user)
    {
        if (!$this->peut_etre_modifiee) {
            return false;
        }
        
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isGerant()) {
            return true;
        }

        if ($user->isCaissier()) {
            return $this->user_id === $user->id;
        }

        return false;
    }

    // Vérifier si l'utilisateur peut voir cette commande
    public function peutEtreVuePar(User $user)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $this->admin_id === $user->id;
        }

        if ($user->isGerant()) {
            return $this->admin_id === $user->admin_id;
        }

        if ($user->isCaissier()) {
            return $this->user_id === $user->id;
        }

        if ($user->isHotesse()) {
            return $this->hotesse_id === $user->id;
        }

        return false;
    }

    // Marquer comme soldée
    public function solder()
    {
        $this->update(['statut' => 'soldée']);
    }

    // Annuler la commande
    public function annuler()
    {
        // Restaurer le stock des produits
        foreach ($this->produits as $commandeProduit) {
            $produit = $commandeProduit->produit;
            if ($produit) {
                $produit->increment('quantite', $commandeProduit->quantite);
            }
        }

        $this->update(['statut' => 'annulée']);
    }

    // Ajouter un produit à la commande
    public function ajouterProduit(Product $product, $quantite)
    {
        // Vérifier le stock
        if ($product->quantite < $quantite) {
            throw new \Exception("Stock insuffisant pour {$product->designation}. Stock disponible: {$product->quantite}");
        }

        $prixTotal = $product->prix * $quantite;

        // Créer la ligne de commande
        $commandeProduit = CommandeProduit::create([
            'commande_id' => $this->id,
            'produit_id' => $product->id,
            'quantite' => $quantite,
            'prix_unitaire' => $product->prix,
            'prix_total' => $prixTotal
        ]);

        // Mettre à jour le stock
        $product->decrement('quantite', $quantite);

        // Mettre à jour le montant total de la commande
        $this->mettreAJourMontantTotal();

        return $commandeProduit;
    }

    // Accesseurs formatés
    public function getMontantFormattedAttribute()
    {
        return number_format($this->montant, 0, ',', ' ') . ' FCFA';
    }

    public function getMontantRemisFormattedAttribute()
    {
        return $this->montant_remis ? number_format($this->montant_remis, 0, ',', ' ') . ' FCFA' : '0 FCFA';
    }

    public function getMonnaieRendueFormattedAttribute()
    {
        return $this->monnaie_rendue ? number_format($this->monnaie_rendue, 0, ',', ' ') . ' FCFA' : '0 FCFA';
    }

    public function getStatutColorAttribute()
    {
        return match($this->statut) {
            'soldée' => 'success',
            'en cours' => 'warning',
            'annulée' => 'danger',
            default => 'info'
        };
    }

    public function getStatutFormattedAttribute()
    {
        return ucfirst($this->statut);
    }

    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at ? $this->created_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i');
    }

    public function getUpdatedAtFormattedAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i');
    }

    // Nombre total de produits dans la commande
    public function getNombreProduitsAttribute()
    {
        return $this->produits->sum('quantite');
    }

    // Liste des produits formatée
    public function getListeProduitsAttribute()
    {
        return $this->produits->map(function ($commandeProduit) {
            return "{$commandeProduit->quantite} x {$commandeProduit->designation}";
        })->implode(', ');
    }

    /**
     * Préparer les données des produits pour le reçu
     */
    public function getProduitsPourRecuAttribute()
    {
        return $this->produits->map(function ($commandeProduit) {
            return [
                'designation' => $commandeProduit->produit->designation ?? 'Produit inconnu',
                'quantite' => $commandeProduit->quantite,
                'prix_unitaire' => $commandeProduit->prix_unitaire,
                'total' => $commandeProduit->prix_total
            ];
        });
    }
}