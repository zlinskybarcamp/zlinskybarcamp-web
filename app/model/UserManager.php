<?php

namespace App\Model;

use App\Orm\Orm;
use App\Orm\User;
use App\Orm\UserRepository;

/**
 * Users management.
 */
class UserManager
{
    /** @var UserRepository */
    private $userRepository;


    /**
     * UserManager constructor.
     * @param Orm $orm
     */
    public function __construct(Orm $orm)
    {
        $this->userRepository = $orm->user;
    }


    /**
     * @param $id
     * @return User|null
     */
    public function getById($id)
    {
        return $this->userRepository->getById($id);
    }


    /**
     * @param User $user
     */
    public function save(User $user)
    {
        $this->userRepository->persistAndFlush($user);
    }
}
