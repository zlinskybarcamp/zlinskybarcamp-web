<?php

namespace App\Presenters;

use App\Forms;
use App\Model\AvatarStorage;
use App\Model\ConfereeManager;
use App\Model\ConfereeNotFound;
use App\Model\EventInfoProvider;
use App\Model\NoUserLoggedIn;
use App\Model\TalkManager;
use App\Model\TalkNotFound;
use App\Model\UserManager;
use App\Model\UserNotFound;
use App\Orm\Conferee;
use App\Orm\Talk;
use Nette\Application\UI\Form;
use Nette\Http\FileUpload;
use Nette\Http\IResponse;
use Tracy\Debugger;
use Tracy\ILogger;

class UserPresenter extends BasePresenter
{
    /**
     * @var UserManager
     */
    private $userManager;
    /**
     * @var ConfereeManager
     */
    private $confereeManager;
    /**
     * @var Forms\ConfereeForm
     */
    private $confereeForm;
    /**
     * @var Forms\TalkForm
     */
    private $talkForm;
    /**
     * @var TalkManager
     */
    private $talkManager;
    /**
     * @var EventInfoProvider
     */
    private $eventInfoProvider;
    /**
     * @var AvatarStorage
     */
    private $avatarStorage;


    /**
     * ConferencePresenter constructor.
     * @param UserManager $userManager
     * @param ConfereeManager $confereeManager
     * @param TalkManager $talkManager
     * @param Forms\ConfereeForm $confereeForm
     * @param Forms\TalkForm $talkForm
     * @param EventInfoProvider $eventInfoProvider
     * @param AvatarStorage $avatarStorage
     */
    public function __construct(
        UserManager $userManager,
        ConfereeManager $confereeManager,
        TalkManager $talkManager,
        Forms\ConfereeForm $confereeForm,
        Forms\TalkForm $talkForm,
        EventInfoProvider $eventInfoProvider,
        AvatarStorage $avatarStorage
    ) {
        $this->userManager = $userManager;
        $this->confereeManager = $confereeManager;
        $this->confereeForm = $confereeForm;
        $this->talkForm = $talkForm;
        $this->talkManager = $talkManager;
        $this->eventInfoProvider = $eventInfoProvider;
        $this->avatarStorage = $avatarStorage;
    }


    /**
     * @throws \Nette\Application\AbortException
     */
    protected function startup()
    {
        parent::startup();
        try {
            $this->userManager->getByLoginUser($this->user);
        } catch (NoUserLoggedIn $e) {
            $backlink = $this->storeRequest();
            $this->redirect(IResponse::S303_SEE_OTHER, ':Sign:conferee', ['backlink' => $backlink]);
        } catch (UserNotFound $e) {
            $this->user->logout();
            $backlink = $this->storeRequest();
            $this->redirect(IResponse::S303_SEE_OTHER, ':Sign:in', ['backlink' => $backlink]);
        }
    }


    /**
     * @throws NoUserLoggedIn
     * @throws UserNotFound
     * @throws \Nette\Utils\JsonException
     */
    public function renderProfil()
    {
        $user = $this->userManager->getByLoginUser($this->user);
        $conferee = $user->conferee;
        $talks = $conferee ? $conferee->talk : [];

        $this->template->conferee = $conferee;
        $this->template->talks = $talks;

        $features = $this->eventInfoProvider->getFeatures();
        $this->template->allowRegisterTalk = $features['talks'];
        $this->template->allowEditTalk = $features['talks_edit'];
    }


    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Utils\JsonException
     */
    public function renderTalk()
    {
        if (!$this->eventInfoProvider->getFeatures()['talks_edit']) {
            $this->flashMessage('Upravování přednášek není v tuto chvíli povoleno, omlouváme se');
            $this->redirect(Response::S303_SEE_OTHER, 'profil');
        }
    }


    /**
     * @return Form
     * @throws ConfereeNotFound
     * @throws NoUserLoggedIn
     * @throws UserNotFound
     */
    protected function createComponentConfereeForm()
    {
        /**
         * @param Conferee $conferee
         * @param $values
         * @throws \Nette\Application\AbortException
         */
        $onSubmitCallback = function (Conferee $conferee, $values) {

            if ($conferee->id != $values->id) {
                Debugger::log(
                    'Security alert: ' . self::class . ':' . __METHOD__ . ' form send invalid $coferee->id',
                    ILogger::ERROR
                );
                throw new \InvalidArgumentException();
            }

            $conferee->user->name = $conferee->name;
            $conferee->user->email = $conferee->email;

            $this->confereeManager->save($conferee);

            $this->flashMessage('Váš profil byl upraven');
            $this->redirect('User:profil');
        };

        $conferee = $this->userManager->getByLoginUser($this->user)->getObligatoryConferee();

        $form = $this->confereeForm->create($onSubmitCallback, $conferee);

        //Additional form modification
        $form->addHidden('id', $conferee->id);
        $form->removeComponent($form['consens']);

        return $form;
    }


    /**
     * @return Form
     * @throws ConfereeNotFound
     * @throws NoUserLoggedIn
     * @throws UserNotFound
     * @throws TalkNotFound
     * @throws \Nette\Utils\JsonException
     */
    protected function createComponentTalkForm()
    {
        /**
         * @param Talk $talk
         * @param $values
         * @throws \Nette\Application\AbortException
         */
        $onSubmitCallback = function (Talk $talk, $values) {

            if ($talk->id != $values->id) {
                Debugger::log(
                    'Security alert: ' . self::class . ':' . __METHOD__ . ' form send invalid $coferee->id',
                    ILogger::ERROR
                );
                throw new \InvalidArgumentException();
            }

            $this->talkManager->save($talk);

            $this->flashMessage('Vaše přednáška byla upravena');
            $this->redirect('User:profil');
        };

        $conferee = $this->userManager->getByLoginUser($this->user)->getObligatoryConferee();

        $talk = null;
        foreach ($conferee->talk as $loopTalk) {
            $talk = $loopTalk;
            break;
        }

        if ($talk === null) {
            throw new TalkNotFound();
        }

        $categories = $this->talkManager->getCategories();
        $durations = $this->talkManager->getDurations();
        $form = $this->talkForm->create($onSubmitCallback, $categories, $durations, $talk);

        //Additional form modification
        $form->addHidden('id', $talk->id);

        return $form;
    }


    /**
     * @throws NoUserLoggedIn
     * @throws UserNotFound
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     * @throws \Nette\Utils\ImageException
     */
    public function handleUploadAvatar()
    {
        $user = $this->userManager->getByLoginUser($this->user);
        $conferee = $user->conferee;

        $files = $this->getRequest()->getFiles();
        if (!isset($files['file'])) {
            Debugger::log('Uploaded empty file', ILogger::WARNING);
            $this->error('Wrong reguest', IResponse::S400_BAD_REQUEST);
        }

        /** @var FileUpload $file */
        $file = $files['file'];

        if (!$file->isOk()) {
            Debugger::log('Uploaded corrupted file', ILogger::WARNING);
            $this->error('Wrong reguest', IResponse::S400_BAD_REQUEST);
        }

        if (!$file->isImage()) {
            Debugger::log('Uploaded non-image file', ILogger::WARNING);
            $this->error('Nelze nahrát jiný soubor než obrázek', IResponse::S403_FORBIDDEN);
        }

        $image = $file->toImage();

        $url = $this->avatarStorage->saveImage($image);

        $user->pictureUrl = $url;
        $conferee->pictureUrl = $url;
        $this->user->getIdentity()->pictureUrl = $url;

        $this->userManager->save($user);

        $this->sendJson(['avatarUrl' => $url]);
    }
}
