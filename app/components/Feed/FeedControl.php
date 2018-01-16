<?php

namespace App\Components\Feed;

use App\Model\WordpressPostReader;
use Nette\Application\UI\Control;

class FeedControl extends Control
{
    /**
     * @var WordpressPostReader
     */
    private $postReader;


    public function __construct(WordpressPostReader $postReader)
    {
        parent::__construct();
        $this->postReader = $postReader;
    }


    public function render()
    {
        $this->template->setFile(__DIR__ . '/Feed.latte');
        $this->template->feed = $this->postReader->get();
        $this->template->sourceUrl = $this->postReader->getSourceUrl();
        $this->template->render();
    }
}
