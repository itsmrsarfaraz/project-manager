<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    public function register(): void
    {
        // Only enable in local environment
        // In production this whole provider is skipped
        Telescope::night(); // dark mode — easier on the eyes

        $this->hideSensitiveRequestDetails();

        // Filter what gets recorded — avoid storing too much
        Telescope::filter(function (IncomingEntry $entry) {
            if ($this->app->environment('local')) {
                return true; // record everything in local
            }

            // On staging: only record slow queries, failed jobs, exceptions
            return $entry->isReportableException()
                || $entry->isFailedRequest()
                || $entry->isFailedJob()
                || $entry->isScheduledTask()
                || $entry->hasMonitoredTag();
        });
    }

    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return; // show everything in local dev
        }

        // On staging, hide sensitive fields
        Telescope::hideRequestParameters(['_token', 'password', 'password_confirmation']);
        Telescope::hideRequestHeaders(['cookie', 'x-csrf-token', 'x-xsrf-token']);
    }

    protected function gate(): void
    {
        // Who can access Telescope UI at /telescope
        Gate::define('viewTelescope', function ($user) {
            // Only allow in local, or specific email domains in staging
            return in_array($user->email, [
                'alice@example.com', // your dev account
            ]) || app()->environment('local');
        });
    }
}
