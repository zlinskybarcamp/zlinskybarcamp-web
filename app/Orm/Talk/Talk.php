<?php

namespace App\Orm;

use Nextras\Orm\Entity\Entity;

/**
 * @property int                        $id            {primary}
 * @property User|null                  $user          {m:1 User::$talk}
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