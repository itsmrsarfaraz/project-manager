<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        Horizon::routeSmsNotificationsTo('your-phone-number');
        Horizon::routeMailNotificationsTo('your-email@example.com');
        // ↑ Get notified when the queue stops processing
    }

    protected function gate(): void
    {
        Gate::define('viewHorizon', function ($user) {
            // Only allow in local dev, or expand to specific users in staging/prod
            return app()->environment('local')
                || in_array($user->email, [
                    'alice@example.com',
                ]);
        });
    }
}
