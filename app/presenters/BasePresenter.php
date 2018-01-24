<?php

namespace App\Presenters;

use App\Model\EventInfoProvider;
use Nette;
use Nextras\Application\UI\SecuredLinksPresenterTrait;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    use SecuredLinksPresenterTrait;

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
     * @throws Nette\Utils\JsonException
     */
    protected function beforeRender()
    {
        parent::beforeRender();
        $parameters = $this->context->getParameters();
        $this->template->wwwDir = $parameters['wwwDir'];

        $this->template->dates = $this->eventInfo->getDates();
        $this->template->features = $this->eventInfo->getFeatures();
        $this->template->socialUrls = $this->eventInfo->getSocialUrls();
    }
}
