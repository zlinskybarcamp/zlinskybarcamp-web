<?php

namespace App\Forms;

use App\Model\AuthenticationException;
use App\Model\Authenticator\Email as EmailAuthenticator;
use App\Model\PasswordMismatchException;
use App\Model\UserNotFoundException;
use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;
use Nette\Utils\Html;

class SignInFormFactory
{
    use Nette\SmartObject;

    /** @var FormFactory */
    private $factory;

    /** @var User */
    private $user;

    /** @var EmailAuthenticator */
    private $authenticator;
    /**
     * @var IPresenter
     */
    private $presenter;


    public function __construct(IPresenter $presenter, FormFactory $factory, EmailAuthenticator $authenticator)
    {
        $this->factory = $factory;
        $this->authenticator = $authenticator;
        $this->presenter = $presenter;
    }


    /**
     * @param callable $onSuccess
     * @return Form
     */
    public function create(callable $onSuccess)
    {
        $form = $this->factory->create();
        $form->addText('email', 'E-mail:')
            ->setRequired('Prosím, vyplňte e-mail');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím, zadejte heslo');

        $form->addSubmit('send', 'Přihlásit')
            ->setOption('itemClass', 'text-center')
            ->getControlPrototype()->setName('button')->setText('Přihlásit');

        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            try {
                $identity = $this->authenticator->getIdentityByAuth($values->email, $values->password);
                $onSuccess($identity);
            } catch (AuthenticationException $e) {
                $form->addError('Omlouvámě se, přihlášení se nepovedlo. Zkontrolujte prosím zadané údaje.');
                if ($e instanceof UserNotFoundException) {
                    //$link = $this->
                    $form['email']->addError(Html::el()
                        ->addText('Tento e-mail jsme u nás nenalezli. Nechtěli jste se nejdříve ')
                        ->addHtml(Html::el('a')->href($this->presenter->link(':Sign:up'))->addText('registrovat'))
                        ->addText('?'));
                }
                if ($e instanceof PasswordMismatchException) {
                    $form['password']->addError('Heslo se neshoduje, zkuste to prosím znovu.');
                }
            }
        };

        return $form;
    }
}

