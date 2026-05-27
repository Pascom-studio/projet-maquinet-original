<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Illuminate\Console\Scheduling\Schedule $schedule): void {
        // Remise à zéro des commissions le 1er du mois à 00:01
        $schedule->command('commissions:reset')
                 ->monthlyOn(1, '00:01')
                 ->timezone('Africa/Ouagadougou')
                 ->description('Remise à zéro mensuelle des commissions Mobile Money');
    })
    ->create();