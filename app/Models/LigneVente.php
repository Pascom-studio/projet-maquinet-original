<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;
class LigneVente extends Model
{
    use HasFactory;

    protected $table = 'ligne_ventes';

    protected $fillable = [
        'vente_id',
        'product_id',
        'quantite',
        'prix_unitaire',
        'montant_total'
    ];

    // Relations
    public function vente()
    {
        return $this->belongsTo(Vente::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Calculer le montant total de la ligne
    public function calculerMontantTotal()
    {
        return $this->quantite * $this->prix_unitaire;
    }
}