<?php

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;

class SignUpFormFactory
{
    use Nette\SmartObject;

    const PASSWORD_MIN_LENGTH = 5;

    /** @var FormFactory */
    private $factory;

    /** @var Model\UserManager */
    private $userManager;


    public function __construct(FormFactory $factory, Model\UserManager $userManager)
    {
        $this->factory = $factory;
        $this->userManager = $userManager;
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
                $this->userManager->save($values->email, $values->password);
            } catch (Model\DuplicateNameException $e) {
                $form['email']->addError('E-mail is already taken.');
                return;
            }
            $onSuccess();
        };

        return $form;
    }
}
