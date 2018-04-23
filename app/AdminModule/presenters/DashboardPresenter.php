<?php

namespace App\AdminModule\Presenters;

use App\Components\Enumerator\IEnumeratorFormControlFactory;
use App\Model\ConfigManager;
use App\Model\DebugEnabler;
use App\Model\EnumeratorManager;
use App\Model\EventInfoProvider as Event;
use App\Model\ScheduleManager;
use Nette\Application\Request;
use Nette\Application\UI\Form;
use Nette\Http\IResponse;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;

class DashboardPresenter extends BasePresenter
{

    const NOFLAG = 0;
    const REQUIRED = 1;

    /**
     * @var array
     */
    private $simpleConfigs = [
        Event::COUNTS_CONFEREE => ['int', 'Počet účastníků', self::REQUIRED, 'Pozor, zobrazuje se na úvodní stránce'],
        Event::COUNTS_TALKS => ['int', 'Počet přednášek', self::REQUIRED, 'Pozor, zobrazuje se na úvodní stránce'],
        Event::COUNTS_WORKSHOPS => ['int', 'Počet workshopů', self::REQUIRED, 'Pozor, zobrazuje se na úvodní stránce'],
        Event::COUNTS_WARMUPPARTY => [
            'int',
            'Počet warm-up párty',
            self::REQUIRED,
            'Pozor, zobrazuje se na úvodní stránce'
        ],
        Event::COUNTS_AFTERPARTY => [
            'int',
            'Počet afterpárty',
            self::REQUIRED,
            'Pozor, zobrazuje se na úvodní stránce'
        ],
        Event::URL_FACEBOOK => ['url', 'URL profilu na Facebook'],
        Event::URL_TWITTER => ['url', 'URL profilu na Twitter'],
        Event::URL_YOUTUBE => ['url', 'URL profilu na YouTube'],
        Event::URL_INSTAGRAM => ['url', 'URL profilu na Instagram'],
        Event::URL_WAY_TO_EVENT => ['url', 'URL na článek Jak se k nám dostanete'],
        Event::URL_REPORT => ['url', 'URL na report z akce (třeba videa)'],
    ];

    /**
     * @var array
     */
    private $featureConfigs = [
        Event::FEATURE_CONFEREE => ['bool', 'Povolit registraci účastníků'],
        Event::FEATURE_TALK => ['bool', 'Povolit zapisování přednášek'],
        Event::FEATURE_TALK_EDIT => ['bool', 'Povolit editace zapsaných přednášek'],
        Event::FEATURE_VOTE => ['bool', 'Povolit hlasování přednášek'],
        Event::FEATURE_SHOW_VOTE => ['bool', 'Povolit zobrazení hlasů'],
        Event::FEATURE_TALK_ORDER => ['select', 'Přednášky', self::NOFLAG, null, [
            '' => 'Řazené podle přihlášení',
            'random' => 'Řazené náhodně',
            'vote' => 'Řazené podle hlasů',
        ]],
        Event::FEATURE_PROGRAM => ['bool', 'Zobrazit program přednášek'],
        Event::FEATURE_REPORT => ['bool', 'Zobrazit výstupy (YouTube/Reporty)'],
    ];

    private $visualDates = [
        Event::SCHEDULE_VISUAL_DATE_BEGIN => 'Začátek',
        Event::SCHEDULE_VISUAL_DATE_END => 'Konec'
    ];

    /**
     * @var ConfigManager
     */
    private $configManager;
    /**
     * @var ScheduleManager
     */
    private $scheduleManager;
    /**
     * @var IEnumeratorFormControlFactory
     */
    private $enumeratorFormControlFactory;


    /**
     * DashboardPresenter constructor.
     * @param ConfigManager $configManager
     * @param ScheduleManager $scheduleManager
     * @param IEnumeratorFormControlFactory $enumeratorFormControlFactory
     */
    public function __construct(
        ConfigManager $configManager,
        ScheduleManager $scheduleManager,
        IEnumeratorFormControlFactory $enumeratorFormControlFactory
    ) {
        parent::__construct();
        $this->configManager = $configManager;
        $this->scheduleManager = $scheduleManager;
        $this->enumeratorFormControlFactory = $enumeratorFormControlFactory;
    }


    /**
     *
     */
    public function actionEnums()
    {
        $this['faq'] = $this->enumeratorFormControlFactory->create(EnumeratorManager::SET_FAQS);
        $this['categories'] = $this->enumeratorFormControlFactory->create(EnumeratorManager::SET_TALK_CATEGORIES);
        $this['durations'] = $this->enumeratorFormControlFactory->create(EnumeratorManager::SET_TALK_DURATIONS);
        $this['rooms'] = $this->enumeratorFormControlFactory->create(EnumeratorManager::SET_TALK_ROOMS);
    }


    /**
     * @throws \Nette\Utils\JsonException
     */
    public function renderSchedule()
    {
        $steps = $this->scheduleManager->getSteps();
        $currentStepIndex = $this->scheduleManager->getCurrentStepIndex();

        $this->template->currentStepIndex = $currentStepIndex;
        $this->template->steps = $steps;
    }


    /**
     * @param $step
     * @secured
     * @throws \Nette\Utils\JsonException
     * @throws \Nette\Application\AbortException
     */
    public function handleScheduleStepActivate($step)
    {
        $this->scheduleManager->changeCurrentStep($step);

        $messageAppend = $step ? 'Web byl nastaven podle nastavení kroku.' : 'Natavení webu se nijak nezměnilo.';
        $this->flashMessage('Harmonogram byl úspěšně převeden do zvoleného kroku. ' . $messageAppend, 'success');
        $this->redirect(IResponse::S303_SEE_OTHER, 'this');
    }


    /**
     *
     */
    public function renderDebug()
    {
        $this->template->isDebug = DebugEnabler::isDebug();
        $this->template->isDebugByEnv = DebugEnabler::isDebugByEnv();
        $this->template->secured = $this->getRequest()->hasFlag(Request::SECURED);
    }


    /**
     * @throws \Nette\Application\AbortException
     * @secured
     */
    public function handleTurnOff()
    {
        DebugEnabler::turnOff();
        $this->flashMessage('Ladící režim vypnut', 'success');
        $this->redirect('this');
    }


    /**
     * @throws \Nette\Application\AbortException
     * @secured
     */
    public function handleTurnOn()
    {
        DebugEnabler::turnOn();
        $this->flashMessage('Ladící režim zapnut', 'success');
        $this->redirect('this');
    }


    /**
     * @return Form
     * @throws \Nette\Utils\JsonException
     */
    public function createComponentConfigForm()
    {
        $form = new Form();
        foreach ($this->simpleConfigs as $key => $data) {
            $formId = $this->ideable($key);
            $item = null;
            $isRequired = isset($data[2]) && ($data[2] & self::REQUIRED);
            switch ($data[0]) {
                case 'text':
                    $item = $form->addText($formId, $data[1])
                        ->setDefaultValue($this->configManager->get($key, ''));
                    break;
                case 'url':
                    $item = $form->addText($formId, $data[1])
                        ->setType($data[0])
                        ->setDefaultValue($this->configManager->get($key, ''))
                        ->addCondition(Form::FILLED)
                        ->addRule(Form::URL, 'Toto není platné URL');
                    break;
                case 'date':
                case 'time':
                case 'datetime-local':
                    $item = $form->addText($formId, $data[1])
                        ->setType($data[0])
                        ->setDefaultValue($this->configManager->get($key, ''));
                    break;
                case 'bool':
                    $item = $form->addCheckbox($formId, $data[1])
                        ->setDefaultValue($this->configManager->get($key, ''));
                    break;
                case 'int':
                    $item = $form->addInteger($formId, $data[1])
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
        $form->addProtection('Prosím, odešlete tento formulář ještě jednou (bezpečnostní kontrola)');
        $form->onSuccess[] = [$this, 'onConfigFormSuccess'];
        return $form;
    }


    /**
     * @param Form $form
     * @param ArrayHash $values
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


    public function createComponentScheduleConfigForm()
    {
        $form = new Form();

        $form->addGroup();
        foreach ($this->featureConfigs as $key => $data) {
            $formId = $this->ideable($key);
            $item = null;
            $isRequired = isset($data[2]) && ($data[2] & self::REQUIRED);
            switch ($data[0]) {
                case 'bool':
                    $item = $form->addCheckbox($formId, $data[1])
                        ->setDefaultValue($this->configManager->get($key, ''));
                    break;
                case 'select':
                    $item = $form->addSelect($formId, $data[1], $data[4])
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

        $form->addGroup('Vizuální zobrazení pokroku (pohyb kuličky)');

        foreach ($this->visualDates as $key => $name) {
            $form->addText($this->ideable($key), $name)
                ->setType('datetime-local')
                ->setDefaultValue($this->dateToHtml5($this->configManager->get($key)));
        }

        $form->addGroup();

        $form->addSubmit('submit', 'Uložit');
        $form->addProtection('Prosím, odešlete tento formulář ještě jednou (bezpečnostní kontrola)');

        $form->onSuccess[] = [$this, 'onScheduleConfigFormSuccess'];

        return $form;
    }


    /**
     * @param Form $form
     * @param $values
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Utils\JsonException
     */
    public function onScheduleConfigFormSuccess(Form $form, $values)
    {
        $bothConfigs = array_merge($this->featureConfigs, $this->visualDates);

        foreach ($bothConfigs as $key => $data) {
            $id = $this->ideable($key);
            if (isset($values[$id])) {
                $this->configManager->set($key, $values[$id]);
            }
        }

        $this->flashMessage('Nastavení bylo upraveno a změny se hned projevily na webu', 'success');
        $this->redirect(IResponse::S303_SEE_OTHER, 'this');
    }


    /**
     * @return Form
     * @throws \Nette\Utils\JsonException
     */
    public function createComponentScheduleForm()
    {
        $steps = $this->scheduleManager->getSteps(true);

        $form = new Form();

        foreach ($steps as $stepNum => $step) {
            $form->addGroup(sprintf('Krok č. %d: %s', $stepNum + 1, $step['name']));
            foreach ($step['config'] as $config) {
                $fomId = $this->ideable($config['id']);
                switch ($config['type']) {
                    case 'bool':
                        $item = $form->addCheckbox($fomId, $config['name'])
                            ->setDefaultValue($config['value']);
                        break;
                    case 'select':
                        $item = $form->addSelect($fomId, $config['name'], $config['enum'])
                            ->setDefaultValue($config['value']);
                        break;
                    case 'datetime-local':
                        $item = $form->addText($fomId, $config['name'])
                            ->setType($config['type'])
                            ->setDefaultValue($config['value'] ? $this->dateToHtml5($config['value']) : null);
                        break;
                    default:
                        throw new \LogicException("Invalid form field type: $config[type]");
                }
                if ($config['isRequired']) {
                    $item->setRequired("Pole '$config[name]' v sekci '$step[name]' je povinné, ale není vyplněno.'");
                }
            }
        }

        $form->addGroup();
        $form->addSubmit('submit', 'Uložit');
        $form->addProtection('Prosím, odešlete tento formulář ještě jednou (bezpečnostní kontrola)');
        $form->onSuccess[] = [$this, 'onScheduleFormSuccess'];
        return $form;
    }


    /**
     * @param Form $form
     * @param ArrayHash $values
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Utils\JsonException
     */
    public function onScheduleFormSuccess(Form $form, $values)
    {
        $steps = $this->scheduleManager->getSteps(false);

        foreach ($steps as $step) {
            foreach ($step['config'] as $config) {
                $formId = $this->ideable($config['id']);
                $value = $values[$formId];
                $this->scheduleManager->setConfig($step['key'], $config['key'], $value, $config['type']);
            }
        }
        $this->flashMessage('Nastavení uloženo', 'success');
        $this->redirect('this');
    }


    /**
     * @param string $key
     * @return string
     */
    private function ideable($key)
    {
        return str_replace('.', '', $key);
    }


    /**
     * @param string $date
     * @return string
     */
    private function dateToHtml5($date)
    {
        return (new DateTime($date))->format('Y-m-d\TH:i:s');
    }
}
