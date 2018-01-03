<?php


namespace App\AdminModule\Presenters;

use App\Model\ConfigManager;
use Nette\Application\UI\Form;

class DashboardPresenter extends BasePresenter
{

    private $simpleConfigs = [
        'features.registerConferee.limit' => ['int', 'Počet účastníků'],
        'features.registerConferee.enabled' => ['bool', 'Povolení registrace účastníků'],
        'features.registerTalk.enabled' => ['bool', 'Povolení zapisování přednášek'],
        'features.voteTalk.enabled' => ['bool', 'Povolení hlasování přednášek'],
        'features.showVoteTalk.enabled' => ['bool', 'Povolení zobrazení pořadí přednášek (podle hlasování)'],
        'features.showProgram.enabled' => ['bool', 'Zobrazení programu přednášek'],
        'features.showRecordings.enabled' => ['bool', 'Zobrazení záznamů přednášek (YouTube)'],
    ];


    /**
     * @var ConfigManager
     */
    private $configManager;


    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }


    public function createComponentDashboardForm()
    {
        $form = new Form();
        foreach ($this->simpleConfigs as $key => $data) {
            $id = $this->ideable($key);
            switch ($data[0]) {
                case 'bool':
                    $form->addCheckbox($id, $data[1])->setDefaultValue($this->configManager->get($key, ''));
                    break;
                case 'int':
                    $form->addInteger($id, $data[1])->setDefaultValue($this->configManager->get($key, ''));
                    break;
            }
        }
        $form->addSubmit('submit', 'Uložit');
        $form->onValidate[] = [$this, 'onFormValidate'];
        $form->onSuccess[] = [$this, 'onFormSuccess'];
        return $form;
    }


    public function onFormValidate(Form $form, $values)
    {
        $confereeLimit = $values[$this->ideable('features.registerConferee.limit')];
        $allowedRegisterConferee = $values[$this->ideable('features.registerConferee.enabled')];
        $allowedRegisterTalk = $values[$this->ideable('features.registerTalk.enabled')];

        if($allowedRegisterConferee && $confereeLimit <= 0) {
            $form->addError('Registrace účastníků je povolena, ale současně je Počet účastníků nulový');
        }
        if($allowedRegisterTalk && !$allowedRegisterConferee) {
            $form->addError('Je-li povoleno zapisování přednášek, potřebujeme povolit registraci účastníků');
        }
    }


    public function onFormSuccess(Form $form, $values)
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


    private function ideable($key)
    {
        return str_replace('.', '', $key);
    }
}
