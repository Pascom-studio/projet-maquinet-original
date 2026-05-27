<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Observation extends Model
{
    use HasFactory;

    protected $fillable = [
        'manager_id',
        'hotesse_id',
        'titre',
        'contenu',
        'type',
        'priorite',
        'date_observation',
        'est_lu'
    ];

    protected $casts = [
        'date_observation' => 'datetime',
        'est_lu' => 'boolean'
    ];

    // Relations
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function hotesse()
    {
        return $this->belongsTo(User::class, 'hotesse_id');
    }

    // Accesseurs
    public function getTypeCouleurAttribute()
    {
        return match($this->type) {
            'positif' => 'success',
            'negatif' => 'danger',
            'suggestion' => 'warning',
            default => 'secondary'
        };
    }

    public function getPrioriteCouleurAttribute()
    {
        return match($this->priorite) {
            'faible' => 'success',
            'moyenne' => 'warning',
            'elevee' => 'danger',
            default => 'secondary'
        };
    }

    public function getEstRecentAttribute()
    {
        return $this->date_observation->gt(now()->subDays(7));
    }

    // Scopes
    public function scopeNonLues($query)
    {
        return $query->where('est_lu', false);
    }

    public function scopeParType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeParPriorite($query, $priorite)
    {
        return $query->where('priorite', $priorite);
    }

    public function scopePourHotesse($query, $hotesseId)
    {
        return $query->where('hotesse_id', $hotesseId);
    }

    public function scopeParManager($query, $managerId)
    {
        return $query->where('manager_id', $managerId);
    }

    /**
 * Relation avec les commentaires
 */
public function commentaires()
{
    return $this->hasMany(Commentaire::class)->recents();
}

/**
 * Vérifie si l'observation a des commentaires
 */
public function getAContenuAttribute()
{
    return $this->commentaires->count() > 0;
}

/**
 * Dernier commentaire
 */
public function getDernierCommentaireAttribute()
{
    return $this->commentaires->first();
}
}