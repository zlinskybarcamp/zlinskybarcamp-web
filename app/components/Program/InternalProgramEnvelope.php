<?php

namespace App\Components\Program;

use App\Orm\Program;

/**
 * Class InternalProgramEnvelope
 * @package App\components\Program
 */
class InternalProgramEnvelope extends InternalProgram
{
    /**
     * @var Program
     */
    private $program;


    /**
     * InternalProgramEnvelope constructor.
     * @param Program $program
     */
    public function __construct(Program $program)
    {
        $this->program = $program;
    }


    /**
     * @return \DateInterval
     */
    public function getTime()
    {
        return $this->program->time;
    }


    /**
     * @return int
     */
    public function getDuration()
    {
        return $this->program->duration;
    }


    /**
     * @return int
     */
    public function getTalkId()
    {
        return $this->program->talk ? $this->program->talk->id : null;
    }


    /**
     * @return null|string
     */
    public function getTitle()
    {
        if (empty($this->program->title) && $this->program->talk) {
            return $this->program->talk->title;
        } else {
            return $this->program->title;
        }
    }


    /**
     * @return null|string
     */
    public function getSpeaker()
    {
        if (empty($this->program->speaker) && $this->program->talk) {
            return $this->program->talk->conferee->name;
        } else {
            return $this->program->speaker;
        }
    }


    /**
     * @return null|string
     */
    public function getType()
    {
        return $this->program->type;
    }
}