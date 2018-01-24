<?php

namespace App\Orm;

use Nextras\Orm\Entity\Entity;
use Nextras\Orm\Relationships\OneHasMany;

/**
 * @property int                        $id            {primary}
 * @property User|null                  $user          {1:1 User::$conferee, isMain=true}
 * @property string                     $name
 * @property string                     $email
 * @property string|null                $pictureUrl
 * @property string|null                $bio
 * @property bool                       $allowMail      {default false}
 * @property \DateTimeImmutable|null    $consens
 * @property string|null                $extended
 * @property \DateTimeImmutable         $created        {default now}
 * @property OneHasMany|Talk[]          $talk           {1:m Talk::$conferee}
 */
class Conferee extends Entity
{

}
