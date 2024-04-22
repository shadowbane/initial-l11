<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

/**
 * Trait ApiFormatter.
 */
trait ApiFormatter
{
    /**
     * @param  array|Collection  $data
     * @return JsonResponse
     */
    public function sendResponse(array|Collection $data): JsonResponse
    {
        $return = [
            'success' => true,
            'data' => $this->setData($data),
        ];

        $this->storeAuditRequest($return);

        return response()
            ->json($return, 200)
            ->header('Content-Type', 'application/json');
    }

    /**
     * @param  string  $message
     * @param  Throwable  $th
     * @return JsonResponse
     */
    private function logAndSendErrorResponse(string $message, Throwable $th): JsonResponse
    {
        Log::error($message, [
            'exception' => $th,
            'message' => $th->getMessage(),
            'code' => $th->getCode(),
            'trace' => $th->getTraceAsString(),
        ]);

        $defaultStatusCode = 500;
        if ($th instanceof ValidationException) {
            $defaultStatusCode = 422;
        }

        if ($th instanceof AuthenticationException || $th instanceof UnauthorizedHttpException) {
            $defaultStatusCode = 401;
        }

        return $this->sendErrorResponse([
            'message' => $message,
        ], $this->isHttpException($th) ? $th->getStatusCode() : $defaultStatusCode);
    }

    /**
     * @param  array|Collection  $data
     * @param  int  $status
     * @return JsonResponse
     */
    private function sendErrorResponse(array|Collection $data, int $status = 500): JsonResponse
    {
        $return = [
            'success' => false,
            'data' => $this->setData($data),
        ];

        $this->storeAuditRequest($return);

        return response()
            ->json($return, $status)
            ->header('Content-Type', 'application/json');
    }

    /**
     * Return data for response.
     *
     * @param  array|Collection  $data
     * @return array
     */
    private function setData(array|Collection $data): array
    {
        if ($data instanceof Collection) {
            return $data->toArray();
        }

        return $data;
    }

    /**
     * Determine if the given exception is an HTTP exception.
     *
     * @param  Throwable  $e
     * @return bool
     */
    protected function isHttpException(Throwable $e): bool
    {
        return $e instanceof HttpExceptionInterface;
    }

    /**
     * Uncomment to enable auditing.
     * You need to setup audit tables first.
     *
     * @param  array  $response
     *
     * @return void
     */
    public function storeAuditRequest(array $response): void
    {
        //        try {
        //            Audit::create([
        //                'response' => $response,
        //            ]);
        //        } catch (Exception $e) {
        //            Log::critical('Failed to create audit request: '.$e->getMessage(), [
        //                'exception' => $e,
        //                'trace' => $e->getTrace(),
        //            ]);
        //        }
    }
}
