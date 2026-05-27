<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Audit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'table_name', 
        'record_id',
        'old_data',
        'new_data',
        'description'
    ];

    // SUPPRIMEZ les casts car les colonnes sont en TEXT, pas JSON
    // protected $casts = [
    //     'old_data' => 'array',
    //     'new_data' => 'array',
    // ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Méthode pour logger les audits
     */
    public static function log($action, $tableName, $recordId, $description, $oldData = null, $newData = null)
    {
        try {
            // Vérifier que l'utilisateur est authentifié
            if (!auth()->check()) {
                Log::warning("Audit skipped: No authenticated user");
                return null;
            }

            // Convertir les données en JSON pour le stockage TEXT
            $oldDataJson = $oldData ? json_encode($oldData, JSON_PRETTY_PRINT) : null;
            $newDataJson = $newData ? json_encode($newData, JSON_PRETTY_PRINT) : null;

            // Créer l'audit
            $audit = self::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'table_name' => $tableName,
                'record_id' => $recordId,
                'description' => $description,
                'old_data' => $oldDataJson,
                'new_data' => $newDataJson
            ]);

            Log::info("Audit created: {$action} on {$tableName} ID: {$recordId}");

            return $audit;

        } catch (\Exception $e) {
            Log::error("Audit failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Accesseurs pour convertir le TEXT en array à la volée
     */
    public function getOldDataAttribute($value)
    {
        if ($value && is_string($value)) {
            return json_decode($value, true);
        }
        return $value;
    }

    public function getNewDataAttribute($value)
    {
        if ($value && is_string($value)) {
            return json_decode($value, true);
        }
        return $value;
    }

    /**
     * Scope pour filtrer les audits visibles selon les permissions
     */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isAdmin()) {
            // Admin voit ses audits + ceux de ses sub-users
            return $query->where('user_id', $user->id)
                        ->orWhereHas('user', function($q) use ($user) {
                            $q->where('admin_id', $user->id);
                        });
        }

        if ($user->isGerant()) {
            // Gérant voit les audits de son admin parent
            return $query->whereHas('user', function($q) use ($user) {
                $q->where('admin_id', $user->admin_id);
            });
        }

        // Par défaut : seulement ses propres audits
        return $query->where('user_id', $user->id);
    }

    /**
     * Scopes utiles
     */
    public function scopeForTable($query, $tableName)
    {
        return $query->where('table_name', $tableName);
    }

    public function scopeForAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Vérifie si l'utilisateur peut voir cet audit
     */
    public function canBeViewedBy(User $user)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            // Admin peut voir ses audits + ceux de ses sub-users
            return $this->user_id === $user->id || 
                   ($this->user && $this->user->admin_id === $user->id);
        }

        if ($user->isGerant()) {
            // Gérant peut voir les audits de son admin parent
            return $this->user && $this->user->admin_id === $user->admin_id;
        }

        // Caissiers ne peuvent voir que leurs propres audits
        return $this->user_id === $user->id;
    }

    /**
     * Accesseurs pour l'affichage
     */
    public function getActionLabelAttribute()
    {
        $labels = [
            'created' => 'Création',
            'updated' => 'Modification',
            'deleted' => 'Suppression'
        ];
        
        return $labels[$this->action] ?? $this->action;
    }

    public function getActionColorAttribute()
    {
        $colors = [
            'created' => 'green',
            'updated' => 'blue', 
            'deleted' => 'red'
        ];
        
        return $colors[$this->action] ?? 'gray';
    }

    /**
     * Formater les données pour l'affichage
     */
    public function getFormattedOldDataAttribute()
    {
        if (!$this->old_data) return null;
        
        return json_encode($this->old_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function getFormattedNewDataAttribute()
    {
        if (!$this->new_data) return null;
        
        return json_encode($this->new_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Récupère les audits pour un enregistrement spécifique
     */
    public static function getForRecord($tableName, $recordId)
    {
        return self::where('table_name', $tableName)
                  ->where('record_id', $recordId)
                  ->orderBy('created_at', 'desc')
                  ->get();
    }

    /**
     * Récupère les audits récents pour un utilisateur
     */
    public static function getRecentForUser(User $user, $limit = 10)
    {
        return self::visibleTo($user)
                  ->with('user')
                  ->orderBy('created_at', 'desc')
                  ->limit($limit)
                  ->get();
    }

    /**
     * Statistiques d'audit pour un utilisateur
     */
    public static function getStatsForUser(User $user, $days = 30)
    {
        $query = self::visibleTo($user)
                    ->where('created_at', '>=', now()->subDays($days));

        return [
            'total' => $query->count(),
            'created' => $query->where('action', 'created')->count(),
            'updated' => $query->where('action', 'updated')->count(),
            'deleted' => $query->where('action', 'deleted')->count(),
            'top_tables' => $query->groupBy('table_name')
                                ->selectRaw('table_name, COUNT(*) as count')
                                ->orderBy('count', 'desc')
                                ->limit(5)
                                ->get()
                                ->pluck('count', 'table_name')
        ];
    }

    /**
     * Nettoyer les anciens audits (pour les tâches de maintenance)
     */
    public static function cleanupOldRecords($days = 365)
    {
        $deleted = self::where('created_at', '<', now()->subDays($days))->delete();
        Log::info("Audit cleanup: {$deleted} old records deleted");
        return $deleted;
    }
    /** Audit archives.pascal */

    public static function archiveOldRecords($days = 365)
{
    $limitDate = now()->subDays($days);

    // Récupérer les audits à archiver
    $oldRecords = self::where('created_at', '<', $limitDate)->get();

    if ($oldRecords->isEmpty()) {
        return 0;
    }

    foreach ($oldRecords as $audit) {
        AuditArchive::create($audit->toArray());
    }

    // Supprimer ensuite
    $deleted = self::where('created_at', '<', $limitDate)->delete();

    \Log::info("Audit Archive: {$deleted} records archived & deleted");

    return $deleted;
}
    /**
     * Représentation string de l'audit
     */
    public function __toString()
    {
        return "Audit [{$this->action}] on {$this->table_name} by {$this->user->prenom} {$this->user->nom}";
    }
}