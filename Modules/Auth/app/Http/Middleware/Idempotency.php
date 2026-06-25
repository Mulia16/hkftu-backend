<?php

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class Idempotency
{
    private const CACHE_PREFIX = 'idempotency:';

    private const TTL_SECONDS = 86400;

    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('Idempotency-Key');

        if (! $key) {
            return $next($request);
        }

        $cacheKey = self::CACHE_PREFIX.$key;
        $cached = Cache::get($cacheKey);

        if ($cached) {
            return response()->json(
                $cached['body'],
                $cached['status'],
                array_merge($cached['headers'], ['X-Idempotent-Replay' => 'true']),
            );
        }

        /** @var Response $response */
        $response = $next($request);

        if ($response->isSuccessful()) {
            Cache::put($cacheKey, [
                'status' => $response->getStatusCode(),
                'body' => json_decode($response->getContent(), true),
                'headers' => ['Content-Type' => 'application/json'],
            ], self::TTL_SECONDS);
        }

        return $response;
    }
}
