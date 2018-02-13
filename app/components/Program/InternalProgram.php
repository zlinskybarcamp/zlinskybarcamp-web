<?php

namespace App\Components\Program;

use Nette\SmartObject;

/**
 * Class InternalProgram
 * @package App\Components\Program
 * @property-read string $title
 * @property-read string $speaker
 * @property-read \DateInterval $time
 * @property-read string $startClock
 * @property-read string $endClock
 * @property-read string $type
 * @property-read string $duration
 */
abstract class InternalProgram implements IInternalProgram
{
    use SmartObject;

    /**
     * @return string
     */
    abstract public function getTitle();


    /**
     * @return string
     */
    abstract public function getSpeaker();


    /**
     * @return string
     */
    abstract public function getType();


    /**
     * @return \DateInterval
     */
    abstract public function getTime();


    /**
     * @return int
     */
    abstract public function getDuration();


    /**
     * @return \DateInterval
     * @throws \Exception
     */
    public function getEndTime()
    {
        $start = $this->getTime();
        $end = new \DateInterval('PT0H');
        $h = $start->h;
        $i = $start->i + $this->getDuration();

        while ($i >= 60) {
            $i -= 60;
            $h++;
        }

        $end->i = $i;
        $end->h = $h;
        return $end;
    }


    /**
     * @return string
     */
    public function getStartClock()
    {
        $start = $this->getTime();
        return $start->format('%H:%I');
    }


    /**
     * @return string
     * @throws \Exception
     */
    public function getEndClock()
    {
        $end = $this->getEndTime();
        return $end->format('%H:%I');
    }


    /**
     * @param \DateInterval $previousEnd
     * @return float|int
     */
    public function computePreviousSpaceMinutes(\DateInterval $previousEnd)
    {
        $start = $this->getTime();

        $space = ($start->h - $previousEnd->h) * 60;
        $space += $start->i - $previousEnd->i;

        return $space;
    }


}
