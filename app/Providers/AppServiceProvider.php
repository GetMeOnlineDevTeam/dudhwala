<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\MemberContact;
use App\Models\Policies;

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
        // Share active contacts and footer policies with all views
        View::composer('*', function ($view) {
            $contacts = MemberContact::where('is_active', true)->get();

            $footerPolicies = Policies::whereIn('type', ['privacy', 'terms'])->get()->keyBy('type');

            $view->with([
                'footerContacts' => $contacts,
                'footerPolicies' => $footerPolicies, // accessed via $footerPolicies['privacy'], etc.
            ]);
        });
    }
}
