<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caisse extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'statut',
        'solde_ouverture',
        'solde_actuel',
        'solde_fermeture',
        'total_ventes',
        'total_retraits',
        'total_approvisionnements',
        'total_depenses',
        'total_commandes_soldees', // AJOUT: Pour stocker le total des commandes soldées
        'date_ouverture',
        'date_fermeture'
    ];

    protected $casts = [
        'solde_ouverture' => 'decimal:2',
        'solde_actuel' => 'decimal:2',
        'solde_fermeture' => 'decimal:2',
        'total_ventes' => 'decimal:2',
        'total_retraits' => 'decimal:2',
        'total_approvisionnements' => 'decimal:2',
        'total_depenses' => 'decimal:2',
        'total_commandes_soldees' => 'decimal:2', // AJOUT
        'date_ouverture' => 'datetime',
        'date_fermeture' => 'datetime',
    ];

    /**
     * Accesseur pour calculer le solde actuel en incluant les commandes soldées
     */
    public function getSoldeActuelAttribute()
    {
        // Si la caisse est fermée, retourner le solde de fermeture
        if ($this->statut === 'fermee') {
            return $this->solde_fermeture;
        }

        // Calculer le total des commandes soldées pour la période de la caisse
        $total_commandes_soldees = \App\Models\Commande::where('user_id', $this->user_id)
            ->where('statut', 'soldée')
            ->whereBetween('updated_at', [$this->date_ouverture, now()])
            ->sum('montant');

        // Calculer le solde actuel
        return $this->solde_ouverture 
            + $this->total_ventes 
            + $this->total_approvisionnements 
            + $total_commandes_soldees
            - $this->total_retraits 
            - $this->total_depenses;
    }

    /**
     * Accesseur pour obtenir le total des commandes soldées
     */
    public function getTotalCommandesSoldeesAttribute()
    {
        if ($this->statut === 'fermee' && $this->attributes['total_commandes_soldees']) {
            // Si la caisse est fermée et qu'on a stocké la valeur, la retourner
            return $this->attributes['total_commandes_soldees'];
        }

        // Sinon calculer en temps réel
        return \App\Models\Commande::where('user_id', $this->user_id)
            ->where('statut', 'soldée')
            ->whereBetween('updated_at', [$this->date_ouverture, now()])
            ->sum('montant');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(TransactionCaisse::class);
    }

    public function ventes()
    {
        return $this->hasMany(Vente::class);
    }

    public function estOuverte()
    {
        return $this->statut === 'ouverte';
    }
}