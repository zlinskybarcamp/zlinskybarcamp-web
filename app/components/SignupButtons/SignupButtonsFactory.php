<?php

namespace App\Components\SignupButtons;

use App\Model\EventInfoProvider;

class SignupButtonsFactory
{
    /**
     * @var EventInfoProvider
     */
    private $eventInfoProvider;


    /**
     * SignupButtonsFactory constructor.
     * @param EventInfoProvider $eventInfoProvider
     */
    public function __construct(EventInfoProvider $eventInfoProvider)
    {
        $this->eventInfoProvider = $eventInfoProvider;
    }


    /**
     * @return SignupButtonsControl
     */
    public function create()
    {
        return new SignupButtonsControl($this->eventInfoProvider);
    }
}
