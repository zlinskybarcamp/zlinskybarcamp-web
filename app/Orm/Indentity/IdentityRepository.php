<?php

namespace App\Orm;

use Nextras\Orm\Repository\Repository;

class IdentityRepository extends Repository
{
    public static function getEntityClassNames()
    {
        return [Identity::class];
    }
}
