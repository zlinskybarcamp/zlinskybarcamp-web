<?php

namespace App\Components\Faq;

use App\Model\FaqManager;

class FaqFactory
{
    /**
     * @var FaqManager
     */
    private $faqManager;


    /**
     * FaqFactory constructor.
     * @param FaqManager $faqManager
     */
    public function __construct(FaqManager $faqManager)
    {
        $this->faqManager = $faqManager;
    }


    /**
     * @return FaqControl
     */
    public function create()
    {
        return new FaqControl($this->faqManager);
    }
}
