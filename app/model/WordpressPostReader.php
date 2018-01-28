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
     * @param array $config
     * @param Caching\IStorage $cacheStorage
     */
    public function __construct($config, Caching\IStorage $cacheStorage)
    {
        $this->config = $config;
        $this->cache = new Caching\Cache($cacheStorage, self::class);
    }


    /**
     * @return array
     */
    public function get()
    {
        $feed = $this->cache->load('feed', function (&$dependencies) {
            $dependencies = [Caching\Cache::EXPIRE => '10 minutes'];
            return $this->load();
        });

        if (!is_array($feed)) {
            $feed = [];
        }

        return $feed;
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


        $json = @file_get_contents($apiQueryUrl);
        if (!$json) {
            return [];
        }

        return Json::decode($json, Json::FORCE_ARRAY);
    }


    /**
     * @param array $originFeed
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
     * @param array $item
     * @return string
     * @throws \Nette\Utils\JsonException
     */
    private function getThumbnailForItem($item)
    {
        $thumbnailUrl = $this->config['defaultThumbnail'];

        if (!isset($item['_links']['wp:featuredmedia'])) {
            return $thumbnailUrl;
        }

        $featuredmedia = $item['_links']['wp:featuredmedia'];

        $mediaInfo = null;
        foreach ($featuredmedia as $medium) {
            $mediaInfo = $this->loadMediaInfo(($medium['href']));
            break;
        }

        if (isset($mediaInfo['media_details']['sizes']['thumbnail']['source_url'])) {
            $thumbnailUrl = $mediaInfo['media_details']['sizes']['thumbnail']['source_url'];
        }

        return $thumbnailUrl;
    }


    /**
     * @param string $mediaUrl
     * @return array|null
     * @throws \Nette\Utils\JsonException
     */
    private function loadMediaInfo($mediaUrl)
    {
        $json = @file_get_contents($mediaUrl);
        if (!$json) {
            return null;
        }

        return Json::decode($json, Json::FORCE_ARRAY);
    }
}
