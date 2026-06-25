<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\Auth\Http\Middleware\Idempotency;
use Tests\TestCase;

uses(TestCase::class);

it('passes through when no idempotency key header', function () {
    $middleware = new Idempotency;
    $request = Request::create('/test', 'POST');
    $next = fn (Request $req) => response('ok', 200);

    $response = $middleware->handle($request, $next);

    expect($response->getStatusCode())->toBe(200);
});

it('returns cached response when idempotency key exists', function () {
    Cache::shouldReceive('get')
        ->once()
        ->with('idempotency:replay-key')
        ->andReturn([
            'status' => 201,
            'body' => ['id' => 1, 'name' => 'test'],
            'headers' => ['Content-Type' => 'application/json'],
        ]);

    $middleware = new Idempotency;
    $request = Request::create('/test', 'POST');
    $request->headers->set('Idempotency-Key', 'replay-key');

    $next = fn (Request $req) => response('should not reach', 200);
    $response = $middleware->handle($request, $next);

    expect($response->getStatusCode())->toBe(201);
    expect($response->headers->get('X-Idempotent-Replay'))->toBe('true');
});
