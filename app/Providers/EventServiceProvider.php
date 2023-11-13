<?php

namespace App\Providers;

use App\Events\NotificacionNuevoVentaCobro;
use App\Events\NuevoRegistroSopEsp;
use App\Events\RegistrarCobro;
use App\Listeners\ListenerRegistroCobro;
use App\Listeners\NotificacionVentaCobroListener;
use App\Listeners\NotificarRgistroSopEspListner;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        NuevoRegistroSopEsp::class => [
            NotificarRgistroSopEspListner::class,
        ],
        RegistrarCobro::class =>[
            ListenerRegistroCobro::class,
        ],
        NotificacionNuevoVentaCobro::class => [
            NotificacionVentaCobroListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
