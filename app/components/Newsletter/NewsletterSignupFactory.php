<?php

namespace App\Components\Newsletter;

use App\Model\NewsletterSignupManager;

class NewsletterSignupFactory
{
    /**
     * @var NewsletterSignupManager
     */
    private $signupManager;


    public function __construct(NewsletterSignupManager $signupManager)
    {

        $this->signupManager = $signupManager;
    }


    public function create()
    {
        return new NewsletterSignupControl($this->signupManager);
    }
}
