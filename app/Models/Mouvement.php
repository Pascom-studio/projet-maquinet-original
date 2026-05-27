<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Mouvement extends Model
{
    use HasFactory;

    protected $fillable = [
        'quantite',
        'prix',
        'ancien_stock',
        'nouveau_stock',
        'raison',
        'type',
        'product_id',
        'user_id',
        'statut',
        'annule_par',
        'annule_le'
    ];

    protected $casts = [
        'quantite' => 'integer',
        'prix' => 'decimal:2',
        'ancien_stock' => 'integer',
        'nouveau_stock' => 'integer',
        'annule_le' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function annulePar()
    {
        return $this->belongsTo(User::class, 'annule_par');
    }

    /**
     * Scope pour les mouvements actifs (non annulés)
     */
    public function scopeActifs($query)
    {
        return $query->where('statut', '!=', 'annule')->orWhereNull('statut');
    }

    /**
     * Scope pour les mouvements annulés
     */
    public function scopeAnnules($query)
    {
        return $query->where('statut', 'annule');
    }

    /**
     * Vérifier si le mouvement est annulé
     */
    public function estAnnule()
    {
        return $this->statut === 'annule';
    }

    /**
     * Événements du modèle
     */
    //protected static function boot()
    //{
      //  parent::boot();

       // static::creating(function ($mouvement) {
            // S'assurer que le prix est toujours défini
        //    if (is_null($mouvement->prix) && $mouvement->product) {
        //        $mouvement->prix = $mouvement->product->prix;
       //     }
       // });
   // }
}