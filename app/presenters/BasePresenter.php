<?php

namespace App\Presenters;

use App\Model\EventInfoProvider;
use Nette;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    /**
     * @var EventInfoProvider $eventInfo
     */
    protected $eventInfo;


    /**
     * @param EventInfoProvider $eventInfo
     */
    public function inject(EventInfoProvider $eventInfo)
    {
        $this->eventInfo = $eventInfo;
    }


    /**
     *
     */
    protected function beforeRender()
    {
        parent::beforeRender();
        $this->template->dates = $this->eventInfo->getDates();
        $this->template->socialUrls = $this->eventInfo->getSocialUrls();
    }
}
