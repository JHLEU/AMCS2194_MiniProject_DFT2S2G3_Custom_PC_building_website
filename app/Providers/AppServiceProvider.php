<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
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
        View::composer('layouts.app', function ($view) {
            $contactInfo = [
                'store_name'       => 'Custom PC Enterprise & Workshop',
                'hotline'          => '+60 3-8888 1234',
                'whatsapp_phone'   => '+60 16-888 2345',
                'whatsapp_url'     => 'https://wa.me/60168882345?text=' . urlencode('Hello Custom PC Team! I would like to inquire about building a custom PC.'),
                'support_email'    => 'support@custompc.local',
                'sales_email'      => 'sales@custompc.local',
                'address'          => 'Lot 4.28, Level 4, Plaza Low Yat, No. 7, Jalan Bintang, Bukit Bintang, 55100 Kuala Lumpur',
                'operating_hours'  => 'Monday – Saturday: 10:00 AM – 8:00 PM (Sunday Closed for System Testing)',
                'workshop_service' => 'Walk-in Diagnostics, Bench Thermal Repasting, Custom Hard-line Liquid Cooling Servicing',
            ];

            $view->with('contactInfo', $contactInfo);
        });
    }
}
