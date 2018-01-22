<?php

namespace App\Orm;

use Nextras\Orm\Entity\Entity;

/**
 * @property int                        $id             {primary}
 * @property Conferee|null              $conferee       {m:1 Conferee::$talk}
 * @property string                     $title
 * @property string|null                $description
 * @property string|null                $purpose
 * @property string|null                $category
 * @property string|null                $company
 * @property string|null                $extended
 * @property \DateTimeImmutable         $created        {default now}
 */
class Talk extends Entity
{

}
