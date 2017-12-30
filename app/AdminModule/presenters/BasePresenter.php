<?php


namespace App\AdminModule\Presenters;

use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Presenter;

class BasePresenter extends Presenter
{
    protected function startup()
    {
        parent::startup();

        if (!$this->user->isLoggedIn()) {
            $this->redirect(301, ':Sign:in');
        }

        if (!$this->user->isInRole('admin')) {
            throw new ForbiddenRequestException('Nemáte přístup do administrace', 403);
        }
    }
}
