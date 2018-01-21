<?php

namespace App\Orm;

use Nextras\Orm\Entity\Entity;
use Nextras\Orm\Relationships\OneHasMany;

/**
 * @property string                 $id            {primary}
 * @property string|null            $email
 * @property string|null            $name
 * @property string|null            $pictureUrl
 * @property OneHasMany|Identity[]  $identity       {1:m Identity::$user}
 * @property OneHasMany|Conferee[]  $conferee       {1:m Conferee::$user}
 * @property OneHasMany|Talk[]      $talk           {1:m Talk::$user}
 */
class User extends Entity
{

}
