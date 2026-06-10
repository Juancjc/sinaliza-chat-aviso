<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreserveRequestOrigin
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $location = $response->headers->get('Location');

        if (! $response->isRedirection() || ! is_string($location) || str_starts_with($location, '/')) {
            return $response;
        }

        $target = parse_url($location);
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $internalHosts = array_filter([
            $request->getHost(),
            is_string($appHost) ? $appHost : null,
        ]);

        if (
            ! is_array($target)
            || ! isset($target['host'])
            || ! in_array(strtolower($target['host']), array_map('strtolower', $internalHosts), true)
        ) {
            return $response;
        }

        $relativeLocation = $target['path'] ?? '/';

        if (isset($target['query'])) {
            $relativeLocation .= '?'.$target['query'];
        }

        if (isset($target['fragment'])) {
            $relativeLocation .= '#'.$target['fragment'];
        }

        $response->headers->set('Location', $relativeLocation);

        return $response;
    }
}
