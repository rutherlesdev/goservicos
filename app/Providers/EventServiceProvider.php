<?php
/*
 * File name: EventServiceProvider.php
 * Last modified: 2021.04.02 at 16:30:17
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        'App\Events\EProviderChangedEvent' => [
            'App\Listeners\UpdateEProviderEarningTableListener',
            'App\Listeners\ChangeCustomerRoleToEProvider',
        ],
        'App\Events\UserRoleChangedEvent' => [

        ],
        'App\Events\BookingChangedEvent' => [
            'App\Listeners\UpdateBookingEarningTable'
        ],

    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
