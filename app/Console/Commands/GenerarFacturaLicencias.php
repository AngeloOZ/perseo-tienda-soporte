<?php

namespace App\Console\Commands;

use App\Http\Controllers\FacturasLicenciasRenovarController;
use Illuminate\Console\Command;

class GenerarFacturaLicencias extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'renovar:factura_licencias';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Este comando consulta las licencias que estan por vencer a 5 días y genera la factura de forma auyomatica para su renovacion ';

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
        $renovador = new FacturasLicenciasRenovarController();
        $numeroFacturas = $renovador->generar_facturas_renovacion();
        return $numeroFacturas;
    }
}
