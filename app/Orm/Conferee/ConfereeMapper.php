<?php

namespace App\Orm;

use Nextras\Orm\Mapper\Mapper;

class ConfereeMapper extends Mapper
{
    protected $tableName = 'conferee';

    protected function createStorageReflection()
    {
        $reflection = parent::createStorageReflection();
        $reflection->addMapping('pictureUrl', 'picture_url');
        $reflection->addMapping('allowMail', 'allow_mail');
        return $reflection;
    }
}
