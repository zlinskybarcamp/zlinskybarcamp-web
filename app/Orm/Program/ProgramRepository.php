<?php

namespace App\Orm;

use Nextras\Orm\Repository\Repository;

class ProgramRepository extends Repository
{
    public static function getEntityClassNames(): array
    {
        return [Program::class];
    }
}
