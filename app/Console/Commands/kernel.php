<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Remise à zéro des commissions le 1er du mois à 00:01
        $schedule->command('commissions:reset')->monthlyOn(1, '00:01');
         // Archive les audits tous les jours à 01h du matin
        $schedule->command('audit:archive')->dailyAt('01:00');
        // Vous pouvez ajouter d'autres tâches planifiées ici
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
    
}