<?php

namespace App\Http\Controllers\Widgets;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ApiFormatter;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SysAdminController extends Controller
{
    use ApiFormatter;

    public function index(Request $request, string $action): JsonResponse
    {
        return match ($action) {
            'clear-cache' => $this->clearCache(),
            'restart-queue' => $this->restartQueue(),
            default => $this->sendErrorResponse(['message' => 'Invalid Action'], 500),
        };
    }

    private function clearCache(): JsonResponse
    {
        Artisan::call('optimize:clear');

        if (app()->isProduction()) {
            Artisan::call('optimize');
        }

        return $this->sendResponse(['message' => 'Cache Cleared!']);
    }

    private function restartQueue(): JsonResponse
    {
        try {
            if (app()->isProduction()) {
                (new \App\Services\SupervisorService())->restart();

                return $this->sendResponse(['message' => 'Supervisor reloaded']);
            }

            return $this->sendErrorResponse(['message' => 'Restart Horizon only works in Production Mode']);
        } catch (Exception $e) {
            return $this->sendErrorResponse(['message' => $e->getMessage()], 500);
        }
    }
}
