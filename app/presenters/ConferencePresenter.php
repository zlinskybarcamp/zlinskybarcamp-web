<?php

namespace App\Presenters;

use App\Model\TalkManager;
use App\Orm\Orm;
use App\Orm\Talk;
use App\Orm\TalkRepository;
use Nette\Http\IResponse;
use Nette\Utils\Json;
use Nextras\Orm\Collection\ICollection;

class ConferencePresenter extends BasePresenter
{
    /** @var TalkRepository $talkRepository */
    private $talkRepository;
    /**
     * @var TalkManager
     */
    private $talkManager;


    /**
     * ConferencePresenter constructor.
     * @param Orm $orm
     * @param TalkManager $talkManager
     */
    public function __construct(Orm $orm, TalkManager $talkManager)
    {
        $this->talkRepository = $orm->talk;
        $this->talkManager = $talkManager;

    }


    /**
     * @throws \Nette\Utils\JsonException
     */
    public function renderTalks()
    {
        /** @var ICollection|Talk[] $talks */
        $talks = $this->talkRepository->findAll();
        $categories = $this->talkManager->getCategories();

        $filtered = [];
        foreach ($talks as $talk) {
            if ($talk->conferee === null) {
                continue;
            }

            $extended = [];

            if ($talk->extended) {
                $extended = Json::decode($talk->extended, Json::FORCE_ARRAY);
            }

            $filtered[] = [
                'talk' => $talk,
                'extended' => $extended,
                'category' => isset($categories[$talk->category]) ? $categories[$talk->category] : null,
            ];
        }
        $this->template->talksInfo = $filtered;
        $this->template->count = count($filtered);

        $votes = [];

        if ($this->user->isLoggedIn()) {
            $votes = $this->talkManager->getUserVotes($this->user->id);
        }

        $this->template->votes = $votes;
    }


    /**
     * @secured
     * @param int $talkId
     * @throws \Nette\Application\AbortException
     */
    public function handleVote($talkId)
    {
        $userId = $this->user->id;
        $this->talkManager->addVote($userId, $talkId);
        $this->redirect(IResponse::S303_SEE_OTHER, 'this');
    }


    /**
     * @secured
     * @param int $talkId
     * @throws \Nette\Application\AbortException
     */
    public function handleUnvote($talkId)
    {
        $userId = $this->user->id;
        $this->talkManager->removeVote($userId, $talkId);
        $this->redirect(IResponse::S303_SEE_OTHER, 'this');
    }


    /**
     * @throws \Nette\Application\AbortException
     */
    public function handleSignToVote()
    {
        if ($this->user->isLoggedIn()) {
            $this->redirect(IResponse::S303_SEE_OTHER, 'this');
        } else {
            $this->redirect(IResponse::S303_SEE_OTHER, 'Sign:in', [
                'backlink' => $this->storeRequest()
            ]);
        }
    }
}
