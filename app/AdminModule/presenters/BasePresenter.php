<?php


namespace App\AdminModule\Presenters;

use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Presenter;
use Nextras\Application\UI\SecuredLinksPresenterTrait;

class BasePresenter extends Presenter
{
    use SecuredLinksPresenterTrait;

    /**
     * @throws ForbiddenRequestException
     * @throws \Nette\Application\AbortException
     */
    protected function startup()
    {
        parent::startup();

        if (!$this->user->isLoggedIn()) {
            $this->flashMessage('Pro přístup do administrace se nejdříve přihlaste.');
            $this->redirect(301, ':Sign:in', ['backlink' => $this->storeRequest()]);
        }

        if (!$this->user->isInRole('admin')) {
            $this->flashMessage('Váš učet nemá do administrace přístup.');
            throw new ForbiddenRequestException('Nemáte přístup do administrace');
        }
    }
}
