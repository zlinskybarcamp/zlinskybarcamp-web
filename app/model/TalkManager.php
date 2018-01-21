<?php

namespace App\Model;

use App\Orm\Orm;
use App\Orm\TalkRepository;
use Nette\Utils\DateTime;

class TalkManager
{
    /** @var TalkRepository $talkRepository */
    private $talkRepository;


    /**
     * TalkManager constructor.
     * @param Orm $orm
     */
    public function __construct(Orm $orm)
    {
        $this->talkRepository = $orm->talk;
    }


    /**
     * @param Talk $talk
     */
    public function save(Talk $talk)
    {
        $this->talkRepository->persistAndFlush($talk);
    }


    /**
     * @return array
     */
    public function getCategories()
    {
        return [
            'teambuilding' => 'Teambuilding',
            'seo' => 'SEO',
            'media' => 'Marketing a média',
            'leadership' => 'Leadership'
        ];
    }
}
