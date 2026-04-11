<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas de caja negra — login (solo comportamiento observable, sin asumir implementación interna).
 *
 * Qué cubren:
 * - Pantalla de login accesible.
 * - Validación de campos obligatorios y formato de correo.
 * - Límite de longitud (coherente con columna BD y mitigación de payloads enormes).
 * - Credenciales incorrectas: mensaje genérico (no enumerar si el correo existe).
 * - Cuenta desactivada.
 * - Muchos intentos fallidos: limitación de tasa (mensaje con tiempo de espera).
 *
 * Ejecutar: php artisan test --filter=LoginBlackBoxTest
 */
class LoginBlackBoxTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('Correo electrónico', false);
        $response->assertSee('Contraseña', false);
    }

    public function test_empty_submission_shows_validation_errors(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    public function test_invalid_email_format_is_rejected(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'esto-no-es-un-correo',
            'password' => 'cualquier-cosa',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_email_exceeding_max_length_is_rejected(): void
    {
        $longLocal = str_repeat('a', 250).'@x.com'; // > 255 chars total
        $this->assertGreaterThan(255, strlen($longLocal));

        $response = $this->from('/login')->post('/login', [
            'email' => $longLocal,
            'password' => 'secret',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_password_exceeding_max_length_is_rejected(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'user@example.com',
            'password' => str_repeat('x', 256),
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_wrong_password_shows_generic_failure_message(): void
    {
        $user = User::factory()->create([
            'email' => 'known@example.com',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'definitivamente-incorrecta',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        $errors = session('errors');
        $this->assertNotNull($errors);
        $msg = $errors->first('email');
        $this->assertStringContainsStringIgnoringCase('credenciales', $msg);
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->create([
            'email' => 'inactivo@example.com',
            'is_active' => false,
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        $msg = session('errors')->first('email');
        $this->assertStringContainsStringIgnoringCase('desactivada', $msg);
    }

    public function test_successful_login_redirects_to_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'ok@example.com',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_repeated_failed_attempts_trigger_rate_limit_message(): void
    {
        User::factory()->create(['email' => 'rate@example.com']);

        for ($i = 0; $i < 5; $i++) {
            $this->from('/login')->post('/login', [
                'email' => 'rate@example.com',
                'password' => 'mala',
            ]);
        }

        $response = $this->from('/login')->post('/login', [
            'email' => 'rate@example.com',
            'password' => 'mala',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        $msg = session('errors')->first('email');
        $this->assertStringContainsStringIgnoringCase('intentos', $msg);
    }
}
