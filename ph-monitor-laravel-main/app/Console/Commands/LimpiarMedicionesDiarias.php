<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LimpiarMedicionesDiarias extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mediciones:limpiar-diarias';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina todas las mediciones registradas al inicio de un nuevo día';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $cantidadEliminada = DB::table('mediciones')->delete();

            $this->info("✓ Se eliminaron {$cantidadEliminada} mediciones correctamente.");
            $this->info('Nuevo día iniciado con mediciones limpias.');

            return 0;
        } catch (\Exception $e) {
            $this->error('Error al eliminar las mediciones: ' . $e->getMessage());
            return 1;
        }
    }
}
