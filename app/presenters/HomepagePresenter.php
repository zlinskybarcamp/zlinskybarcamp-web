<?php

namespace App\Presenters;

use App\Components\Newsletter\NewsletterSignupControl;
use App\Components\Newsletter\NewsletterSignupFactory;
use App\Components\Schedule\ScheduleControl;
use App\Components\Schedule\ScheduleFactory;
use App\Components\SignupButtons\SignupButtonsControl;
use App\Components\SignupButtons\SignupButtonsFactory;
use App\Model\EventInfoProvider;

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
     * @var EventInfoProvider
     */
    private $eventInfo;


    /**
     * HomepagePresenter constructor.
     * @param EventInfoProvider $eventInfo
     * @param ScheduleFactory $scheduleFactory
     * @param SignupButtonsFactory $buttonsFactory
     * @param NewsletterSignupFactory $newsletterFactory
     */
    public function __construct(
        EventInfoProvider $eventInfo,
        ScheduleFactory $scheduleFactory,
        SignupButtonsFactory $buttonsFactory,
        NewsletterSignupFactory $newsletterFactory
    ) {
        $this->scheduleFactory = $scheduleFactory;
        $this->buttonsFactory = $buttonsFactory;
        $this->newsletterFactory = $newsletterFactory;
        $this->eventInfo = $eventInfo;
    }


    /**
     *
     */
    public function renderDefault()
    {
        $this->template->isHp = true;
        $this->template->eventDate = $this->eventInfo->getEventDate();
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
