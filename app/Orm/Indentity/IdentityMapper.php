<?php

namespace App\Orm;

use Nextras\Orm\Mapper\Mapper;

class IdentityMapper extends Mapper
{
    protected $tableName = 'user_identity';

    protected function createStorageReflection()
    {
        $reflection = parent::createStorageReflection();
        return $reflection;
    }
}
