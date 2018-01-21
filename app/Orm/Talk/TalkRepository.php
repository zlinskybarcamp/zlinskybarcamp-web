<?php

namespace App\Orm;

use Nextras\Orm\Repository\Repository;

class TalkRepository extends Repository
{
    public static function getEntityClassNames()
    {
        return [Talk::class];
    }
}
