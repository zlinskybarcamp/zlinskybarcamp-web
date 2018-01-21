<?php

namespace App\Model;

use App\Orm\Orm;
use App\Orm\Identity;
use App\Orm\User;

class IdentityManager
{

    /**
     * @var \App\Orm\IdentityRepository
     */
    private $identityRepository;


    /**
     * IdentityManager constructor
     *
     * @param Orm $orm
     */
    public function __construct(Orm $orm)
    {
        $this->identityRepository = $orm->identity;
    }


    /**
     * @param $id
     * @return mixed|\Nextras\Orm\Entity\IEntity|null
     */
    public function getById($id)
    {
        return $this->identityRepository->getById($id);
    }


    /**
     * Get Identity entity from Repository based on anotner Identity entity.
     *
     * @param Identity $identityTemplate
     * @return Identity
     * @throws IdentityNotFoundException
     */
    public function getIdentityByIdentity(Identity $identityTemplate)
    {
        /** @var Identity $identity */
        $identity = $this->identityRepository->getBy(
            ['key' => $identityTemplate->key, 'platform' => $identityTemplate->platform]
        );

        if ($identity === null) {
            throw new IdentityNotFoundException('Identity not found');
        }

        return $identity;
    }


    /**
     * @param Identity $identity
     * @param bool $withCascade
     */
    public function save(Identity $identity, $withCascade = true)
    {
        $this->identityRepository->persistAndFlush($identity, $withCascade);
    }
}
