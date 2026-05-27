<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileMoneyCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'operateur',
        'mois',
        'commission_total',
        'commission_nette',
        'taxes_total', 
        'nombre_transactions'
    ];

    protected $attributes = [
        'commission_nette' => 0,
        'taxes_total' => 0,
    ];

    protected $casts = [
        'commission_total' => 'decimal:2',
        'commission_nette' => 'decimal:2',
        'taxes_total' => 'decimal:2',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function getOperateurFormattedAttribute()
    {
        $operateurs = [
            'orange_money' => 'Orange Money',
            'telecel_money' => 'Telecel Money',
            'moov_money' => 'Moov Money',
            'coris_money' => 'Coris Money'
        ];

        return $operateurs[$this->operateur] ?? $this->operateur;
    }

    public function getMoisFormattedAttribute()
    {
        return \Carbon\Carbon::createFromFormat('Y-m', $this->mois)->format('F Y');
    }
}