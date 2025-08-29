<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\MemberContact;

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
        // Share all active contacts with every single view
        View::composer('*', function ($view) {
            $contacts = MemberContact::where('is_active', true)->get();
            $view->with('footerContacts', $contacts);
        });
    }
}
