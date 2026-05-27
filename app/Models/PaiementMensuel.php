<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaiementMensuel extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'annee',
        'mois',
        'montant',
        'est_paye',
        'date_paiement',
        'paye_par'
    ];

    protected $casts = [
        'est_paye' => 'boolean',
        'date_paiement' => 'datetime',
        'montant' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payeur()
    {
        return $this->belongsTo(User::class, 'paye_par');
    }

    /**
     * Scope pour un utilisateur spécifique
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope pour une année et un mois spécifiques
     */
    public function scopeForPeriode($query, $annee, $mois)
    {
        return $query->where('annee', $annee)->where('mois', $mois);
    }
}