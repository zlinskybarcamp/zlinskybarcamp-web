<?php

namespace App\Components\Program;

/**
 * Class InternalProgramVirtual
 * @package App\Components\Program
 */
class InternalProgramVirtual extends InternalProgram implements IInternalProgram
{
    /**
     * @var string|null
     */
    private $title;
    /**
     * @var string|null
     */
    private $speaker;
    /**
     * @var string
     */
    private $type;
    /**
     * @var \DateInterval
     */
    private $time;
    /**
     * @var int
     */
    private $duration;


    /**
     * InternalProgramVirtual constructor.
     * @param $type
     * @param \DateInterval $time
     * @param $duration
     */
    public function __construct($type, \DateInterval $time, $duration)
    {
        $this->type = $type;
        $this->time = $time;
        $this->duration = $duration;
    }


    /**
     * @return string
     */
    public function getTalkId()
    {
        return null;
    }


    /**
     * @return \DateInterval
     */
    public function getTime()
    {
        return $this->time;
    }


    /**
     * @param \DateInterval $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }


    /**
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }


    /**
     * @param int $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }


    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }


    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }


    /**
     * @return mixed
     */
    public function getSpeaker()
    {
        return $this->speaker;
    }


    /**
     * @param mixed $speaker
     */
    public function setSpeaker($speaker)
    {
        $this->speaker = $speaker;
    }
}
