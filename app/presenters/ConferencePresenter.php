<?php

namespace App\Presenters;

use App\Model\ConfereeManager;
use App\Model\TalkManager;

class ConferencePresenter extends BasePresenter
{
    /**
     * @var ConfereeManager
     */
    private $conferee;
    /**
     * @var TalkManager
     */
    private $talk;


    /**
     * ConferencePresenter constructor.
     * @param ConfereeManager $conferee
     * @param TalkManager $talk
     */
    public function __construct(ConfereeManager $conferee, TalkManager $talk)
    {
        $this->conferee = $conferee;
        $this->talk = $talk;
    }


}
