<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\Auditable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Auditable;

    // Constantes pour les fonctions
    const FONCTION_SUPER_ADMIN = 'super_admin';
    const FONCTION_ADMIN = 'admin';
    const FONCTION_GERANT = 'gerant';
    const FONCTION_CAISSIER = 'caissier';
    const FONCTION_HOTESSE = 'hotesse';
    const FONCTION_MANAGER = 'manager';
    const FONCTION_MOBILE_CAISSIER = 'mobile_caissier';
    const FONCTION_COMMERCIAL = 'commercial';
    const FONCTION_GRANDE_CAISSE_MOBILE = 'grande_caisse_mobile';

    protected $fillable = [
        'prenom',
        'name', 
        'email',
        'password',
        'fonction',
        'admin_id',
        'commercial_id',
        'grande_caisse_id',
        'contact',
        'adresse',
        'est_actif',
        'paiements_mensuels'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'est_actif' => 'boolean',
        'paiements_mensuels' => 'array',
    ];

    public function caisse()
    {
        return $this->hasOne(Caisse::class, 'user_id');
    }

    // Relations hiérarchiques
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function subUsers()
    {
        return $this->hasMany(User::class, 'admin_id');
    }

    // Relations avec les commerciaux
    public function commercial()
    {
        return $this->belongsTo(User::class, 'commercial_id')->where('fonction', self::FONCTION_COMMERCIAL);
    }

    public function mobileCaissiersCommercial()
    {
        return $this->hasMany(User::class, 'commercial_id')->where('fonction', self::FONCTION_MOBILE_CAISSIER);
    }

    // Relations avec la grande caisse mobile
    public function grandeCaisseMobile()
    {
        return $this->belongsTo(User::class, 'grande_caisse_id')->where('fonction', self::FONCTION_GRANDE_CAISSE_MOBILE);
    }

    public function comptesMobileCaissiers()
    {
        return $this->hasMany(User::class, 'grande_caisse_id')->where('fonction', self::FONCTION_MOBILE_CAISSIER);
    }

    // Relations avec les tables
    public function tables()
    {
        return $this->hasMany(Table::class, 'user_id');
    }

    // Relations avec les données
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function categories()
    {
        return $this->hasMany(Categorie::class);
    }

    public function ventes()
    {
        return $this->hasMany(Vente::class);
    }

    public function mouvements()
    {
        return $this->hasMany(Mouvement::class);
    }

    // Relations avec les observations
    public function observationsEnvoyees()
    {
        return $this->hasMany(Observation::class, 'manager_id');
    }

    public function observationsRecues()
    {
        return $this->hasMany(Observation::class, 'hotesse_id');
    }

    // Relations Mobile Money
    public function mobileMoneyTransactions()
    {
        return $this->hasMany(MobileMoneyTransaction::class, 'user_id');
    }

    public function mobileMoneyCommissions()
    {
        return $this->hasMany(MobileMoneyCommission::class, 'admin_id');
    }

    // Relations spécifiques par rôle
    public function caissiers()
    {
        return $this->hasMany(User::class, 'admin_id')->where('fonction', self::FONCTION_CAISSIER);
    }

    public function gerants()
    {
        return $this->hasMany(User::class, 'admin_id')->where('fonction', self::FONCTION_GERANT);
    }

    public function admins()
    {
        return $this->hasMany(User::class, 'admin_id')->where('fonction', self::FONCTION_ADMIN);
    }

    public function hotesses()
    {
        return $this->hasMany(User::class, 'admin_id')->where('fonction', self::FONCTION_HOTESSE);
    }

    public function managers()
    {
        return $this->hasMany(User::class, 'admin_id')->where('fonction', self::FONCTION_MANAGER);
    }

    public function mobileCaissiers()
    {
        return $this->hasMany(User::class, 'admin_id')->where('fonction', self::FONCTION_MOBILE_CAISSIER);
    }

    public function commerciaux()
    {
        return $this->hasMany(User::class, 'admin_id')->where('fonction', self::FONCTION_COMMERCIAL);
    }

    public function grandesCaissesMobile()
    {
        return $this->hasMany(User::class, 'admin_id')->where('fonction', self::FONCTION_GRANDE_CAISSE_MOBILE);
    }

    // Méthodes de vérification de rôle
    public function isSuperAdmin()
    {
        return $this->fonction === self::FONCTION_SUPER_ADMIN;
    }

    public function isAdmin()
    {
        return $this->fonction === self::FONCTION_ADMIN;
    }

    public function isGerant()
    {
        return $this->fonction === self::FONCTION_GERANT;
    }

    public function isCaissier()
    {
        return $this->fonction === self::FONCTION_CAISSIER;
    }

    public function isHotesse()
    {
        return $this->fonction === self::FONCTION_HOTESSE;
    }

    public function isManager()
    {
        return $this->fonction === self::FONCTION_MANAGER;
    }

    public function isMobileCaissier()
    {
        return $this->fonction === self::FONCTION_MOBILE_CAISSIER;
    }

    public function isCommercial()
    {
        return $this->fonction === self::FONCTION_COMMERCIAL;
    }

    public function isGrandeCaisseMobile()
    {
        return $this->fonction === self::FONCTION_GRANDE_CAISSE_MOBILE;
    }

    /**
     * Vérifier si l'utilisateur peut gérer un autre utilisateur
     */
    public function canManageUser(User $user)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isAdmin()) {
            return $user->admin_id === $this->id || $user->id === $this->id;
        }

        if ($this->isGerant()) {
            return $user->admin_id === $this->admin_id;
        }

        if ($this->isManager()) {
            return $user->fonction === self::FONCTION_HOTESSE && $user->admin_id === $this->admin_id;
        }

        if ($this->isCommercial()) {
            return $user->commercial_id === $this->id && $user->fonction === self::FONCTION_MOBILE_CAISSIER;
        }

        if ($this->isGrandeCaisseMobile()) {
            return $user->grande_caisse_id === $this->id && $user->fonction === self::FONCTION_MOBILE_CAISSIER;
        }

        return false;
    }

    /**
     * Vérifier si l'utilisateur peut gérer une table
     */
    public function canManageTable($table)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isAdmin()) {
            return $table->admin_id === $this->id;
        }

        if ($this->isGerant() || $this->isManager()) {
            return $table->admin_id === $this->admin_id;
        }

        return false;
    }

    /**
     * Vérifier si l'utilisateur peut gérer un produit
     */
    public function canManageProduct(Product $product)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isAdmin()) {
            return $product->user_id === $this->id || 
                   ($product->user && $product->user->admin_id === $this->id);
        }

        return $product->user && $product->user->admin_id === $this->admin_id;
    }

    /**
     * Vérifier si l'utilisateur peut gérer une catégorie
     */
    public function canManageCategorie(Categorie $categorie)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isAdmin()) {
            return $categorie->user_id === $this->id || 
                   ($categorie->user && $categorie->user->admin_id === $this->id);
        }

        return $categorie->user && $categorie->user->admin_id === $this->admin_id;
    }

    /**
     * NOUVELLE MÉTHODE : Obtenir les paiements optimisés (une seule requête pour tous les mois)
     */
    public function getPaiementsOptimises($annee)
    {
        $paiements = $this->paiements_mensuels ?? [];
        $result = [];
        
        for ($mois = 1; $mois <= 12; $mois++) {
            $cle = "{$annee}-{$mois}";
            $result[$mois] = [
                'paye' => isset($paiements[$cle]) && 
                         isset($paiements[$cle]['paye']) && 
                         $paiements[$cle]['paye'] === true,
                'date_paiement' => isset($paiements[$cle]['date_paiement']) ? $paiements[$cle]['date_paiement'] : null,
                'montant' => isset($paiements[$cle]['montant']) ? $paiements[$cle]['montant'] : 0,
                'paye_par' => isset($paiements[$cle]['paye_par']) ? $paiements[$cle]['paye_par'] : null,
                'cle' => $cle,
                'annee' => $annee,
                'mois' => $mois
            ];
        }
        
        return $result;
    }

    /**
     * CORRECTION COMPLÈTE : Marquer un paiement mensuel comme effectué
     */
    public function marquerPaiementMensuel($annee, $mois)
    {
        try {
            \Log::info("=== DÉBUT marquerPaiementMensuel ===", [
                'user_id' => $this->id,
                'nom' => $this->nomComplet,
                'annee' => $annee,
                'mois' => $mois
            ]);
            
            // Récupérer les paiements actuels
            $paiements = $this->paiements_mensuels ?? [];
            
            \Log::info("Paiements avant modification:", [$paiements]);
            
            // Créer la clé au format "année-mois"
            $cle = "{$annee}-{$mois}";
            
            \Log::info("Clé utilisée:", [$cle]);
            
            // Ajouter ou mettre à jour le paiement
            $paiements[$cle] = [
                'paye' => true,
                'date_paiement' => now()->toDateTimeString(),
                'montant' => $this->getCommissionMensuelle($annee, $mois),
                'paye_par' => auth()->id(),
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString()
            ];
            
            // Trier les clés pour une meilleure organisation
            ksort($paiements);
            
            \Log::info("Paiements après modification:", [$paiements]);
            
            // Sauvegarder
            $this->paiements_mensuels = $paiements;
            $success = $this->save();
            
            if ($success) {
                \Log::info("✅ Paiement marqué avec succès!");
                \Log::info("Base de données mise à jour:", [
                    'user_id' => $this->id,
                    'paiements_mensuels' => $this->paiements_mensuels
                ]);
            } else {
                \Log::error("❌ Échec de la sauvegarde du paiement");
            }
            
            return $success;

        } catch (\Exception $e) {
            \Log::error("❌ ERREUR marquerPaiementMensuel: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * CORRECTION COMPLÈTE : Annuler un paiement mensuel
     */
    public function annulerPaiementMensuel($annee, $mois)
    {
        try {
            \Log::info("=== DÉBUT annulerPaiementMensuel ===", [
                'user_id' => $this->id,
                'nom' => $this->nomComplet,
                'annee' => $annee,
                'mois' => $mois
            ]);
            
            // Récupérer les paiements actuels
            $paiements = $this->paiements_mensuels ?? [];
            
            \Log::info("Paiements avant annulation:", [$paiements]);
            
            // Créer la clé au format "année-mois"
            $cle = "{$annee}-{$mois}";
            
            \Log::info("Clé recherchée pour annulation:", [$cle]);
            
            if (isset($paiements[$cle])) {
                // Supprimer le paiement
                unset($paiements[$cle]);
                \Log::info("✅ Paiement trouvé et supprimé");
                
                // Sauvegarder les changements
                $this->paiements_mensuels = $paiements;
                $success = $this->save();
                
                if ($success) {
                    \Log::info("✅ Paiement annulé avec succès!");
                    \Log::info("Base de données mise à jour:", [
                        'user_id' => $this->id,
                        'paiements_mensuels' => $this->paiements_mensuels
                    ]);
                } else {
                    \Log::error("❌ Échec de la sauvegarde après annulation");
                }
                
                return $success;
            } else {
                \Log::warning("⚠️ Paiement non trouvé pour la clé: {$cle}");
                \Log::warning("Paiements actuels:", $paiements);
                return true; // Déjà annulé ou jamais payé
            }

        } catch (\Exception $e) {
            \Log::error("❌ ERREUR annulerPaiementMensuel: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * CORRECTION COMPLÈTE : Obtenir le statut d'un paiement mensuel
     */
    public function getPaiementMensuel($annee, $mois)
    {
        try {
            $paiements = $this->paiements_mensuels ?? [];
            
            $cle = "{$annee}-{$mois}";
            
            $estPaye = isset($paiements[$cle]) && 
                      isset($paiements[$cle]['paye']) && 
                      $paiements[$cle]['paye'] === true;
            
            $resultat = [
                'paye' => $estPaye,
                'date_paiement' => $estPaye ? ($paiements[$cle]['date_paiement'] ?? null) : null,
                'montant' => $estPaye ? ($paiements[$cle]['montant'] ?? 0) : 0,
                'paye_par' => $estPaye ? ($paiements[$cle]['paye_par'] ?? null) : null,
                'cle' => $cle,
                'annee' => $annee,
                'mois' => $mois,
                'mois_nom' => $this->getNomMois($mois),
                'user_id' => $this->id,
                'user_nom' => $this->nomComplet
            ];
            
            return $resultat;

        } catch (\Exception $e) {
            \Log::error("❌ ERREUR getPaiementMensuel: " . $e->getMessage());
            return [
                'paye' => false,
                'date_paiement' => null,
                'montant' => 0,
                'paye_par' => null,
                'cle' => "{$annee}-{$mois}",
                'annee' => $annee,
                'mois' => $mois,
                'mois_nom' => $this->getNomMois($mois),
                'user_id' => $this->id,
                'user_nom' => $this->nomComplet,
                'erreur' => $e->getMessage()
            ];
        }
    }

    /**
     * CORRECTION : Calculer la commission mensuelle
     */
    public function getCommissionMensuelle($annee, $mois)
    {
        try {
            $commission = MobileMoneyTransaction::where('user_id', $this->id)
                ->whereYear('created_at', $annee)
                ->whereMonth('created_at', $mois)
                ->sum('commission');
            
            return $commission ?? 0;
            
        } catch (\Exception $e) {
            \Log::error("Erreur calcul commission: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * CORRECTION : Vérifier si un mois est payé
     */
    public function estMoisPaye($annee, $mois)
    {
        $paiement = $this->getPaiementMensuel($annee, $mois);
        return $paiement['paye'] === true;
    }

    /**
     * Obtenir le nom du mois
     */
    private function getNomMois($mois)
    {
        $moisNoms = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];
        
        return $moisNoms[$mois] ?? "Mois $mois";
    }

    /**
     * Obtenir le statut des paiements pour une année
     */
    public function getStatutPaiementsAnnee($annee)
    {
        $paiements = $this->paiements_mensuels ?? [];
        $statut = [];
        
        for ($mois = 1; $mois <= 12; $mois++) {
            $statut[$mois] = $this->estMoisPaye($annee, $mois);
        }
        
        return $statut;
    }

    /**
     * Compter les mois payés pour une année
     */
    public function getNombreMoisPayes($annee)
    {
        $statut = $this->getStatutPaiementsAnnee($annee);
        return count(array_filter($statut));
    }

    /**
     * Vérifier si toutes les cases de l'année en cours sont cochées
     */
    public function estAnneeComplete($annee)
    {
        return $this->getNombreMoisPayes($annee) === 12;
    }

    /**
     * Récupérer tous les sub-users
     */
    public function getAllSubUsers()
    {
        return User::where('admin_id', $this->id)->get();
    }

    /**
     * Récupérer tous les IDs des sub-users
     */
    public function getSubUserIds()
    {
        return User::where('admin_id', $this->id)->pluck('id')->toArray();
    }

    /**
     * Récupérer tous les IDs de la hiérarchie
     */
    public function getHierarchyUserIds()
    {
        return array_merge([$this->id], $this->getSubUserIds());
    }

    /**
     * Récupérer les mobile caissiers assignés (pour commercial)
     */
    public function getMobileCaissiersAssignes()
    {
        if (!$this->isCommercial()) {
            return collect();
        }

        return User::where('commercial_id', $this->id)
                  ->where('fonction', self::FONCTION_MOBILE_CAISSIER)
                  ->get();
    }

    /**
     * Récupérer les comptes regroupés (pour grande caisse mobile)
     */
    public function getComptesRegroupes()
    {
        if (!$this->isGrandeCaisseMobile()) {
            return collect();
        }

        return User::where('grande_caisse_id', $this->id)
                  ->where('fonction', self::FONCTION_MOBILE_CAISSIER)
                  ->get();
    }

    /**
     * Scope pour les utilisateurs d'un admin spécifique
     */
    public function scopeForAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId)
                    ->orWhere('id', $adminId);
    }

    /**
     * Scope pour les utilisateurs visibles par l'utilisateur courant
     */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isAdmin()) {
            return $query->where(function($q) use ($user) {
                $q->where('id', $user->id)
                  ->orWhere('admin_id', $user->id);
            });
        }

        if ($user->isGerant() || $user->isManager() || $user->isMobileCaissier()) {
            return $query->where(function($q) use ($user) {
                $q->where('id', $user->id)
                  ->orWhere('admin_id', $user->admin_id);
            });
        }

        if ($user->isCommercial()) {
            return $query->where(function($q) use ($user) {
                $q->where('id', $user->id)
                  ->orWhere('commercial_id', $user->id);
            });
        }

        if ($user->isGrandeCaisseMobile()) {
            return $query->where(function($q) use ($user) {
                $q->where('id', $user->id)
                  ->orWhere('grande_caisse_id', $user->id);
            });
        }

        return $query->where('id', $user->id);
    }

    /**
     * Scope pour les utilisateurs actifs
     */
    public function scopeActif($query)
    {
        return $query->where('est_actif', true);
    }

    /**
     * Scope pour les utilisateurs inactifs
     */
    public function scopeInactif($query)
    {
        return $query->where('est_actif', false);
    }

    /**
     * Scope pour les mobile caissiers
     */
    public function scopeMobileCaissiers($query)
    {
        return $query->where('fonction', self::FONCTION_MOBILE_CAISSIER);
    }

    /**
     * Scope pour les commerciaux
     */
    public function scopeCommerciaux($query)
    {
        return $query->where('fonction', self::FONCTION_COMMERCIAL);
    }

    /**
     * Scope pour les grandes caisses mobile
     */
    public function scopeGrandesCaissesMobile($query)
    {
        return $query->where('fonction', self::FONCTION_GRANDE_CAISSE_MOBILE);
    }

    /**
     * Méthode pour récupérer les hôtesses selon les permissions
     */
    public function getAvailableHotesses()
    {
        if ($this->isSuperAdmin()) {
            return User::where('fonction', self::FONCTION_HOTESSE)
                      ->orderBy('prenom')
                      ->get();
        }

        if ($this->isAdmin()) {
            return User::where('fonction', self::FONCTION_HOTESSE)
                      ->where('admin_id', $this->id)
                      ->orderBy('prenom')
                      ->get();
        }

        if ($this->isGerant() || $this->isManager()) {
            return User::where('fonction', self::FONCTION_HOTESSE)
                      ->where('admin_id', $this->admin_id)
                      ->orderBy('prenom')
                      ->get();
        }

        return collect();
    }

    /**
     * Vérifier si l'utilisateur peut créer des observations
     */
    public function canCreateObservations()
    {
        return $this->isManager() || $this->isAdmin() || $this->isGerant();
    }

    /**
     * Récupérer les commandes visibles pour le manager
     */
    public function getCommandesForManager()
    {
        if (!$this->isManager()) {
            return collect();
        }

        return Commande::whereHas('hotesse', function($query) {
                $query->where('admin_id', $this->admin_id);
            })
            ->with(['table', 'hotesse', 'produits'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Activer l'utilisateur
     */
    public function activer()
    {
        $this->est_actif = true;
        return $this->save();
    }

    /**
     * Désactiver l'utilisateur
     */
    public function desactiver()
    {
        $this->est_actif = false;
        return $this->save();
    }

    /**
     * Vérifier si l'utilisateur est actif
     */
    public function getEstActifAttribute($value)
    {
        // Pour les rôles qui n'ont pas besoin de statut actif/inactif, toujours considérer comme actif
        if (in_array($this->fonction, [self::FONCTION_SUPER_ADMIN, self::FONCTION_ADMIN, self::FONCTION_GERANT, self::FONCTION_COMMERCIAL, self::FONCTION_GRANDE_CAISSE_MOBILE])) {
            return true;
        }

        return (bool) $value;
    }

    /**
     * Accesseurs
     */
    public function getNomCompletAttribute()
    {
        return $this->prenom . ' ' . $this->name;
    }

    public function getInitialesAttribute()
    {
        return strtoupper(substr($this->prenom, 0, 1) . substr($this->name, 0, 1));
    }

    public function getStatutFormattedAttribute()
    {
        return $this->est_actif ? 'Actif' : 'Inactif';
    }

    public function getFonctionFormattedAttribute()
    {
        $fonctions = [
            self::FONCTION_SUPER_ADMIN => 'Super Admin',
            self::FONCTION_ADMIN => 'Admin',
            self::FONCTION_GERANT => 'Gérant',
            self::FONCTION_CAISSIER => 'Caissier',
            self::FONCTION_HOTESSE => 'Hôtesse',
            self::FONCTION_MANAGER => 'Manager',
            self::FONCTION_MOBILE_CAISSIER => 'Caissier Mobile Money',
            self::FONCTION_COMMERCIAL => 'Commercial',
            self::FONCTION_GRANDE_CAISSE_MOBILE => 'Grande Caisse Mobile'
        ];

        return $fonctions[$this->fonction] ?? $this->fonction;
    }

    // Statut de vérification d'email
    public function getEstVerifieAttribute()
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * DEBUG : Méthode pour afficher tous les paiements
     */
    public function debugPaiements()
    {
        \Log::info("=== DEBUG PAIEMENTS POUR USER {$this->id} ===");
        \Log::info("Nom: {$this->nomComplet}");
        \Log::info("Fonction: {$this->fonction}");
        \Log::info("Paiements mensuels:", $this->paiements_mensuels ?? []);
        
        $paiements = $this->paiements_mensuels ?? [];
        if (empty($paiements)) {
            \Log::info("Aucun paiement enregistré");
        } else {
            foreach ($paiements as $cle => $details) {
                \Log::info("Clé: {$cle}, Détails:", $details);
            }
        }
        
        \Log::info("=== FIN DEBUG ===");
    }

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        // Définir la valeur par défaut pour est_actif
        static::creating(function ($user) {
            if (is_null($user->est_actif)) {
                // Par défaut, tous les utilisateurs sont actifs sauf indication contraire
                $user->est_actif = true;
            }
        });
    }
}