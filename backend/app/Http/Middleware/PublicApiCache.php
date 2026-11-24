<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cache headers for the public read API.
 * Successful responses get a short public cache and an ETag (304 when unchanged).
 * Errors, such as a 404 for a draft that is published a moment later, are never cached.
 */
class PublicApiCache
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethodCacheable()) {
            return $response;
        }

        if (! $response->isSuccessful()) {
            $response->headers->set('Cache-Control', 'no-store');

            return $response;
        }

        $response->setPublic();
        $response->setMaxAge(60);
        $response->setEtag(md5((string) $response->getContent()));
        $response->isNotModified($request);

        return $response;
    }
}
