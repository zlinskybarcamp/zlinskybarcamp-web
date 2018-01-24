<?php

namespace App\Orm;

use Nextras\Orm\Repository\Repository;

class UserRoleRepository extends Repository
{
    public static function getEntityClassNames()
    {
        return [UserRole::class];
    }
}
