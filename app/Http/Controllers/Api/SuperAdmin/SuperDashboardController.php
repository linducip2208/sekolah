<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\SuperAdminService;
use Illuminate\Http\JsonResponse;

class SuperDashboardController extends Controller
{
    public function __construct(private SuperAdminService $service) {}

    public function index(): JsonResponse
    {
        return response()->json($this->service->dashboardStats());
    }
}
