<?php

namespace App\Forms;

use App\Orm\Talk;
use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\Html;
use Nette\Utils\Json;

class TalkForm
{
    use Nette\SmartObject;

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
     * @param array|null $categories
     * @param Talk $talk
     * @return Form
     * @throws Nette\Utils\JsonException
     */
    public function create(callable $onSuccess, array $categories = null, array $durations = null, Talk $talk = null)
    {
        $form = $this->factory->create();
        $form->addText('title', 'Název tvojí přednášky:')
            ->setOption('description', 'Zvolte název, který zaujme; nepoužívejte však emotikony a jiné zvláštnosti')
            ->setRequired('Prosíme, vyplňte název přednášky');

        $form->addTextArea('description', 'Popis tvé přednášky:')
            ->setOption('description', 'V několika větách shrňte záměr přednášky. Nepodporujeme formátování, '
                . 'pouze odřádkování')
            ->setRequired('Popis přednášky je důležitý, prosíme vyplňte jej.');

        $form->addTextArea('purpose', 'Pro koho je přednáška určena:')
            ->setRequired('Prosíme, vyplňte pro koho je přednáška určena');

        if ($categories) {
            $form->addSelect('category', 'Kategorie', [null => Html::el()->setHtml('&rarr; Vyberte')] + $categories)
                ->setRequired('Prosím, zvolte jednu kategorii, do které byste přednášku zařadili');
        }

        if ($durations) {
            $form->addRadioList('duration', 'Délka přednášky:', $durations)
                ->setDefaultValue(key($durations));
        }

        $form->addGroup('Něco o vás');

        $form->addText('company', 'Firma:')
            ->setOption('description', 'Volitelné: firma, kterou reprezentujete; napište tak, jak se běžne užívá. '
                . 'Bude zobrazeno v popisu přednášky.');

        $form->addText('url_www', 'WWW stránka:')
            ->setOption('description', 'Volitelné. Odkaz na vaše stránky, (příp. stránky firmy)')
            ->addCondition(Form::FILLED)
            ->addRule(Form::URL);

        $form->addText('url_facebook', 'URL Facebook profilu:')
            ->addCondition(Form::FILLED)
            ->addRule(Form::URL);

        $form->addText('url_twitter', 'URL Twitter profilu:')
            ->addCondition(Form::FILLED)
            ->addRule(Form::URL);

        $form->addText('url_google', 'URL Google+ profilu:')
            ->addCondition(Form::FILLED)
            ->addRule(Form::URL);

        $form->addText('url_linkedin', 'URL LinkedIn profilu:')
            ->addCondition(Form::FILLED)
            ->addRule(Form::URL);

        $form->addGroup('');

        $form->addTextArea('notes', 'Poznámky / požadavky pro organizátory:')
            ->setOption('description', 'Volitelné. Chcete nám něco sdělit? Např. speciální požadavky, ap.');

        $form->addSubmit('send')
            ->setOption('itemClass', 'text-center')
            ->getControlPrototype()->setName('button')->setText('Odeslat');

        $form->addProtection('Prosím, odešlete formulář ještě jednou');

        $form->onSuccess[] = function (Form $form, $values) use ($talk, $onSuccess) {
            if ($talk === null) {
                $talk = new Talk();
            }

            $talk->title = $values->title;
            $talk->description = $values->description;
            $talk->purpose = $values->purpose;
            $talk->company = $values->company;
            $talk->notes = $values->notes;
            if (isset($values->category)) {
                $talk->category = $values->category;
            }
            $talk->extended = Json::encode($this->reverseMapFields($values, $this->extendedFieldsMap()));

            $onSuccess($talk, $values);
        };

        if ($talk) {
            $values = $talk->toArray();
            try {
                $extended = Json::decode($talk->extended, Json::FORCE_ARRAY);
                $values += $this->mapFields($extended, $this->extendedFieldsMap());
            } catch (JsonException $e) {
                // void
            }
            $form->setDefaults($values);
        }

        return $form;
    }


    /**
     * Map values from structured fields to flat (Json to form fields)
     *
     * @param $inputValues
     * @param $map
     * @return array
     */
    private function mapFields($inputValues, $map)
    {
        $outputValues = [];

        foreach ($map as $inputKey => $outputKey) {
            if (isset($inputValues[$inputKey])) {
                $value = $inputValues[$inputKey];
            } else {
                continue;
            }

            if (is_array($outputKey)) {
                $outputValues += $this->mapFields($value, $outputKey);
            } else {
                $outputValues[$outputKey] = $value;
            }
        }

        return $outputValues;
    }


    /**
     * Map values from flat fields to structured (form fields to Json)
     *
     * @param $inputValues
     * @param $map
     * @return array
     */
    private function reverseMapFields($inputValues, $map)
    {
        $outputValues = [];

        foreach ($map as $outputKey => $inputKey) {
            if (is_array($inputKey)) {
                $outputValues[$outputKey] = $this->reverseMapFields($inputValues, $inputKey);
            } elseif (isset($inputValues[$inputKey])) {
                $outputValues[$outputKey] = $inputValues[$inputKey];
            } else {
                continue;
            }
        }

        return $outputValues;
    }


    /**
     * @return array
     */
    private function extendedFieldsMap()
    {
        return [
            'requested_duration' => 'duration',
            'url' => [
                'www' => 'url_www',
                'facebook' => 'url_facebook',
                'twitter' => 'url_twitter',
                'google' => 'url_google',
                'linkedin' => 'url_linkedin',
            ]
        ];
    }
}
