<?php

namespace App\Model;

use Nette\Http\Request;
use Nette\Utils\Image;
use Nette\Utils\Random;

class AvatarStorage
{
    private $uploadDir;
    private $urlPrefix;
    /**
     * @var Request
     */
    private $request;


    public function __construct($uploadDir, $urlPrefix, Request $request)
    {
        $this->uploadDir = $uploadDir;
        $this->urlPrefix = $urlPrefix;
        $this->request = $request;
    }


    public function saveImage(Image $image)
    {
        $image->resize(200, 200, Image::EXACT);
        $filename = $this->getFilename();

        $storageFile = $this->getStorageFilename($filename);

        $image->save($storageFile);

        return $this->getUrl($filename);
    }


    private function getFilename()
    {
        return Random::generate() . '.jpg';
    }


    private function getStorageFilename($filename)
    {
        return $this->uploadDir . '/' . $filename;
    }


    private function getUrl($filename)
    {
        $baseUrl = $this->request->getUrl()->getBaseUrl();
        return $baseUrl . $this->urlPrefix . '/' . $filename;
    }
}
