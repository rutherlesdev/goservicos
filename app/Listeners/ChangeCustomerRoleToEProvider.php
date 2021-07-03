<?php
/*
 * File name: ChangeCustomerRoleToEProvider.php
 * Last modified: 2021.04.14 at 06:43:59
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Listeners;

/**
 * Class ChangeCustomerRoleToEProvider
 * @package App\Listeners
 */
class ChangeCustomerRoleToEProvider
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        if ($event->newEProvider->accepted && !$event->oldEProvider->accepted) {
            foreach ($event->newEProvider->users as $user) {
                $user->syncRoles(['provider']);
            }
        }
    }
}
