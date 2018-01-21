<?php

namespace App\Model;

use App\Orm\Conferee;
use App\Orm\ConfereeRepository;
use App\Orm\Orm;


class ConfereeManager
{
    /**
     * @var ConfereeRepository
     */
    private $confereeRepository;


    /**
     * ConfereeManager constructor.
     * @param Orm $orm
     */
    public function __construct(Orm $orm)
    {
        $this->confereeRepository = $orm->conferee;
    }


    /**
     * @param Conferee $conferee
     */
    public function save(Conferee $conferee)
    {
        $this->confereeRepository->persistAndFlush($conferee);
    }
}
