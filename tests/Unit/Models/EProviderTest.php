<?php
/*
 * File name: EProviderTest.php
 * Last modified: 2021.05.21 at 16:11:25
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace Models;

use App\Models\EProvider;
use Carbon\Carbon;
use Tests\TestCase;

class EProviderTest extends TestCase
{

    public function testGetAvailableAttribute()
    {
        $eProvider = EProvider::find(17);
        $this->assertTrue($eProvider->available);
        $this->assertTrue($eProvider->accepted);
        $this->assertTrue($eProvider->openingHours()->isOpenAt(new Carbon('2021-02-05 12:00:00')));
    }

    public function testOpeningHours()
    {
        $eProvider = EProvider::find(17);
        $open = $eProvider->openingHours()->isOpenAt(new Carbon('2021-02-05 12:00:00'));
        $this->assertTrue($open);
    }

    public function testWeekCalendar()
    {
        $eProvider = EProvider::find(17);
        $dates = $eProvider->weekCalendar(Carbon::now());
        $this->assertIsArray($dates);
    }
}
