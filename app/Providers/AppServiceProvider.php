<?php

namespace App\Providers;

use App\Contracts\MessagingProvider;
use App\Contracts\PaymentGateway;
use App\Services\FakeMessagingProvider;
use App\Services\FakePaymentGateway;
use App\Support\Tenancy\TenantContext;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(TenantContext::class);
        $this->app->bind(PaymentGateway::class, FakePaymentGateway::class);
        $this->app->bind(MessagingProvider::class, FakeMessagingProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = Str::transliterate(Str::lower((string) $request->input('email')));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });
    }
}
