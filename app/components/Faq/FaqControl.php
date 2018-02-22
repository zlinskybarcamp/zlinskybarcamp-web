<?php

namespace App\Components\Faq;

use App\Model\EnumeratorManager;
use Nette\Application\UI\Control;

class FaqControl extends Control
{
    /**
     * @var EnumeratorManager
     */
    private $enumeratorManager;


    /**
     * FaqControl constructor.
     * @param EnumeratorManager $enumeratorManager
     */
    public function __construct(EnumeratorManager $enumeratorManager)
    {
        parent::__construct();
        $this->enumeratorManager = $enumeratorManager;
    }


    /**
     * @throws \App\Model\InvalidEnumeratorSetException
     * @throws \Nette\Utils\JsonException
     */
    public function render()
    {
        $this->template->setFile(__DIR__ . '/Faq.latte');
        $this->template->faqs = $this->enumeratorManager->get(EnumeratorManager::SET_FAQS);
        $this->template->addFilter('linkify', function ($input) {
            $regex = '@((https?://)?([-\w]+\.[-\w\.]+)+\w(:\d+)?(/([-\w/_\.\,]*(\?\S+)?)?)*)@';
            $output = preg_replace($regex, '<a href="$1">$1</a>', $input);
            return $output;
        });
        $this->template->render();
    }
}
