<?php

namespace App\Components\Schedule;

interface IScheduleControlFactory
{

    /**
     * @return ScheduleControl
     */
    public function create();

}
