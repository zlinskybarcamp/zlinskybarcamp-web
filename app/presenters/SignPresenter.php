<?php

namespace App\Presenters;

use App\Forms;
use App\Model\ConfereeManager;
use App\Model\IdentityAuthenticatorProvider;
use App\Model\IdentityManager;
use App\Model\IdentityNotFoundException;
use App\Model\UserManager;
use App\Orm\Conferee;
use App\Orm\Identity;
use App\Orm\User;
use Nette\Application\UI\Form;
use Nette\Http\IResponse;
use Nette\Http\Response;
use Nette\Utils\Random;
use Nextras\Orm\Entity\Entity;
use Tracy\Debugger;
use Tracy\Logger;

class SignPresenter extends BasePresenter
{
    /** @persistent */
    public $backlink = '';

    /** @persistent */
    public $token = '';

    /** @var Forms\SignInFormFactory */
    private $signInFactory;

    /** @var Forms\SignUpFormFactory */
    private $signUpFactory;

    /** @var Forms\ConfereeForm */
    private $registerConfereeForm;
    /** @var Forms\TalkForm */
    private $registerTalkForm;

    /**
     * @var IdentityAuthenticatorProvider
     */
    private $identityAuthenticatorProvider;
    /**
     * @var IdentityManager
     */
    private $identityManager;
    /**
     * @var ConfereeManager
     */
    private $confereeManager;
    /**
     * @var UserManager
     */
    private $userManager;


    /**
     * SignPresenter constructor.
     * @param IdentityAuthenticatorProvider $identityAuthenticatorProvider
     * @param Forms\SignInFormFactory $signInFactory
     * @param Forms\SignUpFormFactory $signUpFactory
     * @param Forms\ConfereeForm $registerConfereeForm
     * @param Forms\TalkForm $registerTalkForm
     * @param IdentityManager $identityManager
     * @param ConfereeManager $confereeManager
     * @param UserManager $userManager
     */
    public function __construct(
        IdentityAuthenticatorProvider $identityAuthenticatorProvider,
        Forms\SignInFormFactory $signInFactory,
        Forms\SignUpFormFactory $signUpFactory,
        Forms\ConfereeForm $registerConfereeForm,
        Forms\TalkForm $registerTalkForm,
        IdentityManager $identityManager,
        ConfereeManager $confereeManager,
        UserManager $userManager

    ) {
        parent::__construct();
        $this->signInFactory = $signInFactory;
        $this->signUpFactory = $signUpFactory;
        $this->registerConfereeForm = $registerConfereeForm;
        $this->registerTalkForm = $registerTalkForm;
        $this->identityAuthenticatorProvider = $identityAuthenticatorProvider;
        $this->identityManager = $identityManager;
        $this->confereeManager = $confereeManager;
        $this->userManager = $userManager;
    }


    /**
     * @param string $platform
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function handleFederated($platform)
    {
        $callbackUrl = $this->link('//callback!', ['platform' => $platform]);
        $loginUrl = $this->getAuthenticator($platform)->getLoginUrl($callbackUrl);
        $this->redirectUrl($loginUrl, Response::S303_POST_GET);
    }


    /**
     * @param string $platform
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Security\AuthenticationException
     * @throws \Nette\Utils\JsonException
     */
    public function handleCallback($platform)
    {
        $authenticator = $this->getAuthenticator($platform);
        $identity = $authenticator->authenticate($this->getHttpRequest());

        try {
            $identity = $this->identityManager->getIdentityByIdentity($identity);
        } catch (IdentityNotFoundException $e) {
            $this->identityManager->save($identity);
        }

        $user = $identity->user;

        if ($user) {
            $this->login($user);
            $this->restoreRequest($this->backlink);
            $this->redirect(IResponse::S303_POST_GET, 'Homepage:');
        } else {
            $user = new User();
            $authenticator->fillUserWithIdentity($user, $identity);

            $this->storeEntity($identity, Identity::class);
            $this->storeEntity($user, User::class);
            $this->redirect(IResponse::S303_POST_GET, 'conferee');
        }
    }


    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function renderConferee()
    {
        /** @var Identity|null $identity */
        $identity = $this->restoreEntity(Identity::class);

        if ($identity instanceof Identity === false) {
            $this->flashMessage('Pro účast na Barcampu se prosím nejdříve přihlaste nebo registrujte');
            $this->redirect(Response::S303_SEE_OTHER, 'in');
        }

        $user = $identity->user;

        if ($user instanceof User === false) {
            /** @var User|null $user */
            $user = $this->restoreEntity(User::class);
        }

        if ($user instanceof User === false) {
            Debugger::log('Při obnovení profilu pro dokončení registraci se nezachoval User', Logger::ERROR);
            $this->error('Chyba konzistence dat', IResponse::S500_INTERNAL_SERVER_ERROR);
        }

        /** @var Form $form */
        $form = $this['registerConfereeForm'];
        $form->setDefaults([
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }


    /**
     *
     */
    public function actionOut()
    {
        $this->getUser()->logout();
    }


    /**
     * Sign-in form factory.
     * @return Form
     */
    protected function createComponentSignInForm()
    {
        return $this->signInFactory->create(function () {
            $this->restoreRequest($this->backlink);
            $this->redirect('Homepage:');
        });
    }


    /**
     * Sign-up form factory.
     * @return Form
     */
    protected function createComponentSignUpForm()
    {
        return $this->signUpFactory->create(function () {
            $this->redirect('Homepage:');
        });
    }


    /**
     * @return Form
     */
    protected function createComponentRegisterConfereeForm()
    {
        /**
         * @param Conferee $conferee
         * @throws \Nette\Application\AbortException
         * @throws \Nette\Application\BadRequestException
         * @throws \Nette\Security\AuthenticationException
         */
        $onSubmitCallback = function ($conferee) {

            /** @var Identity|null $identity */
            $identity = $this->restoreEntity(Identity::class);

            if ($identity instanceof Identity === false) {
                $this->flashMessage('Pro účast na Barcampu se prosím nejdříve přihlaste nebo registrujte');
                $this->redirect(Response::S303_SEE_OTHER, 'in');
            }

            $user = $identity->user;

            if ($user instanceof User === false) {
                /** @var User|null $user */
                $user = $this->restoreEntity(User::class);
            }

            if ($user instanceof User === false) {
                Debugger::log('Při obnovení profilu pro dokončení registraci se nezachoval User', Logger::ERROR);
                $this->error('Chyba konzistence dat', IResponse::S500_INTERNAL_SERVER_ERROR);
            }

            $user->name = $conferee->name;
            $user->email = $conferee->email;

            $identity->user = $user;

            $conferee->pictureUrl = $user->pictureUrl;
            $conferee->user = $user;

            $this->userManager->save($user);

            $this->login($user);
            $this->removePartialLoginSession();

            $this->flashMessage('Právě jste se zaregistrovali na Barcamp!');
            $this->restoreRequest($this->backlink);
            $this->redirect('Homepage:');
        };

        return $this->registerConfereeForm->create($onSubmitCallback);
    }


    /**
     * @return Form
     */
    protected function createComponentRegisterTalkForm()
    {
        return $this->registerTalkForm->create(function () {
            $this->flashMessage('Hurá! Mate zapasanou přednášku, díky!');
            $this->redirect('Homepage:');
        });
    }


    /**
     * @param User $user
     * @throws \Nette\Security\AuthenticationException
     */
    private function login(User $user)
    {
        $appIdentity = new \Nette\Security\Identity(
            $user->id,
            [],
            [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'pictureUrl' => $user->pictureUrl,
            ]
        );

        $this->user->login($appIdentity);
    }


    /**
     * @param string $platform
     * @return \App\Model\IIdentityAuthenticator
     */
    private function getAuthenticator($platform)
    {
        return $this->identityAuthenticatorProvider->provide($platform);
    }


    /**
     * @param Entity $entity
     * @param string $key
     */
    private function storeEntity(Entity $entity, $key)
    {
        $session = $this->getPartialLoginSession(true);

        $session->{$key} = [
            'class' => get_class($entity),
            'entity' => $entity->serialize()
        ];
    }


    /**
     * @param string $key
     * @return Entity|null
     */
    private function restoreEntity($key)
    {
        $session = $this->getPartialLoginSession();
        if ($session === null || isset($session->{$key}) === false) {
            return null;
        }

        $entityPack = $session->{$key};
        $class = $entityPack['class'];

        /** @var Entity $entity */
        $entity = new $class();
        $entity->unserialize($entityPack['entity']);

        return $entity;
    }


    /**
     * @param bool $create
     * @return \Nette\Http\Session|\Nette\Http\SessionSection|null
     */
    private function getPartialLoginSession($create = false)
    {
        if (!$this->token) {
            if ($create) {
                $this->token = Random::generate(5);
            } else {
                return null;
            }
        }

        $session = $this->getSession('part-login-storage/' . $this->token);
        $session->setExpiration('15 minutes');
        return $session;
    }


    private function removePartialLoginSession()
    {
        if ($session = $this->getPartialLoginSession()) {
            $session->remove();
            $this->token = '';
        }
    }

}
