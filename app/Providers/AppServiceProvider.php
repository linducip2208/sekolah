<?php

namespace App\Providers;

use App\Models\Academic\Attendance;
use App\Models\Academic\Mark;
use App\Models\Academic\Student;
use App\Models\Achievement\StudentAchievement;
use App\Models\Finance\FeeInvoice;
use App\Observers\AttendanceObserver;
use App\Observers\FeeInvoiceObserver;
use App\Observers\MarkObserver;
use App\Observers\StudentAchievementObserver;
use App\Observers\StudentObserver;
use App\Services\LicenseClient;
use App\Services\PlatformSettingsService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PlatformSettingsService::class);
        $this->app->singleton(LicenseClient::class);

        // chillerlan QR code (dependency present but composer autoload not regenerated in some environments)
        spl_autoload_register(function (string $class) {
            $map = [
                'chillerlan\\QRCode\\'   => base_path('vendor/chillerlan/php-qrcode/src/'),
                'chillerlan\\Settings\\' => base_path('vendor/chillerlan/php-settings-container/src/'),
            ];
            foreach ($map as $prefix => $dir) {
                if (str_starts_with($class, $prefix)) {
                    $file = $dir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
                    if (is_file($file)) {
                        require $file;
                    }
                    return;
                }
            }
        });
    }

    public function boot(): void
    {
        Student::observe(StudentObserver::class);
        FeeInvoice::observe(FeeInvoiceObserver::class);
        Attendance::observe(AttendanceObserver::class);
        Mark::observe(MarkObserver::class);
        StudentAchievement::observe(StudentAchievementObserver::class);

        $this->configureRateLimiting();

        View::composer('*', function ($view) {
            $data = $view->getData();
            if (!array_key_exists('platform', $data)) {
                $view->with('platform', app(PlatformSettingsService::class)->all());
            }
            if (!array_key_exists('theme', $data)) {
                $view->with('theme', \App\Services\LandingThemeRegistry::get(
                    app(PlatformSettingsService::class)->get('landing_theme')
                ));
            }
        });
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', fn (Request $req) =>
            Limit::perMinute(8)->by(strtolower($req->input('email', '')) . '|' . $req->ip())
        );

        RateLimiter::for('2fa', fn (Request $req) =>
            Limit::perMinute(10)->by((string) ($req->session()->get('2fa.pending_user_id') ?? $req->ip()))
        );

        RateLimiter::for('password-reset', fn (Request $req) =>
            Limit::perHour(5)->by(strtolower($req->input('email', '')) . '|' . $req->ip())
        );

        RateLimiter::for('export', fn (Request $req) =>
            Limit::perHour(6)->by($req->user()?->id ?: $req->ip())
        );

        RateLimiter::for('webhook-test', fn (Request $req) =>
            Limit::perMinute(20)->by($req->user()?->id ?: $req->ip())
        );

        RateLimiter::for('api', fn (Request $req) =>
            $req->user()
                ? Limit::perMinute(180)->by('api:user:' . $req->user()->id)
                : Limit::perMinute(60)->by('api:ip:' . $req->ip())
        );
    }
}
