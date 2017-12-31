<?php

namespace App\Presenters;

use App\Components\Schedule\Schedule;

class HomepagePresenter extends BasePresenter
{
    /**
     * @var Schedule
     */
    private $scheduleComponent;


    /**
     * HomepagePresenter constructor.
     * @param Schedule $scheduleComponent
     */
    public function __construct(Schedule $scheduleComponent)
    {
        $this->isHp = true;
        $this->scheduleComponent = $scheduleComponent;
    }


    /**
     *
     */
    public function renderDefault()
    {
    }


    /**
     * @return Schedule
     */
    protected function createComponentSchedule()
    {
        return $this->scheduleComponent;
    }
}
