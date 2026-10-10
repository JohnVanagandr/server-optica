<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    protected string $token;

    /**
     * Inyectar el token generado por el Broker de Laravel
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Construir el cuerpo del correo con la URL del Frontend
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Se extrae la URL del frontend desde el archivo .env (ej: http://localhost:5173 o https://mioptica.com)
        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
        
        // Construimos la ruta exacta que el router de React debe capturar
        $url = $frontendUrl . '/reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->email);

        return (new MailMessage)
            ->subject('Recuperación de Contraseña - Óptica SaaS')
            ->greeting('Hola, ' . $notifiable->name)
            ->line('Recibiste este correo porque solicitaste restablecer la contraseña de tu cuenta en nuestra plataforma.')
            ->action('Restablecer Contraseña', $url)
            ->line('Este enlace de recuperación expirará en ' . config('auth.passwords.users.expire') . ' minutos.')
            ->line('Si no realizaste esta solicitud, puedes ignorar este mensaje de forma segura.')
            ->salutation('Saludos, El equipo de Soporte Técnico.');
    }
}