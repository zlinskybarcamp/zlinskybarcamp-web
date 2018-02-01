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
     * @return string
     */
    public function getLoginUrl($callbackUrl)
    {
        $helper = $this->facebook->getRedirectLoginHelper();

        $permissions = ['email', 'user_about_me'];
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

        $userDetails = $user->asArray();
        if (isset($userDetails['about'])) {
            $userDetails['bio'] = $userDetails['about'];
        }

        $identity = new Identity();

        $identity->key = $user['id'];
        $identity->platform = self::PLATFORM_ID;
        $identity->identity = Json::encode($userDetails);
        $identity->token = $accessToken;

        return $identity;
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
            $accessToken = $helper->getAccessToken();
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
