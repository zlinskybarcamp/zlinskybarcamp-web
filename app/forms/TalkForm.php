<?php

namespace App\Forms;

use App\Orm\Talk;
use Nette;
use Nette\Application\UI\Form;
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
     */
    public function create(callable $onSuccess, array $categories = null, Talk $talk = null)
    {
        $form = $this->factory->create();
        $form->addText('title', 'Název přednášky:')
            ->setOption('description', 'Zvolte název, který zaujme; nepoužívejte však emotikony a jiné zvláštnosti')
            ->setRequired('Prosíme, vyplňte název přednášky');

        $form->addTextArea('description', 'Popis přednášky:')
            ->setOption('description', 'V několika větách shrňte záměr přednášky. Nepodporujeme formátování, '
                . 'pouze odřádkování')
            ->setRequired('Popis přednášky je důležitý, prosíme vyplňte jej.');

        $form->addTextArea('purpose', 'Pro koho je přednáška určena:')
            ->setRequired('Prosíme, vyplňte pro koho je přednáška určena');

        if ($categories) {
            $form->addSelect('category', 'Kategorie', $categories)
                ->setRequired('Prosím, zvolte jednu kategorii, do které byste přednášku zařadili');
        }

        $form->addRadioList('duration', 'Délka přednášky:', [
            '' => 'Je mi to jedno',
            '40' => '40 minut',
            '60' => '60 minut',
        ])
            ->setDefaultValue('');

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
            $talk->extended = Json::encode([
                'requested_duration' => $values->duration,
                'url' => [
                    'www' => $values->url_www,
                    'facebook' => $values->url_facebook,
                    'twitter' => $values->url_twitter,
                    'google' => $values->url_google,
                    'linkedin' => $values->url_linkedin,
                ],
            ]);


            $onSuccess($talk);
        };

        return $form;
    }
}
