<?php
// app/Http/Middleware/ThemeMiddleware.php

namespace App\Http\Middleware;

use App\Models\UserThemeSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ThemeMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            try {
                // Charger les couleurs
                $themeColors = UserThemeSetting::getColorsForUser(auth()->id());
                
                // SUPPRIMER cette ligne:
                // \Log::info('Couleurs thème chargées pour user ' . auth()->id() . ':', $themeColors);
                
            } catch (\Exception $e) {
                // SUPPRIMER cette ligne:
                // \Log::error('Erreur chargement thème: ' . $e->getMessage());
                $themeColors = [
                    'navbar_bg' => '0b5f37',
                    'footer_bg' => '0b5f37', 
                    'primary_text' => 'ffffff',
                    'hover_color' => 'ee8f13'
                ];
            }
        } else {
            // Thème par défaut pour les non-authentifiés
            $themeColors = [
                'navbar_bg' => '0b5f37',
                'footer_bg' => '0b5f37',
                'primary_text' => 'ffffff',
                'hover_color' => 'ee8f13'
            ];
        }
        
        View::share('themeColors', $themeColors);
        
        return $next($request);
    }
}