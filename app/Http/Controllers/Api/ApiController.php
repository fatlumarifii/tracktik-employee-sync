<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

abstract class ApiController extends Controller
{
    protected function jsonResponse(
        string $message,
        mixed $data = null,
        ?string $error = null,
        int $statusCode = 200
    ): JsonResponse {
        $response = [
            'success' => $error === null,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($error !== null) {
            $response['error'] = $error;
        }

        return response()->json($response, $statusCode);
    }
}
