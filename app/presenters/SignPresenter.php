<?php

namespace App\Presenters;

use App\Forms;
use Nette\Application\UI\Form;

class SignPresenter extends BasePresenter
{
    /** @persistent */
    public $backlink = '';

    /** @var Forms\SignInFormFactory */
    private $signInFactory;

    /** @var Forms\SignUpFormFactory */
    private $signUpFactory;

    /** @var Forms\RegisterConfereeForm */
    private $registerConfereeForm;
    /**
     * @var Forms\RegisterTalkForm
     */
    private $registerTalkForm;


    /**
     * SignPresenter constructor.
     * @param Forms\SignInFormFactory $signInFactory
     * @param Forms\SignUpFormFactory $signUpFactory
     * @param Forms\RegisterConfereeForm $registerConfereeForm
     * @param Forms\RegisterTalkForm $registerTalkForm
     */
    public function __construct(
        Forms\SignInFormFactory $signInFactory,
        Forms\SignUpFormFactory $signUpFactory,
        Forms\RegisterConfereeForm $registerConfereeForm,
        Forms\RegisterTalkForm $registerTalkForm
    ) {
        parent::__construct();
        $this->signInFactory = $signInFactory;
        $this->signUpFactory = $signUpFactory;
        $this->registerConfereeForm = $registerConfereeForm;
        $this->registerTalkForm = $registerTalkForm;
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


    public function actionOut()
    {
        $this->getUser()->logout();
    }


    protected function createComponentRegisterConfereeForm()
    {
        return $this->registerConfereeForm->create(function () {
            $this->flashMessage('Právě jste se zaregistrovali na Barcamp!');
            $this->redirect('Homepage:');
        });
    }


    protected function createComponentRegisterTalkForm()
    {
        return $this->registerTalkForm->create(function () {
            $this->flashMessage('Hurá! Mate zapasanou přednášku, díky!');
            $this->redirect('Homepage:');
        });
    }
}
