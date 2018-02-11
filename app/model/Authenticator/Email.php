<?php

namespace App\Model\Authenticator;

use App\Model\DuplicateNameException;
use App\Model\IdentityManager;
use App\Model\IdentityNotFoundException;
use App\Model\PasswordMismatchException;
use App\Model\UserNotFoundException;
use App\Orm\Identity;

class Email
{
    const PLATFORM_KEY = 'email';

    /**
     * @var IdentityManager
     */
    private $identityManager;


    /**
     * Email constructor.
     * @param IdentityManager $identityManager
     */
    public function __construct(IdentityManager $identityManager)
    {
        $this->identityManager = $identityManager;
    }


    /**
     * @param $email
     * @param $password
     * @return Identity
     * @throws PasswordMismatchException
     * @throws UserNotFoundException
     */
    public function getIdentityByAuth($email, $password)
    {
        try {
            /** @var Identity $identity */
            $identity = $this->getIdentityByEmail($email);
        } catch (IdentityNotFoundException $e) {
            throw new UserNotFoundException();
        }

        $this->verifyIdentityPassword($identity, $password);

        return $identity;
    }


    /**
     * @param Identity $identity
     * @param string $password
     * @throws PasswordMismatchException
     */
    protected function verifyIdentityPassword(Identity $identity, $password)
    {
        if (password_verify($password, $identity->token) === false) {
            throw new PasswordMismatchException();
        }
    }


    /**
     * @param string $email
     * @param string $password
     * @return Identity
     * @throws DuplicateNameException
     */
    public function createNewIdentity($email, $password)
    {
        $identity = null;

        try {
            /** @var Identity $identity */
            $identity = $this->getIdentityByEmail($email);
        } catch (IdentityNotFoundException $e) {
            // required
        }

        if ($identity instanceof Identity) {
            throw new DuplicateNameException();
        }

        $identity = new Identity();

        $identity->platform = self::PLATFORM_KEY;
        $identity->key = $email;
        $identity->token = password_hash($password, PASSWORD_DEFAULT);

        return $identity;
    }


    /**
     * @param string $email
     * @return Identity
     * @throws IdentityNotFoundException
     */
    protected function getIdentityByEmail($email)
    {
        return $this->identityManager->getIdentity(self::PLATFORM_KEY, $email);
    }

}
