<?php

namespace App\Model\Authenticator;

use App\Orm\Identity;
use App\Orm\User;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook as FacebookSDK;
use Facebook\Helpers\FacebookRedirectLoginHelper;
use Nette\Http\IRequest;
use Nette\Security\AuthenticationException;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Tracy\Debugger;
use Tracy\ILogger;

/**
 * Class FacebookIdentityAuthenticator
 * @package App\Model
 */
class Facebook implements IAuthenticator
{

    const PLATFORM_ID = 'facebook';

    /**
     * @var FacebookSDK
     */
    private $facebook;


    /**
     * FacebookIdentityAuthenticator constructor.
     * @param string $config
     * @throws FacebookSDKException
     */
    public function __construct($config)
    {
        $this->facebook = new FacebookSDK($config);
    }


    /**
     * @param string $callbackUrl
     * @param string|null $backlink
     * @return string
     * @throws \Nette\Utils\JsonException
     * @throws FacebookSDKException
     */
    public function getLoginUrl($callbackUrl, $backlink = null)
    {
        $helper = $this->facebook->getRedirectLoginHelper();

        $helper->getPersistentDataHandler()->set('state', Json::encode([
            'backlink' => $backlink,
            'callback' => $callbackUrl,
            'csrf' => $helper->getPseudoRandomStringGenerator()->getPseudoRandomString(32),
        ]));

        $permissions = ['email'];
        $loginUrl = $helper->getLoginUrl($callbackUrl, $permissions);
        return $loginUrl;
    }


    /**
     * @param IRequest $request
     * @return Identity
     * @throws AuthenticationException
     * @throws \Nette\Utils\JsonException
     */
    public function authenticate(IRequest $request)
    {
        $helper = $this->facebook->getRedirectLoginHelper();

        $accessToken = $this->getAccessToken($helper);
        $user = $this->getUserProfile($accessToken);

        $identity = new Identity();

        $identity->key = $user['id'];
        $identity->platform = self::PLATFORM_ID;
        $identity->identity = Json::encode($user->asArray());
        $identity->token = $accessToken;

        return $identity;
    }


    /**
     * @param IRequest $request
     * @param string|null $default
     * @return string|null
     */
    public function getBacklink(IRequest $request, $default = null)
    {
        $helper = $this->facebook->getRedirectLoginHelper();

        try {
            $state = Json::decode($helper->getPersistentDataHandler()->get('state'), Json::FORCE_ARRAY);
        } catch (JsonException $e) {
            return $default;
        }

        if (isset($state['backlink'])) {
            return (string)$state['backlink'];
        }

        return $default;
    }


    /**
     * Copy base user properties from Identity to User
     *
     * @param User $user
     * @param Identity $identityEntity
     * @return void
     * @throws \Nette\Utils\JsonException
     */
    public function fillUserWithIdentity(User $user, Identity $identityEntity)
    {
        $properties = Json::decode($identityEntity->identity, Json::FORCE_ARRAY);

        $user->name = isset($properties['name']) ? $properties['name'] : null;
        $user->email = isset($properties['email']) ? $properties['email'] : null;
        $user->pictureUrl = isset($properties['picture']['url']) ? $properties['picture']['url'] : null;
    }


    /**
     * @param FacebookRedirectLoginHelper $helper
     * @return string
     * @throws AuthenticationException
     */
    private function getAccessToken(FacebookRedirectLoginHelper $helper)
    {
        try {
            $callbackUrl = $this->getCallbackUrl();
            $accessToken = $helper->getAccessToken($callbackUrl);
        } catch (FacebookSDKException $e) {
            Debugger::log($e, ILogger::EXCEPTION);
            throw new AuthenticationException('Autorizace na API selhalo', 0, $e);
        }

        if (!isset($accessToken)) {
            $message = 'Facebook API error: 400 Bad Request';
            $level = ILogger::ERROR;
            if ($helper->getError()) {
                $message = sprintf(
                    "Facebook Login Error: (%d) %s\n   Reason: %s\n   Description: %s",
                    $helper->getErrorCode(),
                    $helper->getError(),
                    $helper->getErrorReason(),
                    $helper->getErrorDescription()
                );
                $level = ILogger::WARNING;
            }
            Debugger::log($message, $level);
            throw new AuthenticationException($message);
        }

        return (string)$accessToken;
    }


    /**
     * Get callbackUrl from saved state value
     * @return null|string
     */
    protected function getCallbackUrl()
    {
        $helper = $this->facebook->getRedirectLoginHelper();

        try {
            $state = Json::decode($helper->getPersistentDataHandler()->get('state'), Json::FORCE_ARRAY);
        } catch (JsonException $e) {
            return null;
        }

        if (isset($state['callback'])) {
            return (string)$state['callback'];
        }

        return null;
    }


    /**
     * @param string $accesToken
     * @return \Facebook\GraphNodes\GraphUser
     * @throws AuthenticationException
     */
    private function getUserProfile($accesToken)
    {
        try {
            $response = $this->facebook->get(
                'me?fields=id,name,email,picture.width(200).height(200),verified',
                $accesToken
            );
            $user = $response->getGraphUser();
            return $user;
        } catch (FacebookSDKException $e) {
            Debugger::log($e, ILogger::EXCEPTION);
            throw new AuthenticationException('Nepovedlo se z API získat informace o užiateli', 0, $e);
        }
    }
}
