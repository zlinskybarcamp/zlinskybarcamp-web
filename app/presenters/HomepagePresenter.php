<?php

namespace App\Presenters;

use App\Model\ConfigManager;
use Nette\Utils\Random;
use Tracy\Debugger;

class HomepagePresenter extends BasePresenter
{
    /**
     * @var ConfigManager
     */
    private $config;


    /**
     * HomepagePresenter constructor.
     * @param ConfigManager $config
     */
    public function __construct(ConfigManager $config)
    {
        $this->isHp = true;
        $this->config = $config;
    }


    /**
     * @throws \Nette\Utils\JsonException
     */
    public function renderDefault()
    {
        Debugger::barDump($this->config->get('a'));
        $this->config->set('x', Random::generate());
    }
}
