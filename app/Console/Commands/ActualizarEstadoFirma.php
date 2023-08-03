<?php

namespace App\Console\Commands;

use App\Http\Controllers\FirmaController;
use App\Models\Firma;
use Illuminate\Console\Command;

class ActualizarEstadoFirma extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'actualizar-estado-firma:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza el estado de las firmas enviadas a Uanataca y las pone en enviado al correo en caso de estar aprobadasfir';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $firmas = Firma::whereBetween('estado', [3, 4])
            ->whereNotNull('uanatacaid')
            ->limit(350)
            ->get();

        foreach ($firmas as $key => $firma) {
            $res = FirmaController::consultarEstado($firma);
        }

        return 0;
    }
}
