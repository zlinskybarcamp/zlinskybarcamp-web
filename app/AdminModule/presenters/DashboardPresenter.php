<?php


namespace App\AdminModule\Presenters;

use App\Model\ConfigManager;
use App\Model\EventInfoProvider as Event;
use App\Model\FaqManager;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;

class DashboardPresenter extends BasePresenter
{

    const NOFLAG = 0;
    const REQUIRED = 1;

    /**
     * @var array
     */
    private $simpleConfigs = [
        Event::EVENT_DATE => ['datetime-local', 'Datum akce', self::REQUIRED, 'Pozor, zobrazuje se na více místech webu'],
        'features.registerConferee.enabled' => ['bool', 'Povolení registrace účastníků'],
        'features.registerTalk.enabled' => ['bool', 'Povolení zapisování přednášek'],
        'features.voteTalk.enabled' => ['bool', 'Povolení hlasování přednášek'],
        'features.showVoteTalk.enabled' => ['bool', 'Povolení zobrazení pořadí přednášek (podle hlasování)'],
        'features.showProgram.enabled' => ['bool', 'Zobrazení programu přednášek'],
        'features.showRecordings.enabled' => ['bool', 'Zobrazení záznamů přednášek (YouTube)'],
        Event::COUNTS_CONFEREE => ['int', 'Počet účastníků', self::REQUIRED, 'Pozor, zobrazuje se na úvodní stránce'],
        Event::COUNTS_TALKS => ['int', 'Počet přednášek', self::REQUIRED, 'Pozor, zobrazuje se na úvodní stránce'],
        Event::COUNTS_WORKSHOPS => ['int', 'Počet workshopů', self::REQUIRED, 'Pozor, zobrazuje se na úvodní stránce'],
        Event::COUNTS_WARMUPPARTY => ['int', 'Počet warm-up párty', self::REQUIRED, 'Pozor, zobrazuje se na úvodní stránce'],
        Event::COUNTS_AFTERPARTY => ['int', 'Počet afterpárty', self::REQUIRED, 'Pozor, zobrazuje se na úvodní stránce'],
        Event::URL_FACEBOOK => ['url', 'URL profilu na Facebook'],
        Event::URL_TWITTER => ['url', 'URL profilu na Twitter'],
        Event::URL_YOUTUBE => ['url', 'URL profilu na YouTube'],
        Event::URL_INSTAGRAM => ['url', 'URL profilu na Instagram'],
    ];


    /**
     * @var ConfigManager
     */
    private $configManager;
    /**
     * @var FaqManager
     */
    private $faqManager;


    /**
     * DashboardPresenter constructor.
     * @param ConfigManager $configManager
     * @param FaqManager $faqManager
     */
    public function __construct(ConfigManager $configManager, FaqManager $faqManager)
    {
        $this->configManager = $configManager;
        $this->faqManager = $faqManager;
    }


    /**
     * @return Form
     * @throws \Nette\Utils\JsonException
     */
    public function createComponentConfigForm()
    {
        $form = new Form();
        foreach ($this->simpleConfigs as $key => $data) {
            $id = $this->ideable($key);
            $item = null;
            $isRequired = isset($data[2]) && ($data[2] | self::REQUIRED);
            switch ($data[0]) {
                case 'text':
                    $item = $form->addText($id, $data[1])
                        ->setDefaultValue($this->configManager->get($key, ''));
                    break;
                case 'url':
                    $item = $form->addText($id, $data[1])
                        ->setType($data[0])
                        ->setDefaultValue($this->configManager->get($key, ''))
                        ->addCondition(Form::FILLED)
                            ->addRule(Form::URL, 'Toto není platné URL');
                    break;
                case 'date':
                case 'time':
                case 'datetime-local':
                    $item = $form->addText($id, $data[1])
                        ->setType($data[0])
                        ->setDefaultValue($this->configManager->get($key, ''));
                    break;
                case 'bool':
                    $item = $form->addCheckbox($id, $data[1])
                        ->setDefaultValue($this->configManager->get($key, ''));
                    break;
                case 'int':
                    $item = $form->addInteger($id, $data[1])
                        ->setDefaultValue($this->configManager->get($key, ''));
                    break;
                default:
                    throw new \LogicException('Unknown form item type reqested');
            }
            if ($isRequired) {
                $item->setRequired("Pole '$data[1]' musí být vyplněno.'");
            }
            if (isset($data[3])) {
                $item->setOption('description', $data[3]);
            }
        }
        $form->addSubmit('submit', 'Uložit');
        $form->onValidate[] = [$this, 'onConfigFormValidate'];
        $form->onSuccess[] = [$this, 'onConfigFormSuccess'];
        return $form;
    }


    /**
     * @param Form $form
     * @param $values
     */
    public function onConfigFormValidate(Form $form, $values)
    {
        $confereeLimit = $values[$this->ideable(Event::COUNTS_CONFEREE)];
        $allowedRegisterConferee = $values[$this->ideable('features.registerConferee.enabled')];
        $allowedRegisterTalk = $values[$this->ideable('features.registerTalk.enabled')];

        if ($allowedRegisterConferee && $confereeLimit <= 0) {
            $form->addError('Registrace účastníků je povolena, ale současně je Počet účastníků nulový');
        }
        if ($allowedRegisterTalk && !$allowedRegisterConferee) {
            $form->addError('Je-li povoleno zapisování přednášek, potřebujeme povolit registraci účastníků');
        }
    }


    /**
     * @param Form $form
     * @param $values
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Utils\JsonException
     */
    public function onConfigFormSuccess(Form $form, $values)
    {
        foreach ($this->simpleConfigs as $key => $data) {
            $id = $this->ideable($key);
            if (isset($values[$id])) {
                $this->configManager->set($key, $values[$id]);
            }
        }

        $this->flashMessage('Nastavení uloženo', 'success');
        $this->redirect('this');
    }


    /**
     * @throws \Nette\Utils\JsonException
     */
    public function actionFaq()
    {
        $form = $this['faqForm'];
        if (!$form->isSubmitted()) {
            $faqs = $this->faqManager->get();
            foreach ($faqs as $i => $faq) {
                $form['faqs'][$i]->setDefaults($faq);
            }
        }
    }


    /**
     * @return Form
     */
    public function createComponentFaqForm()
    {
        $form = new Form();

        $removeEvent = [$this, 'faqRemoveClicked'];

        $faq = $form->addDynamic('faqs', function (Container $faq) use ($removeEvent) {
            $faq->addText('question', 'Otázka', 30);
            $faq->addText('answer', 'Odpověď', 50);
            $faq->addSubmit('remove', 'Odstranit')
                ->setValidationScope(false)
                ->onClick[] = $removeEvent;
        }, 1);

        $faq->addSubmit('add', 'Přidat další otázku')
            ->setValidationScope(false)
            ->onClick[] = [$this, 'faqAddClicked'];

        $form->addSubmit('submit', 'Uložit');
        $form->onSuccess[] = [$this, 'onFaqFormSuccess'];
        return $form;
    }


    /**
     * @param Form $form
     * @param $values
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Utils\JsonException
     */
    public function onFaqFormSuccess(Form $form, $values)
    {
        if ($form['submit']->isSubmittedBy() === false) {
            return;
        }

        $faqs = [];
        foreach ($form['faqs']->values as $faq) {
            if (empty($faq['question']) || empty($faq['answer'])) {
                continue;
            }
            $faqs[] = $faq;
        }
        $this->faqManager->set($faqs);

        $this->flashMessage('Nastavení uloženo', 'success');
        $this->redirect('this');
    }


    /**
     * @param SubmitButton $button
     */
    public function faqAddClicked(SubmitButton $button)
    {
        $button->parent->createOne();
    }


    /**
     * @param SubmitButton $button
     */
    public function faqRemoveClicked(SubmitButton $button)
    {
        $faqs = $button->parent->parent;
        $faqs->remove($button->parent, true);
    }


    /**
     * @param $key
     * @return mixed
     */
    private function ideable($key)
    {
        return str_replace('.', '', $key);
    }
}
