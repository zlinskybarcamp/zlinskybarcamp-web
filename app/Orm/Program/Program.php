<?php

namespace App\Orm;

use Nextras\Orm\Entity\Entity;

/**
 * @property int                        $id             {primary}
 * @property string|null                $type
 * @property Talk|null                  $talk           {m:1 Talk::$program}
 * @property string|null                $room
 * @property \DateInterval|null         $time
 * @property int|null                   $duration
 * @property string|null                $title
 * @property string|null                $speaker
 * @property \DateTimeImmutable         $created        {default now}
 */
class Program extends Entity
{
}
