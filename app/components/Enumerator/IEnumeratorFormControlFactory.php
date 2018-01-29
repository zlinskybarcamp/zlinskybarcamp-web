<?php

namespace App\Components\Enumerator;

interface IEnumeratorFormControlFactory
{

    /**
     * @param string $setName Name set name (in database)
     * @return EnumeratorFormControl
     */
    public function create($setName);

}
