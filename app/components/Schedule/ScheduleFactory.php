<?php

namespace App\Components\Schedule;

use App\Model\EventInfoProvider;

class ScheduleFactory
{
    /**
     * @var EventInfoProvider
     */
    private $infoProvider;


    /**
     * ScheduleControl constructor.
     * @param EventInfoProvider $infoProvider
     */
    public function __construct(EventInfoProvider $infoProvider)
    {
        $this->infoProvider = $infoProvider;
    }


    /**
     * @return ScheduleControl
     */
    public function create()
    {
        return new ScheduleControl($this->infoProvider);
    }
}
