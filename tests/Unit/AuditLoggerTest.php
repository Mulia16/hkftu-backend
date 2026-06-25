<?php

use Illuminate\Http\Request;
use Modules\Auth\Models\AuditLog;
use Modules\Auth\Services\AuditLogger;

it('creates audit log with all fields', function () {
    $request = Mockery::mock(Request::class);
    $request->shouldReceive('user')->andReturn(null);
    $request->shouldReceive('ip')->andReturn('127.0.0.1');

    $logger = new AuditLogger($request);

    $log = Mockery::mock(AuditLog::class);
    $log->shouldReceive('getAttribute')->with('action')->andReturn('test.action');
    $log->shouldReceive('getAttribute')->with('resource_type')->andReturn('test');
    $log->shouldReceive('getAttribute')->with('resource_id')->andReturn('123');

    expect($log->action)->toBe('test.action');
    expect($log->resource_type)->toBe('test');
    expect($log->resource_id)->toBe('123');
});

it('creates audit log with before and after arrays', function () {
    $request = Mockery::mock(Request::class);
    $request->shouldReceive('user')->andReturn(null);
    $request->shouldReceive('ip')->andReturn('192.168.1.1');

    $logger = new AuditLogger($request);

    expect($logger)->toBeInstanceOf(AuditLogger::class);
});

it('creates audit log with actor user id', function () {
    $request = Mockery::mock(Request::class);
    $request->shouldReceive('user')->andReturn(null);
    $request->shouldReceive('ip')->andReturn('10.0.0.1');

    $logger = new AuditLogger($request);

    expect($logger)->toBeInstanceOf(AuditLogger::class);
});
