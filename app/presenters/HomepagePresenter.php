<?php

namespace App\Presenters;

use App\Components\Newsletter\NewsletterSignupControl;
use App\Components\Newsletter\NewsletterSignupFactory;
use App\Components\Schedule\ScheduleControl;
use App\Components\Schedule\ScheduleFactory;
use App\Components\SignupButtons\SignupButtonsControl;
use App\Components\SignupButtons\SignupButtonsFactory;
use App\Model\EventInfoProvider;

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
     * HomepagePresenter constructor.
     * @param ScheduleFactory $scheduleFactory
     * @param SignupButtonsFactory $buttonsFactory
     * @param NewsletterSignupFactory $newsletterFactory
     */
    public function __construct(
        ScheduleFactory $scheduleFactory,
        SignupButtonsFactory $buttonsFactory,
        NewsletterSignupFactory $newsletterFactory
    ) {
        $this->scheduleFactory = $scheduleFactory;
        $this->buttonsFactory = $buttonsFactory;
        $this->newsletterFactory = $newsletterFactory;
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
     * @return NewsletterSignupControl
     */
    protected function createComponentNewsletterForm()
    {
        return $this->newsletterFactory->create();
    }
}
