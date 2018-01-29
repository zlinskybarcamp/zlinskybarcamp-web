<?php

namespace App\Components\Enumerator;

use App\Model\EnumeratorManager;
use Kdyby\Replicator;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\ArrayHash;

class EnumeratorFormControl extends Control
{
    /**
     * @var EnumeratorManager
     */
    private $enumeratorManager;
    /**
     * @var string
     */
    private $setName;


    /**
     * EnumeratorFormControl constructor.
     * @param string $setName Name set name (in database)
     * @param EnumeratorManager $enumeratorManager
     */
    public function __construct($setName, EnumeratorManager $enumeratorManager)
    {
        parent::__construct();
        $this->setName = $setName;
        $this->enumeratorManager = $enumeratorManager;
    }


    protected function attached($presenter)
    {
        parent::attached($presenter);

        if ($presenter instanceof Presenter) {
            /** @var Form $form */
            $form = $this['form'];
            if (!$form->isSubmitted()) {
                $enums = $this->enumeratorManager->get($this->setName);

                foreach ($enums as $i => $enum) {
                    $form['enums'][$i]->setDefaults($enum);
                }
            }

        }
    }


    /**
     * @throws \App\Model\InvalidEnumeratorSetException
     * @throws \Nette\Utils\JsonException
     */
    public function render()
    {
        $this->template->setFile(__DIR__ . '/EnumeratorForm.latte');
        $this->template->enum = $this->enumeratorManager->get($this->setName);
        $this->template->render();
    }


    /**
     * @return Form
     */
    public function createComponentForm()
    {
        $form = new Form();

        $removeEvent = [$this, 'removeClicked'];

        /** @var Replicator\Container $enums */
        $enums = $form->addDynamic('enums', function (Container $enums) use ($removeEvent) {
            $enums->addText('key', 'Klíč', 30);
            $enums->addText('value', 'Hodnota', 50);

            $enums->addSubmit('remove', 'Odstranit')
                ->setValidationScope(false)
                ->onClick[] = $removeEvent;
        }, 1);

        $enums->addSubmit('add', 'Přidat další otázku')
            ->setValidationScope(false)
            ->onClick[] = [$this, 'addClicked'];

        $form->addSubmit('submit', 'Uložit');
        $form->addProtection('Prosím, odešlete tento formulář ještě jednou (bezpečnostní kontrola)');
        $form->onSuccess[] = [$this, 'onFormSuccess'];
        return $form;
    }


    /**
     * @param Form $form
     * @param ArrayHash $values
     * @throws \App\Model\InvalidEnumeratorSetException
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Utils\JsonException
     */
    public function onFormSuccess(Form $form, $values)
    {
        if ($form['submit']->isSubmittedBy() === false) {
            return;
        }

        $enums = [];
        foreach ($form['enums']->values as $enum) {
            if (empty($enum['key']) || empty($enum['value'])) {
                continue;
            }
            $enums[] = $enum;
        }
        $this->enumeratorManager->set($this->setName, $enums);

        $this->flashMessage('Nastavení uloženo', 'success');
        $this->redirect('this');
    }


    /**
     * @param SubmitButton $button
     */
    public function addClicked(SubmitButton $button)
    {
        /** @var Replicator\Container $enums */
        $enums = $button->parent;
        $enums->createOne();
    }


    /**
     * @param SubmitButton $button
     */
    public function removeClicked(SubmitButton $button)
    {
        /** @var Container $container */
        $container = $button->parent;
        /** @var Replicator\Container $enums */
        $enums = $container->parent;
        $enums->remove($container, true);
    }


}
