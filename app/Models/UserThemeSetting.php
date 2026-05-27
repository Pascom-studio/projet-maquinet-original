<?php
// app/Models/UserThemeSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserThemeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'navbar_bg',
        'footer_bg', 
        'primary_text',
        'hover_color'
    ];

    /**
     * Récupère les couleurs pour un utilisateur
     * Si l'utilisateur n'a pas de thème personnalisé, utilise le thème de son admin
     */
    public static function getColorsForUser($userId)
    {
        // Chercher d'abord le thème de l'utilisateur
        $userTheme = self::where('user_id', $userId)->first();
        
        if ($userTheme) {
            return [
                'navbar_bg' => $userTheme->navbar_bg,
                'footer_bg' => $userTheme->footer_bg,
                'primary_text' => $userTheme->primary_text,
                'hover_color' => $userTheme->hover_color
            ];
        }

        // Si l'utilisateur n'a pas de thème personnalisé, chercher le thème de son admin
        $user = User::find($userId);
        if ($user && $user->admin_id) {
            $adminTheme = self::where('user_id', $user->admin_id)->first();
            if ($adminTheme) {
                return [
                    'navbar_bg' => $adminTheme->navbar_bg,
                    'footer_bg' => $adminTheme->footer_bg,
                    'primary_text' => $adminTheme->primary_text,
                    'hover_color' => $adminTheme->hover_color
                ];
            }
        }

        // Retourner les valeurs par défaut
        return [
            'navbar_bg' => '0b5f37',
            'footer_bg' => '0b5f37',
            'primary_text' => 'ffffff',
            'hover_color' => 'ee8f13'
        ];
    }

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}