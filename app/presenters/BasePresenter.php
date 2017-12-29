<?php

namespace App\Presenters;

use Nette;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

    protected $isHp = false;


    protected function beforeRender()
    {
        parent::beforeRender();
        $this->template->isHp = $this->isHp;
    }


}
