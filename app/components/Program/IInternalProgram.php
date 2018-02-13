<?php

namespace App\Components\Program;

/**
 * Interface IInternalProgram
 * @package App\Components\Program
 */
interface IInternalProgram
{
    /**
     * @return string
     */
    public function getTitle();


    /**
     * @return string
     */
    public function getSpeaker();


    /**
     * @return string
     */
    public function getType();


    /**
     * @return \DateInterval
     */
    public function getTime();


    /**
     * @return int
     */
    public function getDuration();

}
