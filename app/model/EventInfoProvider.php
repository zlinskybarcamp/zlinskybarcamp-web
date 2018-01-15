<?php

namespace App\Model;

use Nette\SmartObject;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;

class EventInfoProvider
{
    use SmartObject;

    const COUNTS_CONFEREE = 'counts.conferee';
    const COUNTS_TALKS = 'counts.talks';
    const COUNTS_WORKSHOPS = 'counts.workshops';
    const COUNTS_WARMUPPARTY = 'counts.warmupparty';
    const COUNTS_AFTERPARTY = 'counts.afterparty';
    const DATE_TALKS = 'schedule.talks.date';
    const DATE_VOTE = 'schedule.vote.date';
    const DATE_EVENT = 'schedule.event.date';
    const DATE_PROGRAM = 'schedule.program.date';
    const DATE_REPORT = 'schedule.report.date';
    const URL_FACEBOOK = 'url.social.facebook';
    const URL_TWITTER = 'url.social.twitter';
    const URL_YOUTUBE = 'url.social.youtube';
    const URL_INSTAGRAM = 'url.social.instagram';


    /**
     * @var ConfigManager
     */
    private $config;


    /**
     * EventInfoProvider constructor.
     * @param ConfigManager $config
     */
    public function __construct(ConfigManager $config)
    {
        $this->config = $config;
    }


    /**
     * @return DateTime
     * @throws \Nette\Utils\JsonException
     */
    public function getEventDate()
    {
        $string = (string)$this->config->get(self::DATE_EVENT);
        return DateTime::from($string);
    }


    /**
     * @return ArrayHash
     * @throws \Nette\Utils\JsonException
     */
    public function getDates()
    {
        return ArrayHash::from([
            'talks' => DateTime::from($this->config->get(self::DATE_TALKS)),
            'vote' => DateTime::from($this->config->get(self::DATE_VOTE)),
            'program' => DateTime::from($this->config->get(self::DATE_PROGRAM)),
            'event' => DateTime::from($this->config->get(self::DATE_EVENT)),
            'report' => DateTime::from($this->config->get(self::DATE_REPORT)),
        ]);
    }


    /**
     * @return ArrayHash
     * @throws \Nette\Utils\JsonException
     */
    public function getSocialUrls()
    {
        return ArrayHash::from([
            'facebook' => $this->config->get(self::URL_FACEBOOK),
            'twitter' => $this->config->get(self::URL_TWITTER),
            'youtube' => $this->config->get(self::URL_YOUTUBE),
            'instagram' => $this->config->get(self::URL_INSTAGRAM),
        ]);
    }


    /**
     * @return ArrayHash
     * @throws \Nette\Utils\JsonException
     */
    public function getCounts()
    {
        return ArrayHash::from([
            'conferee' => $this->config->get(self::COUNTS_CONFEREE),
            'talks' => $this->config->get(self::COUNTS_TALKS),
            'workshops' => $this->config->get(self::COUNTS_WORKSHOPS),
            'warmupparty' => $this->config->get(self::COUNTS_WARMUPPARTY),
            'afterparty' => $this->config->get(self::COUNTS_AFTERPARTY),
        ]);
    }
}
