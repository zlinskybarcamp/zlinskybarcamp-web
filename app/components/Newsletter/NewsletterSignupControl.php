<?php

namespace App\Components\Newsletter;

use App\Model\DuplicateNameException;
use App\Model\NewsletterSignupManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

class NewsletterSignupControl extends Control
{
    /**
     * @var NewsletterSignupManager
     */
    private $manager;


    /**
     * NewsletterSignupControl constructor.
     * @param NewsletterSignupManager $signupManager
     */
    public function __construct(NewsletterSignupManager $signupManager)
    {
        parent::__construct();
        $this->manager = $signupManager;
    }


    /**
     *
     */
    public function render()
    {
        $this->template->setFile(__DIR__ . '/NewsletterSignup.latte');
        $this->template->render();
    }


    /**
     * @return Form
     */
    protected function createComponentForm()
    {
        $form = new Form();
        $form->addEmail('email', 'E-mail')
            ->setRequired('Zadejte prosím svůj e-mail')
            ->addRule(Form::EMAIL, 'Toto není platná e-mailová adresa');
        $form->addCheckbox('consent', 'Souhlasím se zpracováním osobních údajů de zákona č. 101/2000 Sb.')
            ->setRequired('Pro zpracování Vaší e-mailové adresy potřebujeme Váš souhlas');
        $form->addSubmit('submit', 'Přihlásit odběr');

        $form->onSuccess[] = [$this, 'formSucceeded'];

        return $form;
    }


    /**
     * @param Form $form
     * @param ArrayHash $values
     * @throws \Nette\Application\AbortException
     */
    public function formSucceeded(Form $form, $values)
    {
        try {
            $this->manager->add($values->email, 'Subscribed by newsletter form');
            $this->presenter->flashMessage('Váš e-mail jsme přidali k příjemcům zpráv o Barcampu');
            $this->presenter->redirect(302, ':Homepage:');
        } catch (DuplicateNameException $e) {
            $form['email']->addError("Tento e-mail je již přihlášen.");
        }
    }
}
