<?php

namespace App\Presenters;

use App\Components\Faq\FaqFactory;
use App\Components\Feed\FeedFactory;
use App\Components\Newsletter\NewsletterSignupControl;
use App\Components\Newsletter\NewsletterSignupFactory;
use App\Components\Schedule\ScheduleControl;
use App\Components\Schedule\ScheduleFactory;
use App\Components\SignupButtons\SignupButtonsControl;
use App\Components\SignupButtons\SignupButtonsFactory;

/**
 * Class HomepagePresenter
 * @package App\Presenters
 */
class HomepagePresenter extends BasePresenter
{
    /**
     * @var ScheduleFactory
     */
    private $scheduleFactory;
    /**
     * @var SignupButtonsFactory
     */
    private $buttonsFactory;
    /**
     * @var NewsletterSignupFactory
     */
    private $newsletterFactory;
    /**
     * @var FaqFactory
     */
    private $faqFactory;
    /**
     * @var FeedFactory
     */
    private $feedFactory;


    /**
     * HomepagePresenter constructor.
     * @param ScheduleFactory $scheduleFactory
     * @param SignupButtonsFactory $buttonsFactory
     * @param NewsletterSignupFactory $newsletterFactory
     * @param FaqFactory $faqFactory
     * @param FeedFactory $feedFactory
     */
    public function __construct(
        ScheduleFactory $scheduleFactory,
        SignupButtonsFactory $buttonsFactory,
        NewsletterSignupFactory $newsletterFactory,
        FaqFactory $faqFactory,
        FeedFactory $feedFactory
    ) {
        $this->scheduleFactory = $scheduleFactory;
        $this->buttonsFactory = $buttonsFactory;
        $this->newsletterFactory = $newsletterFactory;
        $this->faqFactory = $faqFactory;
        $this->feedFactory = $feedFactory;
    }


    /**
     *
     * @throws \Nette\Utils\JsonException
     */
    public function renderDefault()
    {
        $this->template->isHp = true;
        $this->template->eventDate = $this->eventInfo->getEventDate();
        $this->template->counts = $this->eventInfo->getCounts();
    }


    /**
     * @return ScheduleControl
     */
    protected function createComponentSchedule()
    {
        return $this->scheduleFactory->create();
    }


    /**
     * @return SignupButtonsControl
     */
    protected function createComponentSignupButtons()
    {
        return $this->buttonsFactory->create();
    }


    /**
     * @return mixed
     */
    protected function createComponentFeed()
    {
        return $this->feedFactory->create();
    }


    /**
     * @return NewsletterSignupControl
     */
    protected function createComponentNewsletterForm()
    {
        return $this->newsletterFactory->create();
    }


    /**
     * @return \App\Components\Faq\FaqControl
     */
    protected function createComponentFaq()
    {
        return $this->faqFactory->create();
    }
}
