<?php

namespace App\Forms;

use App\Model\Authenticator\Email as EmailAuthenticator;
use App\Model\DuplicateNameException;
use Nette;
use Nette\Application\UI\Form;

class SignUpFormFactory
{
    use Nette\SmartObject;

    const PASSWORD_MIN_LENGTH = 5;

    /** @var FormFactory */
    private $factory;

    /** @var EmailAuthenticator */
    private $authenticator;


    /**
     * SignUpFormFactory constructor.
     * @param FormFactory $factory
     * @param EmailAuthenticator $authenticator
     */
    public function __construct(FormFactory $factory, EmailAuthenticator $authenticator)
    {
        $this->factory = $factory;
        $this->authenticator = $authenticator;
    }


    /**
     * @param callable $onSuccess
     * @return Form
     */
    public function create(callable $onSuccess)
    {
        $form = $this->factory->create();
        $form->addEmail('email', 'Váš e-mail:')
            ->setRequired('Prosím zadejte svůj e-mail');

        $form->addPassword('password', 'Vytvořte si heslo:')
            ->setOption('description', sprintf('alespoň %d znaků', self::PASSWORD_MIN_LENGTH))
            ->setRequired('Vytvořte si prosím heslo')
            ->addRule($form::MIN_LENGTH, null, self::PASSWORD_MIN_LENGTH);

        $form->addSubmit('send', 'Registrovat')
            ->setOption('itemClass', 'text-center')
            ->getControlPrototype()->setName('button')->setText('Registrovat');

        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            try {
                $identity = $this->authenticator->createNewIdentity($values->email, $values->password);
                $onSuccess($identity);
            } catch (DuplicateNameException $e) {
                $form['email']->addError('Tento e-mail už u nás máte, zkuste se příhlásit');
                return;
            }
        };

        return $form;
    }
}
