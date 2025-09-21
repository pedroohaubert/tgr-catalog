<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use LogicException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Throwable;

class ApiController extends Controller
{
    protected function jsonSuccess(mixed $data = null, ?string $message = null, int $status = 200): JsonResponse
    {
        $payload = ['ok' => true];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        if ($message !== null) {
            $payload['message'] = $message;
        }

        return response()->json($payload, $status);
    }

    protected function jsonError(string $code, string $message, mixed $details = null, int $status = 400): JsonResponse
    {
        $payload = [
            'ok' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ];

        if ($details !== null) {
            $payload['error']['details'] = $details;
        }

        return response()->json($payload, $status);
    }

    /**
     * Map domain exceptions to consistent JSON responses.
     */
    protected function handleDomainException(Throwable $e): JsonResponse
    {
        if ($e instanceof ConflictHttpException) {
            return $this->jsonError('conflict', $e->getMessage(), null, 409);
        }

        if ($e instanceof LogicException) {
            return $this->jsonError('invalid_state', $e->getMessage(), null, 409);
        }

        if ($e instanceof RuntimeException) {
            return $this->jsonError('conflict', $e->getMessage(), null, 409);
        }

        if ($e instanceof InvalidArgumentException) {
            return $this->jsonError('invalid_argument', $e->getMessage(), null, 400);
        }

        return $this->jsonError('server_error', 'Ocorreu um erro inesperado.', null, 500);
    }
}
