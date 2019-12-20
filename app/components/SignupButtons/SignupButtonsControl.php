<?php

namespace App\Components\SignupButtons;

use App\Model\EventInfoProvider;
use Nette\Application\UI\Control;

class SignupButtonsControl extends Control
{
    /**
     * @var EventInfoProvider
     */
    private $eventInfo;


    public function __construct(EventInfoProvider $eventInfo)
    {
        $this->eventInfo = $eventInfo;
    }


    public function render()
    {
        $this->template->setFile(__DIR__ . '/SignupButtons.latte');
        $this->template->dates = $this->eventInfo->getDates();
        $this->template->features = $this->eventInfo->getFeatures();
        $this->template->render();
    }
}
