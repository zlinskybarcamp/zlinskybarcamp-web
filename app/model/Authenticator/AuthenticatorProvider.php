<?php

namespace App\Model\Authenticator;

use Nette\ArgumentOutOfRangeException;

/**
 * Class IdentityAuthenticatorProvider
 * @package App\Model
 */
class AuthenticatorProvider
{
    /** @var IAuthenticator[] */
    private $authenticators;


    /**
     * IdentityAuthenticatorProvider constructor.
     * @param Facebook $facebook
     */
    public function __construct(
        Facebook $facebook
    ) {
        $this->authenticators['facebook'] = $facebook;
    }


    /**
     * @param string $platform
     * @return IAuthenticator
     * @throws ArgumentOutOfRangeException
     */
    public function provide($platform)
    {
        if (!isset($this->authenticators[$platform])) {
            $allowed = join('", "', array_keys($this->authenticators));
            throw new ArgumentOutOfRangeException(
                sprintf('Unknown authenticator "%s", use one of these: "%s".', $platform, $allowed)
            );
        }

        return $this->authenticators[$platform];
    }
}
