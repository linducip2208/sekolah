<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\LandingThemeRegistry;
use App\Services\PlatformSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlatformWhitelabelController extends Controller
{
    public function __construct(private PlatformSettingsService $platformSettings) {}

    public function show(): View
    {
        return view('super-admin.whitelabel.index', [
            'settings'    => $this->platformSettings->all(),
            'imageFields' => PlatformSettingsService::IMAGE_FIELDS,
            'themes'      => LandingThemeRegistry::themes(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'app_name'         => 'nullable|string|max:120',
            'tagline'          => 'nullable|string|max:200',
            'motto_latin'      => 'nullable|string|max:120',
            'motto_translated' => 'nullable|string|max:200',
            'description'      => 'nullable|string|max:500',
            'established_year' => 'nullable|string|max:8',
            'institution_type' => 'nullable|string|max:120',
            'hero_kicker'      => 'nullable|string|max:120',
            'hero_title'       => 'nullable|string|max:300',
            'hero_subtitle'    => 'nullable|string|max:500',
            'color_primary'    => 'nullable|string|max:9',
            'color_secondary'  => 'nullable|string|max:9',
            'color_accent'     => 'nullable|string|max:9',
            'color_paper'      => 'nullable|string|max:9',
            'contact_phone'    => 'nullable|string|max:32',
            'contact_whatsapp' => 'nullable|string|max:32',
            'contact_email'    => 'nullable|email|max:120',
            'address_line1'    => 'nullable|string|max:200',
            'address_line2'    => 'nullable|string|max:200',
            'social_facebook'  => 'nullable|url|max:200',
            'social_instagram' => 'nullable|url|max:200',
            'social_youtube'   => 'nullable|url|max:200',
            'social_linkedin'  => 'nullable|url|max:200',
            'popup_enabled'    => 'nullable|in:0,1,true,false,on',
            'popup_title'      => 'nullable|string|max:120',
            'popup_message'    => 'nullable|string|max:500',
            'popup_phone'      => 'nullable|string|max:32',
            'popup_whatsapp'   => 'nullable|string|max:32',
            'popup_cta_text'   => 'nullable|string|max:60',
            'footer_disclaimer'=> 'nullable|string|max:300',
            'landing_theme'    => 'nullable|in:'.implode(',', LandingThemeRegistry::keys()),
        ]);

        $data['popup_enabled'] = $request->boolean('popup_enabled');

        $this->platformSettings->update($data);

        return back()->with('success', 'Pengaturan whitelabel berhasil disimpan.');
    }

    public function uploadImage(Request $request, string $field): RedirectResponse
    {
        $request->validate(['file' => 'required|file|image|max:5120']);

        try {
            $this->platformSettings->uploadImage($field, $request->file('file'));
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        return back()->with('success', 'Gambar berhasil diupload.');
    }

    public function removeImage(string $field): RedirectResponse
    {
        try {
            $this->platformSettings->removeImage($field);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        return back()->with('success', 'Gambar berhasil dihapus.');
    }
}
