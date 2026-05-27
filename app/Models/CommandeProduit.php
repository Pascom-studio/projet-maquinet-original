<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommandeProduit extends Model
{
    use HasFactory;

    protected $fillable = [
        'commande_id',
        'produit_id', // CORRECTION: 'produit_id' au lieu de 'product_id'
        'quantite',
        'prix_unitaire',
        'prix_total'
    ];

    protected $casts = [
        'quantite' => 'integer',
        'prix_unitaire' => 'decimal:2',
        'prix_total' => 'decimal:2'
    ];

    // Relations
    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function produit()
    {
        return $this->belongsTo(Product::class, 'produit_id'); // CORRECTION: 'produit_id'
    }

    /**
     * Alias pour la relation product (pour compatibilité)
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'produit_id');
    }

    // Calculer le prix total pour CETTE ligne de commande
    public function calculerPrixTotal()
    {
        return $this->prix_unitaire * $this->quantite;
    }

    // Mettre à jour le prix total
    public function mettreAJourPrixTotal()
    {
        $this->update([
            'prix_total' => $this->calculerPrixTotal()
        ]);
    }

    // Accesseurs
    public function getPrixUnitaireFormattedAttribute()
    {
        return number_format($this->prix_unitaire, 0, ',', ' ') . ' FCFA';
    }

    public function getPrixTotalFormattedAttribute()
    {
        return number_format($this->prix_total, 0, ',', ' ') . ' FCFA';
    }

    public function getDesignationAttribute()
    {
        return $this->produit ? $this->produit->designation : 'Produit supprimé';
    }
}