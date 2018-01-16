<?php

namespace App\Model;

use Nette\Utils\DateTime;

class ScheduleManager
{
    const NOFLAG = 0;
    const REQUIRED = 1;

    /**
     * @var array
     */
    private $steps = [
        ['talks', 'Registrace přednášek'],
        ['vote', 'Hlasování o přednáškách'],
        ['program', 'Zveřejnění programu'],
        ['event', 'Barcamp'],
        ['report', 'Výstup (videa)']
    ];

    /**
     * @var array
     */
    private $stepConfigs = [
        ['auto', 'bool', 'Povolit automatické spuštění v daný čas'],
        ['date', 'datetime-local', 'Začátek', self::REQUIRED],
        ['features.registerConferee.enabled', 'bool', 'Povolit registraci účastníků'],
        ['features.registerTalk.enabled', 'bool', 'Povolit zapisování přednášek'],
        ['features.voteTalk.enabled', 'bool', 'Povolit hlasování přednášek'],
        ['features.showVoteTalk.enabled', 'bool', 'Zobrazit pořadí přednášek (podle hlasování)'],
        ['features.showProgram.enabled', 'bool', 'Zobrazit program přednášek'],
        ['features.showReport.enabled', 'bool', 'Zobrazit výstupy (YouTube/Reporty)'],
    ];

    /**
     * @var ConfigManager
     */
    private $configManager;


    /**
     * ScheduleManager constructor.
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }


    /**
     * @param bool $withValues
     * @return array
     * @throws \Nette\Utils\JsonException
     */
    public function getSteps($withValues = false)
    {
        $steps = [];
        foreach ($this->steps as $step) {
            $confs = [];
            foreach ($this->stepConfigs as $stepConfig) {
                $conf = [
                    'id' => $this->getConfigKey($step[0], $stepConfig[0]),
                    'key' => $stepConfig[0],
                    'type' => $stepConfig[1],
                    'name' => $stepConfig[2],
                    'isRequired' => isset($stepConfig[3]) ? ($stepConfig[3] | self::REQUIRED) !== 0 : false,
                ];
                if ($withValues) {
                    $conf['value'] = $this->getConfig($step[0], $stepConfig[0]);
                }
                $confs[] = $conf;
            }
            $steps[] = [
                'key' => $step[0],
                'name' => $step[1],
                'config' => $confs,
                'isCurrent' => $this->getCurrentStep() === $step[0],
            ];
        }
        return $steps;
    }


    /**
     * @param string $stepName
     * @param string $configName
     * @param string $type
     * @return bool|mixed|null|string
     * @throws \Nette\Utils\JsonException
     */
    public function getConfig($stepName, $configName, $type = null)
    {
        $value = $this->configManager->get($this->getConfigKey($stepName, $configName));

        if ($type) {
            $value = $this->strictType($value, $type);
        }

        return $value;
    }


    /**
     * @param string $stepName
     * @param string $configName
     * @param mixed $value
     * @param string $type
     * @throws \Nette\Utils\JsonException
     */
    public function setConfig($stepName, $configName, $value, $type = null)
    {
        if ($type) {
            $value = $this->strictType($value, $type);
        }

        $this->configManager->set($this->getConfigKey($stepName, $configName), $value);
    }


    /**
     * @param string $stepName
     * @param string $configName
     * @return string
     */
    private function getConfigKey($stepName, $configName)
    {
        return sprintf("schedule.%s.%s", $stepName, $configName);
    }


    /**
     * @return mixed
     * @throws \Nette\Utils\JsonException
     */
    public function getCurrentStep()
    {
        return $this->configManager->get('schedule.currentStep');
    }


    /**
     * @param string $value
     * @throws \Nette\Utils\JsonException
     */
    public function setCurrentStep($value)
    {
        $this->configManager->set('schedule.currentStep', $value);
    }


    /**
     * @param mixed $value
     * @param string $type
     * @return bool|null|string
     */
    private function strictType($value, $type)
    {
        if (is_null($value)) {
            return null;
        }

        switch ($type) {
            case 'bool':
                return (bool)$value;
                break;
            case 'datetime-local':
                return (new DateTime($value))->format('c');
                break;
            default:
                throw new \LogicException("Invalid form field type: $type");
        }
    }
}
