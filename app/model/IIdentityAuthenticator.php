<?php

namespace App\Model;

use App\Orm\User;
use App\Orm\Identity;
use Nette\Http\IRequest;

/**
 * Interface IIdentityAuthenticator
 * @package App\Model
 */
interface IIdentityAuthenticator
{
    /**
     * @param string $callbackUrl
     * @return string mixed
     */
    public function getLoginUrl($callbackUrl);


    /**
     * @param IRequest $request
     * @return Identity
     */
    public function authenticate(IRequest $request);


    /**
     * Copy base user properties from Identity to User
     *
     * @param User $user
     * @param Identity $identityEntity
     * @return void
     * @throws \Nette\Utils\JsonException
     */
    public function fillUserWithIdentity(User $user, Identity $identityEntity);
}
