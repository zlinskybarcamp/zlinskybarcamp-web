<?php

namespace App\Components\Schedule;

use App\Model\EventInfoProvider;
use Nette\Application\UI\Control;

class ScheduleControl extends Control
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
        parent::__construct();
        $this->infoProvider = $infoProvider;
    }


    /**
     *
     */
    public function render()
    {
        $this->template->setFile(__DIR__ . '/Schedule.latte');
        $this->template->features = $this->infoProvider->getFeatures();
        $this->template->dates = $this->infoProvider->getDates();
        $this->template->render();
    }
}
