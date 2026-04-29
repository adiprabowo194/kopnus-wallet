<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;


class AppServiceProvider extends ServiceProvider
{
    private function limitResponse()
    {
        return function () {
            return response()->json([
                'status'  => 'error',
                'code'    => 429,
                'message' => 'Terlalu banyak request, coba lagi nanti'
            ], 429);
        };
    }
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // GLOBAL
        RateLimiter::for('global', function (Request $request) {
            $key = $request->route('memberCode');
            return Limit::perMinute(60)
                ->by($key)
                ->response($this->limitResponse());
        });
    }
}
