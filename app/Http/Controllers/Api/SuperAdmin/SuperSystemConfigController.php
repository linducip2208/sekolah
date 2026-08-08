<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SuperSystemConfigController extends Controller
{
    private const CACHE_KEY = 'system_config';

    private array $defaults = [
        'app_name'              => 'Sikad Pro',
        'app_logo'              => null,
        'default_plan'          => 'free',
        'trial_days'            => 14,
        'grace_period_days'     => 7,
        'support_email'         => 'support@whitelabel.co.id',
        'storage_driver'        => 'local',
        'maintenance_mode'      => false,
        'allow_school_register' => true,
    ];

    public function show(): JsonResponse
    {
        $config = Cache::get(self::CACHE_KEY, $this->defaults);
        return response()->json($config);
    }

    public function update(Request $request): JsonResponse
    {
        $current = Cache::get(self::CACHE_KEY, $this->defaults);
        $updated = array_merge($current, $request->only(array_keys($this->defaults)));
        Cache::forever(self::CACHE_KEY, $updated);
        return response()->json($updated);
    }
}
