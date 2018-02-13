<?php

namespace App\Orm;

use Nextras\Orm\Repository\Repository;

class ProgramRepository extends Repository
{
    public static function getEntityClassNames()
    {
        return [Program::class];
    }
}
