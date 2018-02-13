<?php

namespace App\Components\Program;

interface IProgramControlFactory
{

    /**
     * @return ProgramControl
     */
    public function create();

}
