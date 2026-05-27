<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Traits\Auditable;

class Vente extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'user_id',
        'caisse_id',
        'montant_total',
        'numero_vente',
        'cash_client',
        'monnaie_rendue',
        'notes'
    ];

    protected $casts = [
        'cash_client' => 'decimal:2',
        'monnaie_rendue' => 'decimal:2',
        'montant_total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $appends = [
        'cash_client_formatted',
        'monnaie_rendue_formatted',
        'montant_total_formatted',
        'created_at_formatted',
        'peut_etre_modifiee',
        'admin_parent'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vente) {
            if (empty($vente->numero_vente)) {
                $vente->numero_vente = static::genererNumeroVente();
            }
            if (!$vente->created_at) {
                $vente->created_at = now();
            }
        });

        static::created(function ($vente) {
            // Calculer automatiquement la monnaie rendue si pas définie
            if (!$vente->monnaie_rendue && $vente->cash_client && $vente->montant_total) {
                $vente->update([
                    'monnaie_rendue' => $vente->cash_client - $vente->montant_total
                ]);
            }
        });
    }

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function caisse()
    {
        return $this->belongsTo(Caisse::class);
    }

    public function lignesVente()
    {
        return $this->hasMany(LigneVente::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'ligne_ventes')
                    ->withPivot('quantite', 'prix_unitaire', 'montant_total')
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
            'user_id', // Clé locale sur ventes
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

        // 4. Solution de secours
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

    // Scopes
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isAdmin()) {
            return $query->where('user_id', $user->id)
                        ->orWhereHas('user', function($q) use ($user) {
                            $q->where('admin_id', $user->id);
                        });
        }

        if ($user->isGerant()) {
            return $query->whereHas('user', function($q) use ($user) {
                $q->where('admin_id', $user->admin_id);
            });
        }

        return $query->where('user_id', $user->id);
    }

    public function scopeDuJour($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subHours(24));
    }

    public function scopeDeUtilisateur($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Générer un numéro de vente unique
    public static function genererNumeroVente()
    {
        $date = now()->format('Ymd');
        $lastVente = static::where('numero_vente', 'like', "V{$date}%")->latest()->first();
        
        $sequence = $lastVente ? (int)substr($lastVente->numero_vente, -4) + 1 : 1;
        
        return "V{$date}" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    // Vérifications de permissions
    public function getPeutEtreModifieeAttribute()
    {
        return $this->peutEtreModifiee();
    }

    public function peutEtreModifiee()
    {
        if (!$this->created_at) {
            return false;
        }
        
        $hoursDiff = $this->created_at->diffInHours(now());
        return $hoursDiff < 24;
    }

    public function peutEtreModifieePar(User $user)
    {
        if ($user->isSuperAdmin()) {
            return $this->peutEtreModifiee();
        }

        if ($user->isAdmin()) {
            $canManage = $this->user_id === $user->id || 
                        ($this->user && $this->user->admin_id === $user->id);
            return $canManage && $this->peutEtreModifiee();
        }

        if ($user->isGerant()) {
            $canManage = $this->user && $this->user->admin_id === $user->admin_id;
            return $canManage && $this->peutEtreModifiee();
        }

        return $this->user_id === $user->id && $this->peutEtreModifiee();
    }

    public function canBeViewedBy(User $user)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $this->user_id === $user->id || 
                   ($this->user && $this->user->admin_id === $user->id);
        }

        if ($user->isGerant()) {
            return $this->user && $this->user->admin_id === $user->admin_id;
        }

        return $this->user_id === $user->id;
    }

    // Calculs
    public function calculerMontantTotal()
    {
        return $this->lignesVente->sum('montant_total');
    }

    public function calculerMonnaie()
    {
        return $this->cash_client - $this->montant_total;
    }

    public function cashClientSuffisant()
    {
        return $this->cash_client >= $this->montant_total;
    }

    // Accesseurs formatés
    public function getCashClientFormattedAttribute()
    {
        return number_format($this->cash_client, 0, ',', ' ') . ' FCFA';
    }

    public function getMonnaieRendueFormattedAttribute()
    {
        return number_format($this->monnaie_rendue, 0, ',', ' ') . ' FCFA';
    }

    public function getMontantTotalFormattedAttribute()
    {
        return number_format($this->montant_total, 0, ',', ' ') . ' FCFA';
    }

    public function getCreatedAtFormattedAttribute()
    {
        return ($this->created_at ?? now())->format('d/m/Y H:i');
    }

    // Méthodes utilitaires
    public function getCreatedAtSafeAttribute()
    {
        return $this->created_at ?? now();
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
}