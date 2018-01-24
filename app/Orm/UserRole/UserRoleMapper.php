<?php

namespace App\Orm;

use Nextras\Orm\Mapper\Mapper;

class UserRoleMapper extends Mapper
{
    protected $tableName = 'user_role';

    protected function createStorageReflection()
    {
        $reflection = parent::createStorageReflection();
        return $reflection;
    }
}
