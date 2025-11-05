<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

// --- AÑADE TU NUEVA IMPORTACIÓN AQUÍ ---
use App\Jobs\ConciliarLibelula;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // ESTO SE QUEDA IGUAL (TU INTEGRACIÓN DE BINANCE)
        \App\Console\Commands\BinanceStreamCommand::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        // --- AÑADE LA LÍNEA DE PROGRAMACIÓN DEL JOB AQUÍ ---
        $schedule->job(new ConciliarLibelula)
                 ->everyFifteenMinutes() 
                 ->withoutOverlapping();
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