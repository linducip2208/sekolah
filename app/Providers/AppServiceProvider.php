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
            if (!array_key_exists('platform', $view->getData())) {
                $view->with('platform', app(PlatformSettingsService::class)->all());
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
