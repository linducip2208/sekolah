<?php

namespace App\Http\Controllers\Api\Branding;

use App\Http\Controllers\Controller;
use App\Services\Branding\BrandingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandingController extends Controller
{
    public function __construct(private BrandingService $branding) {}

    public function publicShow(string $subdomain): JsonResponse
    {
        return response()->json(['data' => $this->branding->getBySubdomain($subdomain)]);
    }

    public function showMine(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->branding->getForSchool($request->user()->school_id)]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'display_name'           => 'nullable|string|max:200',
            'tagline'                => 'nullable|string|max:200',
            'school_type_label'      => 'nullable|string|max:100',
            'academic_year_format'   => 'nullable|string|max:30',
            'color_primary'          => 'nullable|string|regex:/^#[0-9A-Fa-f]{6,8}$/',
            'color_secondary'        => 'nullable|string|regex:/^#[0-9A-Fa-f]{6,8}$/',
            'color_success'          => 'nullable|string|regex:/^#[0-9A-Fa-f]{6,8}$/',
            'color_warning'          => 'nullable|string|regex:/^#[0-9A-Fa-f]{6,8}$/',
            'color_danger'           => 'nullable|string|regex:/^#[0-9A-Fa-f]{6,8}$/',
            'background_mode'        => 'nullable|in:light,dark,auto',
            'login_welcome_text'     => 'nullable|array',
            'login_show_motto'       => 'nullable|boolean',
            'mobile_splash_bg_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6,8}$/',
            'mobile_app_display_name'=> 'nullable|string|max:200',
            'email_footer_text'      => 'nullable|string|max:2000',
            'receipt_layout'         => 'nullable|in:simple,formal,modern',
            'pdf_watermark_enabled'  => 'nullable|boolean',
            'fcm_notification_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6,8}$/',
        ]);

        $this->branding->update($request->user()->school_id, $data);
        return response()->json(['data' => $this->branding->getForSchool($request->user()->school_id)]);
    }

    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:primary,secondary,monochrome,favicon,login_bg,splash_logo,email_header,fcm_icon',
            'file' => 'required|file|max:2048|mimes:png,jpg,jpeg,svg,ico,webp',
        ]);

        $this->branding->uploadLogo(
            $request->user()->school_id,
            $request->input('type'),
            $request->file('file'),
        );

        return response()->json(['data' => $this->branding->getForSchool($request->user()->school_id)]);
    }

    public function removeLogo(Request $request, string $type): JsonResponse
    {
        $allowed = array_keys(\App\Services\Branding\BrandingService::LOGO_TYPES);
        if (!in_array($type, $allowed, true)) {
            return response()->json(['message' => 'Unknown logo type'], 422);
        }

        $this->branding->removeLogo($request->user()->school_id, $type);
        return response()->json(['data' => $this->branding->getForSchool($request->user()->school_id)]);
    }

    public function reset(Request $request): JsonResponse
    {
        $this->branding->reset($request->user()->school_id);
        return response()->json(['data' => $this->branding->getForSchool($request->user()->school_id)]);
    }
}
