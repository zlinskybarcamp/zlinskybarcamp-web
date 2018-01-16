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
     * SignPresenter constructor.
     * @param Forms\SignInFormFactory $signInFactory
     * @param Forms\SignUpFormFactory $signUpFactory
     * @param Forms\RegisterConfereeForm $registerConfereeForm
     */
    public function __construct(
        Forms\SignInFormFactory $signInFactory,
        Forms\SignUpFormFactory $signUpFactory,
        Forms\RegisterConfereeForm $registerConfereeForm
    ) {
        parent::__construct();
        $this->signInFactory = $signInFactory;
        $this->signUpFactory = $signUpFactory;
        $this->registerConfereeForm = $registerConfereeForm;
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
}
