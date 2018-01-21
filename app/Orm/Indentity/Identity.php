<?php

namespace App\Orm;

use Nextras\Orm\Entity\Entity;


/**
 * @property int            $id            {primary}
 * @property string         $key
 * @property string         $platform
 * @property User|null      $user          {m:1 User::$identity}
 * @property string|null    $identity
 * @property string|null    $token
 */
class Identity extends Entity
{

}
