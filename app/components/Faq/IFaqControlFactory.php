<?php

namespace App\Components\Faq;

interface IFaqControlFactory
{

    /**
     * @return FaqControl
     */
    public function create();

}
