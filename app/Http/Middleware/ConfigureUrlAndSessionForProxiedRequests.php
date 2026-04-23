<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tras TrustProxies (global), alinear raíz de URL y cookie de sesión con el Host/esquema reales.
 * Evita 419 (CSRF) detrás de Cloudflare Tunnel u otros proxies HTTPS→HTTP al origen.
 */
class ConfigureUrlAndSessionForProxiedRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $forwardedHost = $request->headers->get('x-forwarded-host');
        $forwardedProto = $request->headers->get('x-forwarded-proto');

        // Solo forzamos la raíz cuando realmente viene detrás de un proxy/túnel.
        // En tests o requests locales, mutar globalmente el UrlGenerator termina
        // contaminando requests siguientes (por ejemplo: http://localhost/localhost/login).
        if ($forwardedHost || $forwardedProto) {
            URL::forceRootUrl($request->getSchemeAndHttpHost());
        } else {
            URL::forceRootUrl(null);
        }

        config(['session.secure' => $request->secure()]);

        return $next($request);
    }
}
