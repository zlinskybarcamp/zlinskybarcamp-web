<?php

namespace App\Model;

use App\Orm\Identity;
use App\Orm\User;

class RestoredUserIdentity
{
    /**
     * @var User
     */
    private $user;
    /**
     * @var Identity
     */
    private $identity;


    public function __construct(User $user, Identity $identity)
    {
        $this->user = $user;
        $this->identity = $identity;
    }


    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }


    /**
     * @return Identity
     */
    public function getIdentity()
    {
        return $this->identity;
    }
}
