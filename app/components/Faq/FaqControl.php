<?php

namespace App\Components\Faq;

use App\Model\FaqManager;
use Nette\Application\UI\Control;
use Tracy\Debugger;

class FaqControl extends Control
{
    /**
     * @var FaqManager
     */
    private $faqManager;


    /**
     * FaqControl constructor.
     * @param FaqManager $faqManager
     */
    public function __construct(FaqManager $faqManager)
    {
        $this->faqManager = $faqManager;
    }


    /**
     * @throws \Nette\Utils\JsonException
     */
    public function render()
    {
        $this->template->setFile(__DIR__ . '/Faq.latte');
        $this->template->faqs = $this->faqManager->get();
        $this->template->render();
    }
}
