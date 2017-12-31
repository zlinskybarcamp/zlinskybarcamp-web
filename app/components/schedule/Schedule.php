<?php


namespace App\Components\Schedule;

use Nette\Application\UI\Control;

class Schedule extends Control
{
    /**
     *
     */
    public function render()
    {
        $this->template->setFile(__DIR__ . '/schedule.latte');
        $this->template->render();
    }
}
