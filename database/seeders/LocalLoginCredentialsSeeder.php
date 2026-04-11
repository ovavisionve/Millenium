<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Millennium / Incapor — credenciales de prueba SOLO en entorno local
 *
 * Laravel guarda siempre bcrypt (`password` hasheado en `users`). Este seeder asigna texto
 * plano solo en memoria; el cast `hashed` del modelo User persiste el hash.
 *
 * Contraseña única opcional: definí `MILLENNIUM_LOCAL_DEMO_PASSWORD` en `.env` (ver `.env.example`)
 * para demo/video sin tocar constantes en código. Si está vacía, se usan las claves por email abajo.
 *
 * Uso (solo con APP_ENV=local):
 *   php artisan db:seed --class=LocalLoginCredentialsSeeder
 *
 * En producción NO ejecutar: aborta si no es `local`.
 */
class LocalLoginCredentialsSeeder extends Seeder
{
    /**
     * Valores por defecto cuando `MILLENNIUM_LOCAL_DEMO_PASSWORD` no está definida.
     * Para repositorios públicos, preferí solo `.env` local con la variable de entorno.
     *
     * @var array<string, string> email => contraseña
     */
    private const CREDENTIALS = [
        'admin@millennium.local' => 'Millennium-Admin-2026',
        'video@millennium.local' => 'Millennium-Video-2026',
        'invitado@millennium.local' => 'Millennium-Ventas-2026',
        'colaborador@millennium.local' => 'Millennium-Colab-2026',
    ];

    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->error('Abortado: este seeder solo puede ejecutarse con APP_ENV=local.');

            return;
        }

        $envPassword = config('millennium.local_demo_password');
        $useSingle = is_string($envPassword) && $envPassword !== '';

        $rows = [];

        foreach (self::CREDENTIALS as $email => $plainDefault) {
            $plain = $useSingle ? $envPassword : $plainDefault;

            $user = User::query()->where('email', $email)->first();
            if (! $user) {
                $rows[] = [$email, '(usuario no existe — crear primero o migrar)'];
                continue;
            }

            $user->password = $plain;
            $user->save();

            $rows[] = [$email, $plain];
        }

        $this->command?->newLine();
        if ($useSingle) {
            $this->command?->info('Modo: MILLENNIUM_LOCAL_DEMO_PASSWORD (misma clave para todos los usuarios listados).');
        }
        $this->command?->warn('LISTA PARA PRUEBAS EN LOCAL (no compartir en producción ni chats públicos):');
        $this->command?->table(['Correo', 'Contraseña'], $rows);
        $this->command?->info('Podés iniciar sesión en /login y grabar pantalla. Las contraseñas quedan hasheadas en la BD.');
    }
}
