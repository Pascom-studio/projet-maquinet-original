<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileMoneyTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'cnib',
        'telephone',
        'type_operation',
        'nature',
        'montant',
        'commission',
        'frais', 
        'montant_net', 
        'id_transaction',
        'user_id',
        'admin_id',
        'statut'
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'commission' => 'decimal:2', 
         'frais' => 'decimal:2',
        'montant_net' => 'decimal:2', 
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Types d'opération
    const TYPE_DEPOT = 'depot';
    const TYPE_RETRAIT = 'retrait';

    // Natures de transaction
    const NATURE_ORANGE = 'orange_money';
    const NATURE_TELECEL = 'telecel_money';
    const NATURE_MOOV = 'moov_money';

    // Statuts
    const STATUT_ACTIF = 'actif';
    const STATUT_ANNULE = 'annule';

    /**
     * Relation avec l'utilisateur (caissier)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec l'admin
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Scope pour les transactions récentes (moins de 24h)
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subHours(24));
    }

    /**
     * Scope pour les transactions modifiables (moins de 24h)
     */
    public function scopeModifiable($query)
    {
        return $query->where('created_at', '>=', now()->subHours(24))
                    ->where('statut', self::STATUT_ACTIF);
    }

    /**
     * Vérifie si la transaction peut être modifiée
     */
    public function getPeutEtreModifieAttribute()
    {
        return $this->created_at >= now()->subHours(24) && $this->statut === self::STATUT_ACTIF;
    }

    /**
     * Accesseur pour le type d'opération formaté
     */
    public function getTypeOperationFormattedAttribute()
    {
        return [
            self::TYPE_DEPOT => 'Dépôt',
            self::TYPE_RETRAIT => 'Retrait'
        ][$this->type_operation] ?? $this->type_operation;
    }

    /**
     * Accesseur pour la nature formatée
     */
    public function getNatureFormattedAttribute()
    {
        return [
            self::NATURE_ORANGE => 'Orange Money',
            self::NATURE_TELECEL => 'Telecel Money',
            self::NATURE_MOOV => 'Moov Money'
        ][$this->nature] ?? $this->nature;
    }

    /**
     * Accesseur pour le statut formaté
     */
    public function getStatutFormattedAttribute()
    {
        return [
            self::STATUT_ACTIF => 'Actif',
            self::STATUT_ANNULE => 'Annulé'
        ][$this->statut] ?? $this->statut;
    }

    /**
     * Génère un ID de transaction unique
     */
    public static function genererIdTransaction()
    {
        do {
            $id = 'MM' . now()->format('YmdHis') . rand(100, 999);
        } while (self::where('id_transaction', $id)->exists());

        return $id;
    }
}