<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       // Le indicamos a Laravel cómo construir la URL para el correo de recuperación
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            // Asegúrate de tener FRONTEND_URL=https://defensa.johnbecerra.dev en tu archivo .env
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            
            return $frontendUrl . "/restablecer-password?token={$token}&email={$notifiable->getEmailForPasswordReset()}";
        });

        // Esta es la "llave maestra" que ignora los permisos granulares si eres super-admin
        Gate::before(function (User $user, string $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });        
    }
}
