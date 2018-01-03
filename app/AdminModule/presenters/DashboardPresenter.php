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
        'features.showProgram' => ['bool', 'Zobrazení programu přednášek'],
        'features.showRecordings' => ['bool', 'Zobrazení záznamů přednášek (YouTube)'],
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
                    $form->addText($id, $data[1])->setDefaultValue($this->configManager->get($key, ''));
                    break;
            }
        }
        $form->addSubmit('submit', 'Uložit');
        $form->onSuccess[] = [$this, 'onFormSuccess'];
        return $form;
    }


    public function onFormSuccess(Form $form, $values)
    {
        foreach ($this->simpleConfigs as $key => $data) {
            $id = $this->ideable($key);
            if (isset($values[$id])) {
                $this->configManager->set($key, $values[$id]);
            }
        }
    }


    private function ideable($key)
    {
        return str_replace('.', '', $key);
    }
}
