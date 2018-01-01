<?php

namespace App\Components\SignupButtons;

class SignupButtonsFactory
{
    /**
     * @return SignupButtonsControl
     */
    public function create()
    {
        return new SignupButtonsControl();
    }
}
