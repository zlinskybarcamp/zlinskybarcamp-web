<?php

namespace App\Presenters;

use App\Components\Program\IProgramControlFactory;
use App\Model\EventInfoProvider;
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
     * @var EventInfoProvider
     */
    private $eventInfoProvider;
    /**
     * @var IProgramControlFactory
     */
    private $programFactory;


    /**
     * ConferencePresenter constructor.
     * @param Orm $orm
     * @param TalkManager $talkManager
     * @param EventInfoProvider $eventInfoProvider
     * @param IProgramControlFactory $programFactory
     */
    public function __construct(
        Orm $orm,
        TalkManager $talkManager,
        EventInfoProvider $eventInfoProvider,
        IProgramControlFactory $programFactory
    ) {
        $this->talkRepository = $orm->talk;
        $this->talkManager = $talkManager;
        $this->eventInfoProvider = $eventInfoProvider;
        $this->programFactory = $programFactory;
    }


    /**
     * @throws \App\Model\InvalidEnumeratorSetException
     * @throws \Nette\Utils\JsonException
     */
    public function renderTalks()
    {
        /** @var ICollection|Talk[] $talks */
        $talks = $this->talkRepository->findBy(
            ['enabled' => true]
        );
        $categories = $this->talkManager->getCategories();

        $sort = $this->eventInfoProvider->getFeatures()['talks_order'];
        if ($sort === 'random') {
            $talks = $talks->fetchAll();
            shuffle($talks);
        } elseif ($sort === 'vote') {
            $talks = $talks->orderBy('votes', ICollection::DESC);
        }

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
        $this->template->allowVote = $this->eventInfoProvider->getFeatures()['vote'];
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
        if ($this->isAjax()) {
            $talk = $this->talkManager->getById($talkId);
            $this->sendJson([
                'votes' => $talk->votes,
                'hasVoted' => true,
            ]);
        }
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
        if ($this->isAjax()) {
            $talk = $this->talkManager->getById($talkId);
            $this->sendJson([
                'votes' => $talk->votes,
                'hasVoted' => false,
            ]);
        }

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


    /**
     * @param $id
     * @throws \Nette\Application\BadRequestException
     * @throws \Nette\Utils\JsonException
     */
    public function renderTalkDetail($id)
    {
        if (!intval($id)) {
            $this->error('Není vyplněno ID přednášky');
        }

        $talk = $this->talkManager->getById($id);

        if (!$talk) {
            $this->error('Přednáška nenalezena');
        }

        $this->template->talk = $talk;
        $this->template->extended = Json::decode($talk->extended, Json::FORCE_ARRAY);
    }


    /**
     * @return \App\Components\Program\ProgramControl
     */
    public function createComponentProgram()
    {
        return $this->programFactory->create();
    }
}
