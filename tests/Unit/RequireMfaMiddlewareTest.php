<?php

use Illuminate\Http\Request;
use Modules\Auth\Http\Middleware\RequireMfa;
use Tests\TestCase;

uses(TestCase::class);

it('returns 401 when user is not authenticated', function () {
    $middleware = new RequireMfa;
    $request = Request::create('/test', 'GET');

    $next = fn ($req) => response('ok', 200);
    $response = $middleware->handle($request, $next);

    expect($response->getStatusCode())->toBe(401);
});
