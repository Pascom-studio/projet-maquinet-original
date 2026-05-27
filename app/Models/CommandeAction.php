<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommandeAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'commande_id',
        'user_id',
        'action',
        'details',
        'date_action'
    ];

    protected $casts = [
        'date_action' => 'datetime',
        'details' => 'array'
    ];

    // ✅ NOUVEAU : Accesseurs pour les données JSON
    public function getMontantRemisAttribute()
    {
        // Si la colonne existe, l'utiliser
        if (isset($this->attributes['montant_remis'])) {
            return $this->attributes['montant_remis'];
        }
        
        // Sinon, extraire du JSON
        $details = $this->details;
        
        if (is_array($details) && isset($details['montant_remis'])) {
            return (float) $details['montant_remis'];
        }
        
        if (is_string($details)) {
            $decoded = json_decode($details, true);
            return isset($decoded['montant_remis']) ? (float) $decoded['montant_remis'] : 0;
        }
        
        return 0;
    }

    public function getMontantCommandeAttribute()
    {
        // Si la colonne existe, l'utiliser
        if (isset($this->attributes['montant_commande'])) {
            return $this->attributes['montant_commande'];
        }
        
        // Sinon, extraire du JSON
        $details = $this->details;
        
        if (is_array($details) && isset($details['montant_commande'])) {
            return (float) $details['montant_commande'];
        }
        
        if (is_string($details)) {
            $decoded = json_decode($details, true);
            return isset($decoded['montant_commande']) ? (float) $decoded['montant_commande'] : 0;
        }
        
        return 0;
    }

    public function getMontantRestantAttribute()
    {
        return $this->montant_commande - $this->montant_remis;
    }

    public function getMethodePaiementAttribute()
    {
        // Si la colonne existe, l'utiliser
        if (isset($this->attributes['methode_paiement'])) {
            return $this->attributes['methode_paiement'];
        }
        
        // Sinon, extraire du JSON
        $details = $this->details;
        
        if (is_array($details) && isset($details['methode_paiement'])) {
            return $details['methode_paiement'];
        }
        
        if (is_string($details)) {
            $decoded = json_decode($details, true);
            return $decoded['methode_paiement'] ?? null;
        }
        
        return null;
    }

    // ✅ Accesseurs formatés pour affichage
    public function getMontantRemisFormattedAttribute()
    {
        return number_format($this->montant_remis, 0, ',', ' ') . ' FCFA';
    }

    public function getMontantCommandeFormattedAttribute()
    {
        return number_format($this->montant_commande, 0, ',', ' ') . ' FCFA';
    }

    public function getMontantRestantFormattedAttribute()
    {
        return number_format($this->montant_restant, 0, ',', ' ') . ' FCFA';
    }

    // Relations
    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ✅ Scope pour les actions de soldage
    public function scopeSoldes($query)
    {
        return $query->where('action', 'solder');
    }

    // ✅ Scope pour inclure les données extraites du JSON
    public function scopeWithJsonData($query)
    {
        return $query->selectRaw("
            *,
            CASE 
                WHEN JSON_VALID(details) 
                THEN CAST(JSON_UNQUOTE(JSON_EXTRACT(details, '$.montant_commande')) AS DECIMAL(10,2))
                ELSE 0 
            END as montant_commande_json,
            CASE 
                WHEN JSON_VALID(details) 
                THEN CAST(JSON_UNQUOTE(JSON_EXTRACT(details, '$.montant_remis')) AS DECIMAL(10,2))
                ELSE 0 
            END as montant_remis_json,
            CASE 
                WHEN JSON_VALID(details) 
                THEN JSON_UNQUOTE(JSON_EXTRACT(details, '$.methode_paiement'))
                ELSE NULL 
            END as methode_paiement_json
        ");
    }
}