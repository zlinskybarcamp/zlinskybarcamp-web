<?php

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;

class SignUpFormFactory
{
    use Nette\SmartObject;

    const PASSWORD_MIN_LENGTH = 7;

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
        $form->addEmail('email', 'Your e-mail:')
            ->setRequired('Please enter your e-mail.');

        $form->addPassword('password', 'Create a password:')
            ->setOption('description', sprintf('at least %d characters', self::PASSWORD_MIN_LENGTH))
            ->setRequired('Please create a password.')
            ->addRule($form::MIN_LENGTH, null, self::PASSWORD_MIN_LENGTH);

        $form->addSubmit('send', 'Sign up');

        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            try {
                $this->userManager->add($values->email, $values->password);
            } catch (Model\DuplicateNameException $e) {
                $form['email']->addError('E-mail is already taken.');
                return;
            }
            $onSuccess();
        };

        return $form;
    }
}
