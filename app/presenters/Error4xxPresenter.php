<?php

namespace App\Presenters;

use Nette;

class Error4xxPresenter extends BasePresenter
{
    /**
     * @throws Nette\Application\BadRequestException
     */
    public function startup()
    {
        parent::startup();
        if (!$this->getRequest()->isMethod(Nette\Application\Request::FORWARD)) {
            $this->error();
        }
    }


    /**
     * @param Nette\Application\BadRequestException $exception
     */
    public function renderDefault(Nette\Application\BadRequestException $exception)
    {
        // load template 403.latte or 404.latte or ... 4xx.latte
        $errorCode = $exception->getCode();
        $file = __DIR__ . "/templates/Error/{$errorCode}.latte";
        $file = is_file($file) ? $file : __DIR__ . '/templates/Error/4xx.latte';
        $this->template->setFile($file);
        $this->setLayout(__DIR__ . '/templates/Error/@layout.latte');
        $this->template->errorCode = $errorCode;
        $this->template->errorMessage = $exception->getMessage();
    }
}
