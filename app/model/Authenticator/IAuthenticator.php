<?php

namespace App\Model\Authenticator;

use App\Orm\Identity;
use App\Orm\User;
use Nette\Http\IRequest;
use Nette\Security\AuthenticationException;

/**
 * Interface IIdentityAuthenticator
 * @package App\Model
 */
interface IAuthenticator
{
    /**
     * @param string $callbackUrl
     * @param string|null $backlink
     * @return string mixed
     */
    public function getLoginUrl($callbackUrl, $backlink = null);


    /**
     * @param IRequest $request
     * @return Identity
     * @throws AuthenticationException
     */
    public function authenticate(IRequest $request);


    /**
     * @param IRequest $request
     * @param string|null $default
     * @return string|null
     */
    public function getBacklink(IRequest $request, $default = null);


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
