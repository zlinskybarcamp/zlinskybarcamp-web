<?php

namespace App\Orm;

use App\Model\ConfereeNotFound;
use Nextras\Orm\Entity\Entity;
use Nextras\Orm\Relationships\OneHasMany;

/**
 * @property string                 $id            {primary}
 * @property string|null            $email
 * @property string|null            $name
 * @property string|null            $pictureUrl
 * @property OneHasMany|Identity[]  $identity       {1:m Identity::$user}
 * @property Conferee|null          $conferee       {1:1 Conferee::$user}
 * @property OneHasMany|UserRole[]  $role           {1:m UserRole::$user}
 */
class User extends Entity
{
    /**
     * @return Conferee
     * @throws ConfereeNotFound
     */
    public function getObligatoryConferee()
    {
        $conferee = $this->conferee;

        if ($conferee === null) {
            throw new ConfereeNotFound();
        }

        return $conferee;
    }


    /**
     * @param $roleName
     */
    public function addRole($roleName)
    {
        $role = new UserRole();
        $role->role = $roleName;
        $role->user = $this;
    }
}
