<?php

namespace App\Model;

use Nette\SmartObject;

class FaqManager
{
    use SmartObject;

    const CONFIG_KEY = 'faqs';


    /**
     * @var ConfigManager
     */
    private $config;


    /**
     * FaqManager constructor.
     * @param ConfigManager $config
     */
    public function __construct(ConfigManager $config)
    {
        $this->config = $config;
    }


    /**
     * @return array|null
     * @throws \Nette\Utils\JsonException
     */
    public function get()
    {
        return (array)$this->config->get(self::CONFIG_KEY, []);
    }


    /**
     * @param $faqs
     * @throws \Nette\Utils\JsonException
     */
    public function set($faqs)
    {
        $this->config->set(self::CONFIG_KEY, $faqs);
    }
}
