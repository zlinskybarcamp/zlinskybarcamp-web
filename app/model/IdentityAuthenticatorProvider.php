<?php

namespace App\Model;

use Nette\ArgumentOutOfRangeException;

/**
 * Class IdentityAuthenticatorProvider
 * @package App\Model
 */
class IdentityAuthenticatorProvider
{
    /** @var IIdentityAuthenticator[] */
    private $authenticators;


    /**
     * IdentityAuthenticatorProvider constructor.
     * @param FacebookIdentityAuthenticator $facebook
     */
    public function __construct(
        FacebookIdentityAuthenticator $facebook
    ) {
        $this->authenticators['facebook'] = $facebook;
    }


    /**
     * @param string $platform
     * @return IIdentityAuthenticator
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
