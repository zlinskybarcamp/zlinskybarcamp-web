<?php

namespace App\Components\Schedule;

use Nette\Application\UI\Control;

class ScheduleControl extends Control
{
    /**
     *
     */
    public function render()
    {
        $this->template->setFile(__DIR__ . '/Schedule.latte');
        $this->template->render();
    }
}
