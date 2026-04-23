<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatAssistantController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:8000'],
            'page_route' => ['nullable', 'string', 'max:255'],
        ]);

        $apiKey = (string) config('services.openrouter.key');
        if ($apiKey === '') {
            return response()->json([
                'error' => 'OpenRouter no está configurado. Definí OPENROUTER_API_KEY en .env.',
            ], 503);
        }

        $baseUrl = (string) config('services.openrouter.base_url');
        $url = $baseUrl.'/chat/completions';
        $model = (string) config('services.openrouter.model', 'openai/gpt-4o-mini');

        $system = $this->systemPrompt($validated['page_route'] ?? null);

        $response = Http::timeout(90)
            ->withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => (string) config('app.url'),
                'X-Title' => (string) config('app.name', 'Millennium'),
            ])
            ->post($url, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $validated['message']],
                ],
                'max_tokens' => 1024,
            ]);

        if (! $response->successful()) {
            Log::warning('openrouter.chat.error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json([
                'error' => 'No se pudo obtener respuesta del asistente. Revisá créditos en OpenRouter o intentá más tarde.',
            ], 502);
        }

        $data = $response->json();
        $text = data_get($data, 'choices.0.message.content');
        if (! is_string($text) || $text === '') {
            return response()->json(['error' => 'Respuesta vacía del servicio.'], 502);
        }

        return response()->json(['reply' => $text]);
    }

    protected function systemPrompt(?string $pageRoute): string
    {
        $ctx = $pageRoute
            ? " Pantalla actual del usuario (nombre de ruta interna): {$pageRoute}."
            : '';

        return <<<PROMPT
Sos Ovi, asistente del sistema Millennium (marca Incapor) para usuarios internos. Respondés solo en español, de forma breve y clara.

Ámbito: ayuda sobre el uso del sistema — Inicio y guía, Datos maestros (clientes, categorías/líneas, bancos, vendedores), Facturación (facturas vigentes/canceladas, nueva factura, registrar pago), Cobranza por cliente, Reportes con filtros, Estados de cuenta, Usuarios (según permisos).

Reglas:
- No inventes montos en USD, cantidades de facturas ni saldos de clientes: el sistema es la fuente de verdad. Si preguntan números concretos, indicá en qué pantalla o menú pueden verlos (ej. Facturación → Cobranza, Reportes, tablas de facturas).
- No des consejos legales ni fiscales; solo orientación sobre pantallas y flujos del sistema.
- Si no sabés algo específico de su instalación, decilo y sugerí el menú más probable.
{$ctx}
PROMPT;
    }
}
