<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;

class SignInFormFactory
{
    use Nette\SmartObject;

    /** @var FormFactory */
    private $factory;

    /** @var User */
    private $user;


    public function __construct(FormFactory $factory, User $user)
    {
        $this->factory = $factory;
        $this->user = $user;
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
                $this->user->setExpiration($values->remember ? '14 days' : '20 minutes');
                $this->user->login($values->email, $values->password);
            } catch (Nette\Security\AuthenticationException $e) {
                $form->addError('The username or password you entered is incorrect.');
                return;
            }
            $onSuccess();
        };

        return $form;
    }
}

