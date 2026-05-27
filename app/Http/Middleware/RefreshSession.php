<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RefreshSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // RAFRAÎCHISSEMENT AUTOMATIQUE pour éviter les 419
        if ($request->user()) {
            $lastActivity = $request->session()->get('last_activity');
            $currentTime = time();
            
            // Rafraîchir la session toutes les 30 minutes
            if (!$lastActivity || ($currentTime - $lastActivity) > 1800) {
                $request->session()->put('last_activity', $currentTime);
            }
        }

        return $next($request);
    }
}