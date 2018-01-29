<?php

namespace App\Presenters;

use App\Components\Faq\FaqControl;
use App\Components\Faq\IFaqControlFactory;
use App\Components\Feed\FeedControl;
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
     * @var IFaqControlFactory
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
     * @param NewsletterSignupFactory $newsletterFormFactory
     * @param IFaqControlFactory $faqFactory
     * @param FeedFactory $feedFactory
     */
    public function __construct(
        ScheduleFactory $scheduleFactory,
        SignupButtonsFactory $buttonsFactory,
        NewsletterSignupFactory $newsletterFormFactory,
        IFaqControlFactory $faqFactory,
        FeedFactory $feedFactory
    ) {
        parent::__construct();
        $this->scheduleFactory = $scheduleFactory;
        $this->buttonsFactory = $buttonsFactory;
        $this->newsletterFactory = $newsletterFormFactory;
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
     * @return FeedControl
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
     * @return FaqControl
     */
    protected function createComponentFaq()
    {
        return $this->faqFactory->create();
    }
}
