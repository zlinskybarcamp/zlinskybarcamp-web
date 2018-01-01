<?php

namespace App\Components\Schedule;

class ScheduleFactory
{
    /**
     * @return ScheduleControl
     */
    public function create()
    {
        return new ScheduleControl();
    }
}
