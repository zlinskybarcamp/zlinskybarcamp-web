<?php

namespace App\Components\Schedule;

use App\Model\EventInfoProvider;
use App\Model\ScheduleManager;
use Nette\Application\UI\Control;
use Nette\Utils\Json;

class ScheduleControl extends Control
{
    /**
     * @var EventInfoProvider
     */
    private $infoProvider;
    /**
     * @var ScheduleManager
     */
    private $scheduleManager;


    /**
     * ScheduleControl constructor.
     * @param EventInfoProvider $infoProvider
     * @param ScheduleManager $scheduleManager
     */
    public function __construct(EventInfoProvider $infoProvider, ScheduleManager $scheduleManager)
    {
        parent::__construct();
        $this->infoProvider = $infoProvider;
        $this->scheduleManager = $scheduleManager;
    }


    /**
     *
     * @throws \Nette\Utils\JsonException
     */
    public function render()
    {
        $dates = $this->infoProvider->getDates();
        $features = $this->infoProvider->getFeatures();
        $steps = $this->scheduleManager->getSteps(false, false);

        $this->template->setFile(__DIR__ . '/Schedule.latte');
        $this->template->features = $this->infoProvider->getFeatures();
        $this->template->dates = $dates;
        $this->template->urls = $this->infoProvider->getSocialUrls();

        $this->template->config = [
            'dates' => $dates,
            'steps'=> $steps,
            'features' => $features
        ];

        $this->template->render();
    }
}
