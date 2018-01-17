<?php

namespace App\Forms;

use App\Model\ConfereeManager;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;

class RegisterConfereeForm
{
    use Nette\SmartObject;

    /** @var FormFactory */
    private $factory;
    /**
     * @var ConfereeManager
     */
    private $confereeManager;


    /**
     * RegisterConfereeForm constructor.
     * @param FormFactory $factory
     * @param ConfereeManager $confereeManager
     */
    public function __construct(FormFactory $factory, ConfereeManager $confereeManager)
    {
        $this->factory = $factory;
        $this->confereeManager = $confereeManager;
    }


    /**
     * @param callable $onSuccess
     * @return Form
     */
    public function create(callable $onSuccess)
    {
        $form = $this->factory->create();
        $form->addText('name', 'Jméno a příjmení:')
            ->setRequired('Prosíme, vyplňte svoje jméno');

        $form->addText('email', 'E-mail:')
            ->setRequired('Prosíme, vyplňte svůj e-mail');

        $form->addTextArea('bio', 'Řekni nám něco o sobě');

        $form->addGroup('Dotační dotazník');

        $form->addText('extendedOrganization', 'Firma/organizace/škola/instituce')
            ->setRequired('Prosíme, vyplňte jméno instituce, za kterou přicházíte');

        $form->addTextArea('extendedAddress', 'Celá adresa bydlíště, nebo sídla firmy')
            ->setOption('description', 'Děkujeme za pochopení')
            ->setRequired('Prosíme, vyplňte jméno instituce, za kterou přicházíte (nebo adresu bydliště)');

        $form->addGroup();

        $form->addCheckbox('allow_mail', 'Souhlasím se zasíláním informací o akci e-mailem.');

        $form->addCheckbox('consens', 'Souhlasím se zpracováním osobních údajů de zákona č. 101/2000 Sb.')
            ->setRequired('Pro dokončení registrace potřebujeme Váš souhlas se zopracováním osobních údajů. '
                . 'Bez něho nemůžeme registraci dokončit.');

        $form->addSubmit('send')
            ->setOption('itemClass', 'text-center')
            ->getControlPrototype()->setName('button')->setText('Odeslat');

        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {

            $this->confereeManager->fromForm($values);

            $onSuccess();
        };

        return $form;
    }
}
