<?php

namespace App\Components\Feed;

use App\Model\WordpressPostReader;

class FeedFactory
{
    /**
     * @var WordpressPostReader
     */
    private $postReader;


    public function __construct(WordpressPostReader $postReader)
    {
        $this->postReader = $postReader;
    }


    public function create()
    {
        return new FeedControl($this->postReader);
    }
}
