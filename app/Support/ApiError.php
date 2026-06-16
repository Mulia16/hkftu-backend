<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ApiError
{
    public static function respond(
        string $code,
        string $message,
        int $status = 500,
        array $details = [],
        array $fieldErrors = [],
    ): JsonResponse {
        return response()->json([
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
                'traceId' => 'req_'.(string) Str::ulid(),
                'fieldErrors' => $fieldErrors,
            ],
        ], $status);
    }
}
