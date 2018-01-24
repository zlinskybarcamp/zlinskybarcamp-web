<?php

namespace App\Forms;

use App\Orm\Conferee;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

class ConfereeForm
{
    use SmartObject;

    /** @var FormFactory */
    private $factory;


    /**
     * RegisterConfereeForm constructor.
     * @param FormFactory $factory
     */
    public function __construct(FormFactory $factory)
    {
        $this->factory = $factory;
    }


    /**
     * @param callable $onSuccess
     * @param Conferee|null $conferee
     * @return Form
     */
    public function create(callable $onSuccess, Conferee $conferee = null)
    {
        $form = $this->factory->create();
        $form->addText('name', 'Jméno a příjmení:')
            ->setOption('description', 'Jméno bude zobrazeno v na webu s Vaším avatarem')
            ->setRequired('Prosíme, vyplňte svoje jméno');

        $form->addText('email', 'E-mail:')
            ->setOption('description', 'E-mail nikde nezobrazujeme, ale bude sloužit pro přihlášení a tak…')
            ->setRequired('Prosíme, vyplňte svůj e-mail');

        $form->addTextArea('bio', 'Řekni nám něco o sobě:')
            ->setOption('description', 'Pokud to vyplníte, zobrazíme to na webu u Vašeho jména. '
                . 'Formátování není dovoleno.');

        $form->addGroup('Dotační dotazník');

        $form->addText('extendedCompany', 'Firma/organizace/škola/instituce:')
            ->setOption('description', 'Údaj nikde nezobrazujeme, slouží pouze pro potřeby sponzorování Barcampu')
            ->setRequired('Prosíme, vyplňte jméno instituce, za kterou přicházíte');

        $form->addTextArea('extendedAddress', 'Celá adresa bydlíště, nebo sídla firmy:')
            ->setOption('description', 'Údaj nikde nezobrazujeme, slouží pouze pro potřeby sponzorování Barcampu')
            ->setRequired('Prosíme, vyplňte jméno instituce, za kterou přicházíte (nebo adresu bydliště)');

        $form->addGroup();

        $form->addCheckbox('consens', 'Souhlasím se zpracováním osobních údajů de zákona č. 101/2000 Sb.')
            ->setRequired('Pro dokončení registrace potřebujeme Váš souhlas se zopracováním osobních údajů. '
                . 'Bez něho nemůžeme registraci dokončit.');

        $form->addSubmit('send')
            ->setOption('itemClass', 'text-center')
            ->getControlPrototype()->setName('button')->setText('Odeslat');

        $form->addProtection('Prosím, odešlete formulář ještě jednou');

        $form->onSuccess[] = function (Form $form, $values) use ($conferee, $onSuccess) {
            if ($conferee === null) {
                $conferee = new Conferee();
            }

            $conferee->name = $values->name;
            $conferee->email = $values->email;
            $conferee->bio = $values->bio;
            $conferee->extended = Json::encode([
                'company' => $values->extendedCompany,
                'address' => $values->extendedAddress,
            ]);
            if (isset($values->consens)) {
                $conferee->consens = $values->consens ? new \DateTimeImmutable() : null;
            }

            $onSuccess($conferee, $values);
        };

        if ($conferee) {
            $values = $conferee->toArray();
            $values['consens'] = (bool)$conferee->consens;
            try {
                $extended = Json::decode($conferee->extended, Json::FORCE_ARRAY);
                $values['extendedCompany'] = isset($extended['company']) ? $extended['company'] : null;
                $values['extendedAddress'] = isset($extended['address']) ? $extended['address'] : null;
            } catch (JsonException $e) {
                // void
            }
            $form->setDefaults($values);
        }

        return $form;
    }
}
