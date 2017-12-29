<?php

namespace App\Presenters;

class HomepagePresenter extends BasePresenter
{
    public function __construct()
    {
        $this->isHp = true;
    }


    public function renderDefault()
    {
    }
}
