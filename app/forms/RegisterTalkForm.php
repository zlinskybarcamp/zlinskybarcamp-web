<?php

namespace App\Forms;

use App\Model\TalkManager;
use Nette;
use Nette\Application\UI\Form;

class RegisterTalkForm
{
    use Nette\SmartObject;

    /** @var FormFactory */
    private $factory;
    /** @var TalkManager */
    private $talkManager;


    /**
     * RegisterConfereeForm constructor.
     * @param FormFactory $factory
     * @param TalkManager $talkManager
     */
    public function __construct(FormFactory $factory, TalkManager $talkManager)
    {
        $this->factory = $factory;

        $this->talkManager = $talkManager;
    }


    /**
     * @param callable $onSuccess
     * @return Form
     */
    public function create(callable $onSuccess)
    {
        $form = $this->factory->create();
        $form->addText('title', 'Název přednášky:')
            ->setRequired('Prosíme, vyplňte název přednášky');

        $form->addTextArea('description', 'Popis přednášky:')
            ->setRequired('Popis přednášky je důležitý, prosíme vyplňte jej.');

        $form->addTextArea('purpose', 'Pro koho je přednáška určena:')
            ->setRequired('Prosíme, vyplňte pro koho je přednáška určena');

        $form->addSelect('category', 'Kategorie', $this->talkManager->getCategories())
            ->setRequired('Prosím, zvolte jednu kategorii, do které byste přednášku zařadili');

        $form->addRadioList('duration', 'Délka přednášky:', [
            '' => 'Je mi to jedno',
            '40' => '40 minut',
            '60' => '60 minut',
        ])
            ->setDefaultValue('');

        $form->addSubmit('send')
            ->setOption('itemClass', 'text-center')
            ->getControlPrototype()->setName('button')->setText('Odeslat');

        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {

            $this->talkManager->fromForm($values);

            $onSuccess();
        };

        return $form;
    }
}
