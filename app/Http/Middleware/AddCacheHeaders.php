<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddCacheHeaders
{
    /**
     * Handle an incoming request and add appropriate cache headers.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $type = 'default'): Response
    {
        $response = $next($request);

        // Don't cache if there's an error
        if ($response->getStatusCode() >= 400) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
            return $response;
        }

        // Don't cache authenticated responses by default
        if (auth()->check() && $type === 'default') {
            $response->headers->set('Cache-Control', 'private, no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            return $response;
        }

        switch ($type) {
            case 'static':
                // Static assets: cache for 1 year with immutable flag
                $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
                $response->headers->set('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));
                return $response;

            case 'public-short':
                // Public resources: cache for 5 minutes
                $response->headers->set('Cache-Control', 'public, max-age=300, s-maxage=300');
                $response->headers->set('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 300));
                $response->headers->set('Vary', 'Accept-Encoding');
                return $response;

            case 'public-medium':
                // Public resources: cache for 1 hour
                $response->headers->set('Cache-Control', 'public, max-age=3600, s-maxage=3600');
                $response->headers->set('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
                $response->headers->set('Vary', 'Accept-Encoding');
                return $response;

            case 'private':
                // Private resources: cache for 5 minutes on client only
                $response->headers->set('Cache-Control', 'private, max-age=300, must-revalidate');
                $response->headers->set('Vary', 'Accept-Encoding');
                return $response;

            case 'api':
                // API responses: cache for 1 minute with revalidation
                $etag = md5($response->getContent());

                if ($request->header('If-None-Match') === $etag) {
                    return response('', 304)
                        ->header('Cache-Control', 'private, max-age=60, must-revalidate')
                        ->header('ETag', $etag);
                }

                $response->headers->set('Cache-Control', 'private, max-age=60, must-revalidate');
                $response->headers->set('ETag', $etag);
                $response->headers->set('Vary', 'Accept-Encoding, Authorization');
                return $response;

            case 'no-cache':
                // No caching
                $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
                $response->headers->set('Pragma', 'no-cache');
                $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
                return $response;

            default:
                // Default: minimal caching
                $response->headers->set('Cache-Control', 'private, max-age=60');
                return $response;
        }
    }
}
