<?php

namespace App\Traits;

use App\Models\Audit;
use Illuminate\Support\Facades\Log;

trait Auditable
{
    /**
     * Boot the trait
     */
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            self::handleAudit('created', $model, null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            
            // Exclure les timestamps des changements significatifs
            unset($changes['updated_at']);
            
            if (!empty($changes)) {
                self::handleAudit('updated', $model, $model->getOriginal(), $changes);
            }
        });

        static::deleted(function ($model) {
            self::handleAudit('deleted', $model, $model->getAttributes(), null);
        });
    }

    /**
     * Gérer la création d'audit
     */
    private static function handleAudit($action, $model, $oldData, $newData)
    {
        try {
            // Vérifier l'authentification
            if (!auth()->check()) {
                Log::warning("Audit skipped: No user authenticated for {$action}");
                return;
            }

            $tableName = $model->getTable();
            $recordId = $model->id;
            
            $description = self::generateDescription($action, $tableName, $model);
            
            // Logger l'audit
            $audit = Audit::log($action, $tableName, $recordId, $description, $oldData, $newData);
            
            if ($audit) {
                Log::info("✅ Audit SUCCESS: {$action} on {$tableName} ID: {$recordId}");
            } else {
                Log::error("❌ Audit FAILED: {$action} on {$tableName} ID: {$recordId}");
            }
            
        } catch (\Exception $e) {
            Log::error("Auditable trait error: " . $e->getMessage());
        }
    }

    /**
     * Générer la description de l'audit
     */
    private static function generateDescription($action, $tableName, $model)
    {
        $descriptions = [
            'created' => "Création dans {$tableName}",
            'updated' => "Modification dans {$tableName}",
            'deleted' => "Suppression dans {$tableName}"
        ];

        $baseDescription = $descriptions[$action] ?? "Action {$action} sur {$tableName}";
        
        // Ajouter des détails spécifiques au modèle
        if (method_exists($model, 'getDesignation')) {
            return $baseDescription . " - " . $model->getDesignation();
        }
        
        if (isset($model->designation)) {
            return $baseDescription . " - " . $model->designation;
        }
        
        if (isset($model->name)) {
            return $baseDescription . " - " . $model->name;
        }

        return $baseDescription;
    }
}