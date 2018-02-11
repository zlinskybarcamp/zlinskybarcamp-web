<?php

namespace App\Model;

use App\Orm\Identity;
use App\Orm\Orm;


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
        return $this->getIdentity($identityTemplate->platform, $identityTemplate->key);
    }


    /**
     * @param string $platform Platform name for search
     * @param string $key Identity key for search
     * @return Identity
     * @throws IdentityNotFoundException
     */
    public function getIdentity($platform, $key)
    {
        $identity = $this->identityRepository->getBy([
            'key' => $key,
            'platform' => $platform
        ]);

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
