<?php

namespace App\Components\Newsletter;

use App\Model\NewsletterSignupManager;

class NewsletterSignupFactory
{
    /**
     * @var NewsletterSignupManager
     */
    private $signupManager;


    /**
     * NewsletterSignupFactory constructor.
     * @param NewsletterSignupManager $signupManager
     */
    public function __construct(NewsletterSignupManager $signupManager)
    {

        $this->signupManager = $signupManager;
    }


    /**
     * @return NewsletterSignupControl
     */
    public function create()
    {
        return new NewsletterSignupControl($this->signupManager);
    }
}
