<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileMoneyStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'admin_id',
        'operateur',
        'type_mouvement',
        'montant',
        'reference',
        'notes',
        'statut'
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // Scopes
    public function scopeApprovisionnements($query)
    {
        return $query->where('type_mouvement', 'approvisionnement');
    }

    public function scopeRemboursements($query)
    {
        return $query->where('type_mouvement', 'remboursement');
    }

    public function scopeAvoirs($query)
    {
        return $query->where('type_mouvement', 'avoir');
    }

    public function scopeDepenses($query)
    {
        return $query->where('type_mouvement', 'depense');
    }

    public function scopeParOperateur($query, $operateur)
    {
        return $query->where('operateur', $operateur);
    }

    // Accessors
    public function getTypeMouvementLabelAttribute()
    {
        $labels = [
            'approvisionnement' => 'Approvisionnement',
            'remboursement' => 'Remboursement',
            'avoir' => 'Avoir',
            'depense' => 'Dépense'
        ];

        return $labels[$this->type_mouvement] ?? $this->type_mouvement;
    }

    public function getOperateurNomAttribute()
    {
        // Si c'est un avoir, afficher "Liquidité"
        if ($this->type_mouvement === 'avoir') {
            return 'Liquidité';
        }

        $noms = [
            'orange_money' => 'Orange Money',
            'telecel_money' => 'Telecel Money',
            'moov_money' => 'Moov Money',
            'coris_money' => 'Coris Money'
        ];

        return $noms[$this->operateur] ?? $this->operateur;
    }

    public function getMontantFormateAttribute()
    {
        return number_format($this->montant, 0, ',', ' ') . ' F';
    }

    public function getCouleurTypeAttribute()
    {
        $couleurs = [
            'approvisionnement' => 'green',
            'remboursement' => 'red',
            'avoir' => 'blue', 
            'depense' => 'orange'
        ];

        return $couleurs[$this->type_mouvement] ?? 'gray';
    }

    public function getIconeTypeAttribute()
    {
        $icones = [
            'approvisionnement' => '💰',
            'remboursement' => '💳',
            'avoir' => '💎',
            'depense' => '💸'
        ];

        return $icones[$this->type_mouvement] ?? '📊';
    }

    // Méthode pour vérifier si c'est un mouvement positif
    public function getEstPositifAttribute()
    {
        return in_array($this->type_mouvement, ['approvisionnement', 'avoir']);
    }

    // Méthode pour obtenir le signe du montant
    public function getSigneMontantAttribute()
    {
        return $this->est_positif ? '+' : '-';
    }
}