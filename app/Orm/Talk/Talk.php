<?php

namespace App\Orm;

use Nextras\Orm\Entity\Entity;

/**
 * @property int                        $id             {primary}
 * @property Conferee|null              $conferee       {m:1 Conferee::$talk}
 * @property OneHasMany|Program[]|null  $program        {1:m Program::$talk}
 * @property string                     $title
 * @property string|null                $description
 * @property string|null                $purpose
 * @property int                        $enabled        {default 1}
 * @property int                        $votes          {default 0}
 * @property string|null                $category
 * @property string|null                $company
 * @property string|null                $notes
 * @property string|null                $extended
 * @property \DateTimeImmutable         $created        {default now}
 */
class Talk extends Entity
{

}
