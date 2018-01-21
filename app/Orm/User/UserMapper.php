<?php

namespace App\Orm;

use Nextras\Orm\Mapper\Mapper;

class UserMapper extends Mapper
{
    protected $tableName = 'user';

    protected function createStorageReflection()
    {
        $reflection = parent::createStorageReflection();
        $reflection->addMapping('pictureUrl', 'picture_url');
        return $reflection;
    }
}
