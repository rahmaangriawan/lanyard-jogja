<?php

namespace App\Providers;

use App\Models\CityPage;
use App\Models\Post;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Observers\CityPageObserver;
use App\Observers\PostObserver;
use App\Observers\ProductObserver;
use App\Observers\SettingObserver;
use App\Observers\TestimonialObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Product::observe(ProductObserver::class);
        Post::observe(PostObserver::class);
        CityPage::observe(CityPageObserver::class);
        Setting::observe(SettingObserver::class);
        Testimonial::observe(TestimonialObserver::class);

        RateLimiter::for('login-global', fn (Request $request) => Limit::perMinute(20)
            ->by('login-global|'.$request->ip()));
        RateLimiter::for('password-email', fn (Request $request) => Limit::perMinute(3)
            ->by('password-email|'.Str::lower(trim((string) $request->input('email'))).'|'.$request->ip()));
        RateLimiter::for('password-reset', fn (Request $request) => Limit::perMinute(5)
            ->by('password-reset|'.Str::lower(trim((string) $request->input('email'))).'|'.$request->ip()));
        RateLimiter::for('verification-email', fn (Request $request) => Limit::perMinute(3)
            ->by('verification-email|'.Str::lower(trim((string) $request->user()?->email)).'|'.$request->ip()));
        RateLimiter::for('comment-submit', fn (Request $request) => [
            Limit::perMinutes(10, 5)->by('comment-ip|'.$request->ip()),
            Limit::perMinutes(10, 3)->by('comment-email|'.Str::lower(trim((string) $request->input('email')))),
        ]);
        RateLimiter::for('inquiry-submit', fn (Request $request) => [
            Limit::perMinutes(5, 3)->by('inquiry-ip|'.$request->ip()),
            Limit::perMinutes(10, 2)->by('inquiry-email|'.Str::lower(trim((string) $request->input('email')))),
        ]);
        RateLimiter::for('public-api', fn (Request $request) => Limit::perMinute(120)
            ->by('public-api|'.$request->ip()));
        RateLimiter::for('cms-api', fn (Request $request) => Limit::perMinute(120)
            ->by('cms-api|'.($request->user()?->getAuthIdentifier() ?: $request->ip())));

        Vite::prefetch(concurrency: 3);
    }
}
