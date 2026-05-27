<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionCaisse extends Model
{
    use HasFactory;

    protected $fillable = [
        'caisse_id',
        'user_id',
        'type',
        'montant',
        'description'
    ];

    public function caisse()
    {
        return $this->belongsTo(Caisse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}