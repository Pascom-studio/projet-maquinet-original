<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commentaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'observation_id',
        'user_id',
        'contenu'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relation avec l'observation
     */
    public function observation()
    {
        return $this->belongsTo(Observation::class);
    }

    /**
     * Relation avec l'auteur du commentaire
     */
    public function auteur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope pour les commentaires récents
     */
    public function scopeRecents($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}