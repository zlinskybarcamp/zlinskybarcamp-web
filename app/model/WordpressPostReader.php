<?php

namespace App\Model;

use Nette\Caching;
use Nette\Utils\ArrayHash;
use Nette\Utils\Json;

class WordpressPostReader
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var Caching\Cache
     */
    private $cache;


    /**
     * WordpressPostReader constructor.
     * @param $config
     * @param Caching\IStorage $cacheStorage
     */
    public function __construct($config, Caching\IStorage $cacheStorage)
    {
        $this->config = $config;
        $this->cache = new Caching\Cache($cacheStorage, __NAMESPACE__ . '\\' . __CLASS__);
    }


    /**
     * @return array
     */
    public function get()
    {
        return $this->load();
    }


    /**
     * @return mixed
     */
    public function getSourceUrl()
    {
        return $this->config['url'];
    }


    /**
     * @return mixed
     */
    public function getMaxItems()
    {
        return $this->config['maxItems'];
    }


    /**
     * @return array
     * @throws \Nette\Utils\JsonException
     */
    private function load()
    {
        $feed = $this->processOriginFeed($this->loadFeed());
        return $feed;
    }


    /**
     * @return mixed
     * @throws \Nette\Utils\JsonException
     */
    private function loadFeed()
    {
        $apiQueryUrl = sprintf(
            '%s/wp-json/wp/v2/posts?per_page=%d',
            rtrim($this->getSourceUrl(), '/'),
            $this->getMaxItems()
        );

        $json = file_get_contents($apiQueryUrl);

        return Json::decode($json, Json::FORCE_ARRAY);
    }


    /**
     * @param $originFeed
     * @return array
     * @throws \Nette\Utils\JsonException
     */
    private function processOriginFeed($originFeed)
    {
        $feed = [];
        foreach ($originFeed as $originItem) {
            $item = [
                'url' => $originItem['link'],
                'name' => $originItem['title']['rendered'],
                'content' => $originItem['excerpt']['rendered'],
                'thumbnailUrl' => $this->getThumbnailForItem($originItem)
            ];
            $feed[] = ArrayHash::from($item);
        }
        return $feed;
    }


    /**
     * @param $item
     * @return null
     * @throws \Nette\Utils\JsonException
     */
    private function getThumbnailForItem($item)
    {
        if (!isset($item['_links']['wp:featuredmedia'])) {
            return null;
        }

        $featuredmedia = $item['_links']['wp:featuredmedia'];

        $mediaInfo = null;
        foreach ($featuredmedia as $medium) {
            $mediaInfo = $this->loadMediaInfo(($medium['href']));
            break;
        }

        if (isset($mediaInfo['media_details']['sizes']['thumbnail']['source_url'])) {
            return $mediaInfo['media_details']['sizes']['thumbnail']['source_url'];
        }

        return null;
    }


    /**
     * @param $mediaUrl
     * @return mixed
     * @throws \Nette\Utils\JsonException
     */
    private function loadMediaInfo($mediaUrl)
    {
        $json = file_get_contents($mediaUrl);

        return Json::decode($json, Json::FORCE_ARRAY);
    }
}
