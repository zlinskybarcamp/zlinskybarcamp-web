<?php

namespace App\Orm;

use Nextras\Orm\Repository\Repository;

class UserRepository extends Repository
{
    public static function getEntityClassNames(): array
    {
        return [User::class];
    }
}
