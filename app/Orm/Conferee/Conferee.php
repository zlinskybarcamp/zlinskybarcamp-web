<?php

namespace App\Orm;

use Nextras\Orm\Entity\Entity;

/**
 * @property int                        $id            {primary}
 * @property User|null                  $user          {m:1 User::$conferee}
 * @property string                     $name
 * @property string                     $email
 * @property string|null                $pictureUrl
 * @property string|null                $bio
 * @property bool                       $allowMail
 * @property \DateTimeImmutable|null    $consens
 * @property string|null                $extended
 * @property \DateTimeImmutable         $created        {default now}
 */
class Conferee extends Entity
{

}
