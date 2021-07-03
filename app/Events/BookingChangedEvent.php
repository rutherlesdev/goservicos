<?php
/*
 * File name: BookingChangedEvent.php
 * Last modified: 2021.06.09 at 15:53:58
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingChangedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $eProvider;

    /**
     * BookingChangedEvent constructor.
     * @param $eProvider
     */
    public function __construct($eProvider)
    {
        $this->eProvider = $eProvider;
    }


}
