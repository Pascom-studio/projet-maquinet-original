<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class VenteMultiple extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_vente',
        'user_id',
        'caisse_id',
        'montant_total'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function caisse()
    {
        return $this->belongsTo(Caisse::class);
    }

    public function lignes()
    {
        return $this->hasMany(LigneVente::class);
    }

    public static function genererNumeroVente()
    {
        $date = now()->format('Ymd');
        $lastVente = self::where('numero_vente', 'like', "V{$date}%")->latest()->first();
        
        $sequence = $lastVente ? (int)substr($lastVente->numero_vente, -4) + 1 : 1;
        
        return "V{$date}" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}