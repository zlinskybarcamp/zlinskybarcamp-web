<?php

namespace App\Components\SignupButtons;

use Nette\Application\UI\Control;

class SignupButtonsControl extends Control
{
    public function render()
    {
        $this->template->setFile(__DIR__ . '/SignupButtons.latte');
        $this->template->render();
    }
}
