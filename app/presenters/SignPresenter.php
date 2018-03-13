<?php

namespace App\Presenters;

use App\Forms;
use App\Model\Authenticator\AuthenticatorProvider;
use App\Model\ConfereeManager;
use App\Model\ConfereeNotFound;
use App\Model\EventInfoProvider;
use App\Model\IdentityManager;
use App\Model\IdentityNotFoundException;
use App\Model\NoUserLoggedIn;
use App\Model\RestoredUserIdentity;
use App\Model\TalkManager;
use App\Model\UserManager;
use App\Model\UserNotFound;
use App\Orm\Conferee;
use App\Orm\Identity;
use App\Orm\Talk;
use App\Orm\User;
use App\Orm\UserRole;
use Nette\Application\UI\Form;
use Nette\Http\IResponse;
use Nette\Http\Response;
use Nette\Security\AuthenticationException;
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
    private $signInFormFactory;

    /** @var Forms\SignUpFormFactory */
    private $signUpFormFactory;

    /** @var Forms\ConfereeForm */
    private $confereeForm;
    /** @var Forms\TalkForm */
    private $talkForm;

    /**
     * @var AuthenticatorProvider
     */
    private $authenticatorProvider;
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
     * @var TalkManager
     */
    private $talkManager;
    /**
     * @var EventInfoProvider
     */
    private $eventInfoProvider;


    /**
     * SignPresenter constructor.
     * @param AuthenticatorProvider $authenticatorProvider
     * @param Forms\SignInFormFactory $signInFactory
     * @param Forms\SignUpFormFactory $signUpFormFactory
     * @param Forms\ConfereeForm $confereeForm
     * @param Forms\TalkForm $talkForm
     * @param IdentityManager $identityManager
     * @param ConfereeManager $confereeManager
     * @param UserManager $userManager
     * @param TalkManager $talkManager
     * @param EventInfoProvider $eventInfoProvider
     */
    public function __construct(
        AuthenticatorProvider $authenticatorProvider,
        Forms\SignInFormFactory $signInFactory,
        Forms\SignUpFormFactory $signUpFormFactory,
        Forms\ConfereeForm $confereeForm,
        Forms\TalkForm $talkForm,
        IdentityManager $identityManager,
        ConfereeManager $confereeManager,
        UserManager $userManager,
        TalkManager $talkManager,
        EventInfoProvider $eventInfoProvider
    ) {
        parent::__construct();
        $this->signInFormFactory = $signInFactory;
        $this->signUpFormFactory = $signUpFormFactory;
        $this->confereeForm = $confereeForm;
        $this->talkForm = $talkForm;
        $this->authenticatorProvider = $authenticatorProvider;
        $this->identityManager = $identityManager;
        $this->confereeManager = $confereeManager;
        $this->userManager = $userManager;
        $this->talkManager = $talkManager;
        $this->eventInfoProvider = $eventInfoProvider;
    }


    /**
     * @param string $platform
     * @throws \Nette\Application\AbortException
     */
    public function actionFederatedLogin($platform)
    {
        $this->getSession()->start();
        $callbackUrl = $this->link('//federatedCallback', ['platform' => $platform, 'backlink' => null]);
        $loginUrl = $this->getAuthenticator($platform)->getLoginUrl($callbackUrl, $this->backlink);
        $this->redirectUrl($loginUrl, Response::S303_POST_GET);
    }


    /**
     * @param string $platform
     * @throws AuthenticationException
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Utils\JsonException
     */
    public function actionFederatedCallback($platform)
    {

        try {
            $authenticator = $this->getAuthenticator($platform);
            $this->backlink = $authenticator->getBacklink($this->getHttpRequest(), $this->backlink);
            $identity = $authenticator->authenticate($this->getHttpRequest());
        } catch (AuthenticationException $e) {
            $this->flashMessage('Omlouváme, přihlášení se nepovedlo. Zkuste to prosím znovu.');
            $this->redirect(IResponse::S303_SEE_OTHER, 'in');
            return;
        }

        try {
            $identity = $this->identityManager->getIdentityByIdentity($identity);
        } catch (IdentityNotFoundException $e) {
            $this->identityManager->save($identity);
        }

        $user = $identity->user;

        if ($user) {
            $this->login($user);
            $this->restoreRequest($this->backlink);
            $this->redirect(IResponse::S303_POST_GET, 'User:profil');
        } else {
            $user = new User();
            $authenticator->fillUserWithIdentity($user, $identity);

            $this->storeEntity($identity, Identity::class);
            $this->storeEntity($user, User::class);
            $this->redirect(IResponse::S303_POST_GET, 'conferee');
        }
    }


    /**
     * @throws UserNotFound
     * @throws \Nette\Application\AbortException
     */
    public function renderIn()
    {
        try {
            $this->userManager->getByLoginUser($this->user);

            //When user loaded - already loggedIn
            $this->redirect(IResponse::S303_SEE_OTHER, 'User:profil');
        } catch (NoUserLoggedIn $e) {
            //Expected state - user must not be logged
        }
    }


    /**
     * @throws UserNotFound
     * @throws \Nette\Application\AbortException
     */
    public function renderUp()
    {
        try {
            $this->userManager->getByLoginUser($this->user);

            //When user loaded - already loggedIn
            $this->redirect(IResponse::S303_SEE_OTHER, 'User:profil');
        } catch (NoUserLoggedIn $e) {
            //Expected state - user must not be logged
        }
    }


    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     * @throws UserNotFound
     * @throws \Nette\Utils\JsonException
     */
    public function renderConferee()
    {
        if (!$this->eventInfoProvider->getFeatures()['conferee']) {
            $this->flashMessage('Registrace ještě nejsou otevřeny, omlouváme se');
            $this->redirect(Response::S303_SEE_OTHER, 'Homepage:');
        }

        $restoredUserIdentity = $this->getRestorableUserIdentity();

        $user = $restoredUserIdentity->getUser();

        /** @var Form $form */
        $form = $this['confereeForm'];
        $form->setDefaults([
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }


    /**
     * @throws UserNotFound
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Utils\JsonException
     */
    public function renderTalk()
    {
        try {
            $conferee = $this->userManager->getByLoginUser($this->user)->getObligatoryConferee();
        } catch (NoUserLoggedIn $e) {
            $this->flashMessage('Pro vypsání přednášky na Barcampu se prosím přihlaste nebo registrujte');
            $this->backlink = $this->storeRequest();
            $this->redirect(Response::S303_SEE_OTHER, 'up');
            return;
        } catch (ConfereeNotFound $e) {
            $this->flashMessage('Pro vypsání přednášky se nejdříve registrujte jako účastník');
            $this->backlink = $this->storeRequest();
            $this->redirect(Response::S303_SEE_OTHER, 'conferee');
            return;
        }

        if ($conferee->talk->count() > 0) {
            $this->flashMessage('Momentíček, Vy už přece máte přednášku vypsanou :)');
            $this->redirect(IResponse::S303_SEE_OTHER, 'User:talk');
        }

        if (!$this->eventInfoProvider->getFeatures()['talks']) {
            $this->flashMessage('Vypisování přednášek není v tuto chvíli povoleno, omlouváme se');
            $this->redirect(Response::S303_SEE_OTHER, 'Homepage:');
        }
    }


    /**
     *
     * @throws \Nette\Application\AbortException
     */
    public function actionOut()
    {
        $this->getUser()->logout();

        $this->flashMessage('Jste odhlášeni');
        $this->redirect(IResponse::S303_SEE_OTHER, 'Homepage:');
    }


    /**
     * Sign-in form factory.
     * @return Form
     */
    protected function createComponentSignInForm()
    {
        return $this->signInFormFactory->create(function (Identity $identity) {
            $user = $identity->user;
            if (!$user instanceof User) {
                $this->redirect('conferee');
            }

            $this->login($user);

            $this->restoreRequest($this->backlink);
            $this->redirect('User:profil');
        });
    }


    /**
     * Sign-up form factory.
     * @return Form
     */
    protected function createComponentSignUpForm()
    {
        return $this->signUpFormFactory->create(function (Identity $identity) {
            $user = new User();
            $user->email = $identity->key;

            $this->storeEntity($identity, Identity::class);
            $this->storeEntity($user, User::class);
            $this->redirect(IResponse::S303_POST_GET, 'conferee');
        });
    }


    /**
     * @return Form
     */
    protected function createComponentConfereeForm()
    {
        /**
         * @param Conferee $conferee
         * @throws AuthenticationException
         * @throws UserNotFound
         * @throws \Nette\Application\AbortException
         * @throws \Nette\Application\BadRequestException
         */
        $onSubmitCallback = function ($conferee) {

            $restoredUserIdentity = $this->getRestorableUserIdentity();

            $user = $restoredUserIdentity->getUser();
            $identity = $restoredUserIdentity->getIdentity();

            $this->userManager->save($user);
            $this->identityManager->save($identity);
            $this->confereeManager->save($conferee);

            $user->name = $conferee->name;
            $user->email = $conferee->email;

            //Currently not working on weird ORM bug
            $identity->user = $user;

            $conferee->pictureUrl = $user->pictureUrl;
            $conferee->user = $user;

            $this->userManager->save($user);

            //dirty hack to weird ORM bug
            $identity->setRawValue('user', $user->id);
            $this->identityManager->save($identity, false);
            //hack end

            $user->addRole('conferee');
            $this->userManager->save($user);

            $this->login($user);
            $this->removePartialLoginSession();

            $this->flashMessage('Právě jste se zaregistrovali na Barcamp!');
            $this->restoreRequest($this->backlink);
            $this->redirect('User:profil');
        };

        return $this->confereeForm->create($onSubmitCallback);
    }


    /**
     * @return Form
     * @throws \App\Model\InvalidEnumeratorSetException
     * @throws \Nette\Utils\JsonException
     */
    protected function createComponentTalkForm()
    {
        /**
         * @param Talk $talk
         * @throws ConfereeNotFound
         * @throws NoUserLoggedIn
         * @throws UserNotFound
         * @throws \Nette\Application\AbortException
         * @throws \Nette\Security\AuthenticationException
         */
        $onSubmitCallback = function (Talk $talk) {
            $conferee = $this->userManager->getByLoginUser($this->user)->getObligatoryConferee();

            $talk->conferee = $conferee;
            $conferee->user->addRole('speaker');

            $this->talkManager->save($talk);

            //Reset app login - to reload roles
            $this->login($conferee->user);

            $this->flashMessage('Hurá! Mate zapasanou přednášku, díky!');
            $this->redirect('User:profil');
        };

        $categories = $this->talkManager->getCategories();
        $durations = $this->talkManager->getDurations();

        return $this->talkForm->create($onSubmitCallback, $categories, $durations);
    }


    /**
     * @param User $user
     * @throws \Nette\Security\AuthenticationException
     */
    private function login(User $user)
    {
        $roles = [];
        /** @var UserRole $role */
        foreach ($user->role as $role) {
            $roles[] = $role->role;
        }


        $appIdentity = new \Nette\Security\Identity(
            $user->id,
            $roles,
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
     * @return \App\Model\Authenticator\IAuthenticator
     */
    private function getAuthenticator($platform)
    {
        return $this->authenticatorProvider->provide($platform);
    }


    /**
     * Get User & Identity from restored object (created by partial login) or load it from logged user
     * @return RestoredUserIdentity
     * @throws UserNotFound
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    protected function getRestorableUserIdentity()
    {
        $user = null;

        try {
            $user = $this->userManager->getByLoginUser($this->user);
            if ($user->conferee) {
                $this->redirect(IResponse::S303_SEE_OTHER, 'User:profil');
            }
        } catch (NoUserLoggedIn $e) {
            // Reuired exception, no action
        }

        /** @var Identity|null $identity */
        $identity = $this->restoreEntity(Identity::class);

        if ($identity instanceof Identity === false && $user instanceof User) {
            $identities = $user->identity;

            if ($identities->count() > 0) {
                foreach ($identities as $oneIdentity) {
                    $identity = $oneIdentity;
                    break;
                }
            }
        }

        if ($identity instanceof Identity === false) {
            $this->flashMessage('Pro účast na Barcampu se prosím nejdříve přihlaste nebo registrujte');
            $this->redirect(Response::S303_SEE_OTHER, 'up');
        }

        if ($user instanceof User === false) {
            $user = $identity->user;
        }

        if ($user instanceof User === false) {
            /** @var User|null $user */
            $user = $this->restoreEntity(User::class);
        }

        if ($user instanceof User === false) {
            Debugger::log('Při obnovení profilu pro dokončení registraci se nezachoval User', Logger::ERROR);
            $this->error('Chyba konzistence dat', IResponse::S500_INTERNAL_SERVER_ERROR);
        }

        return new RestoredUserIdentity($user, $identity);
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
