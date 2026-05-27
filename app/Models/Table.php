<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Table extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'nom',
        'numero',
        'user_id', // serveuse affectée
        'statut',
        'admin_id'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($table) {
            if (!$table->admin_id && auth()->check()) {
                $user = auth()->user();
                if ($user->isAdmin()) {
                    $table->admin_id = $user->id;
                } elseif ($user->admin_id) {
                    $table->admin_id = $user->admin_id;
                }
            }
        });
    }

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function commandes()
    {
        return $this->hasMany(Commande::class);
    }

    public function commandesEnCours()
    {
        return $this->hasMany(Commande::class)->where('statut', 'en cours');
    }

    /**
     * CORRECTION DÉFINITIVE : Scope pour la visibilité avec gestion des managers
     */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isAdmin()) {
            return $query->where('admin_id', $user->id);
        }

        // CORRECTION : Ajouter la gestion des managers
        if ($user->isGerant() || $user->isManager()) {
            return $query->where('admin_id', $user->admin_id);
        }

        // Les hôtesses voient seulement les tables qui leur sont affectées
        if ($user->isHotesse()) {
            return $query->where('user_id', $user->id);
        }

        // Par défaut, aucun accès
        return $query->where('id', 0);
    }

    // Scope pour l'unicité du numéro par admin
    public function scopeUniqueNumberForAdmin($query, $numero, User $user)
    {
        return $query->visibleTo($user)->where('numero', $numero);
    }

    // Vérifier si un numéro est unique pour le scope admin
    public static function isNumberUniqueForUser($numero, User $user, $ignoreId = null)
    {
        $query = self::uniqueNumberForAdmin($numero, $user);
        
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }
        
        return !$query->exists();
    }

    // Scope pour les tables affectées
    public function scopeAffectees($query)
    {
        return $query->whereNotNull('user_id');
    }

    // Scope pour les tables libres
    public function scopeLibres($query)
    {
        return $query->whereNull('user_id');
    }

    // Vérifier si la table est affectée
    public function estAffectee()
    {
        return !is_null($this->user_id);
    }

    // Vérifier si la table a des commandes en cours
    public function aCommandesEnCours()
    {
        return $this->commandesEnCours()->exists();
    }

    // Affecter la table à une serveuse
    public function affecterA($userId)
    {
        $this->update([
            'user_id' => $userId,
            'statut' => 'affectée'
        ]);
    }

    // Libérer la table
    public function liberer()
    {
        // Vérifier qu'il n'y a pas de commandes en cours
        if ($this->aCommandesEnCours()) {
            throw new \Exception('Impossible de libérer la table : commandes en cours');
        }

        $this->update([
            'user_id' => null,
            'statut' => 'libre'
        ]);
    }

    /**
     * CORRECTION DÉFINITIVE : Vérifier si l'utilisateur peut gérer cette table
     * avec gestion des managers
     */
    public function canBeManagedBy(User $user)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $this->admin_id === $user->id;
        }

        // CORRECTION : Ajouter la gestion des managers
        if ($user->isGerant() || $user->isManager()) {
            return $this->admin_id === $user->admin_id;
        }

        return false;
    }

    // Accesseurs
    public function getStatutFormattedAttribute()
    {
        return $this->estAffectee() ? 'Affectée' : 'Libre';
    }

    public function getStatutColorAttribute()
    {
        return $this->estAffectee() ? 'success' : 'secondary';
    }

    public function getNomCompletAttribute()
    {
        return "Table {$this->numero} - {$this->nom}";
    }

    public function getServeuseAttribute()
    {
        return $this->user ? $this->user->prenom . ' ' . $this->user->name : 'Non affectée';
    }

    /**
     * NOUVEAU : Méthode pour vérifier rapidement les permissions de gestion
     */
    public function canUserManage(User $user)
    {
        return $this->canBeManagedBy($user);
    }

    /**
     * NOUVEAU : Récupérer les tables d'un admin spécifique
     */
    public static function getTablesForAdmin($adminId)
    {
        return self::where('admin_id', $adminId)->get();
    }

    /**
     * NOUVEAU : Récupérer les tables pour un manager/gérant
     */
    public static function getTablesForManagerGerant(User $user)
    {
        if (!$user->admin_id) {
            return collect();
        }
        
        return self::where('admin_id', $user->admin_id)->get();
    }

    /**
     * NOUVEAU : Statistiques des tables pour un utilisateur
     */
    public static function getStatsForUser(User $user)
    {
        $tables = self::visibleTo($user)->get();
        
        return [
            'total' => $tables->count(),
            'affectees' => $tables->where('user_id', '!=', null)->count(),
            'libres' => $tables->where('user_id', null)->count(),
        ];
    }
}