<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Same response headers as Next's `next.config` and `proxy.ts`: a
 * Content-Security-Policy plus the usual clickjacking / sniffing / referrer
 * hardening.
 *
 * script-src keeps 'unsafe-inline' on purpose: the pages use inline handlers
 * and inline scripts that PJAX re-executes from other responses, and the
 * `footer_content` setting holds admin-supplied markup. A per-request nonce
 * would break all of those. There is no 'unsafe-eval'. Everything else
 * (framing, plugins, base, form targets, connections) is locked down.
 */
class SecurityHeaders
{
    private const CDNS = 'https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://cdn.datatables.net';

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $headers = [
            'Content-Security-Policy' => $this->csp(),
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
        ];

        if ($request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=63072000; includeSubDomains';
        }

        foreach ($headers as $name => $value) {
            // A route may have set its own (e.g. a download); never overwrite it.
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }

    private function csp(): string
    {
        $vite = $this->viteDevOrigin();
        $cdns = self::CDNS;
        $fileOrigin = trim($this->originOf((string) config('filesystems.disks.s3.url')).' '.$this->originOf((string) config('files.imgproxy.url')));

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://www.google.com https://www.gstatic.com {$cdns} {$vite}",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com {$cdns} {$vite}",
            "img-src 'self' data: blob: {$fileOrigin}",
            "font-src 'self' data: https://fonts.gstatic.com {$cdns}",
            "connect-src 'self' {$vite}",
            'frame-src https://www.google.com',
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ]);
    }

    /** The Vite dev server (`npm run dev`) serves assets from another origin while `public/hot` exists. */
    private function viteDevOrigin(): string
    {
        $hot = public_path('hot');

        return is_file($hot) ? (string) $this->originOf(trim((string) file_get_contents($hot))) : '';
    }

    private function originOf(string $url): string
    {
        $parts = parse_url($url);

        return isset($parts['scheme'], $parts['host'])
            ? $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '')
            : '';
    }
}
