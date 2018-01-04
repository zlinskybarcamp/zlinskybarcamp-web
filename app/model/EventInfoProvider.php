<?php

namespace App\Model;

use Nette\SmartObject;
use Nette\Utils\DateTime;

class EventInfoProvider
{
    use SmartObject;

    const EVENT_DATE = 'dates.eventDate';


    /**
     * @var ConfigManager
     */
    private $config;


    public function __construct(ConfigManager $config)
    {
        $this->config = $config;
    }


    public function getEventDate()
    {
        $string = (string)$this->config->get(self::EVENT_DATE);
        return DateTime::from($string);
    }
}
