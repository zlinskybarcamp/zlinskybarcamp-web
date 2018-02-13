<?php

namespace App\Components\Program;

use App\Model\EventInfoProvider;
use App\Model\TalkManager;
use App\Orm\Program;
use Nette\Application\UI\Control;
use Tracy\Debugger;
use Tracy\ILogger;

class ProgramControl extends Control
{

    const HOUR_START = 9;
    const HOUR_END = 17;

    /**
     * @var EventInfoProvider
     */
    private $infoProvider;
    /**
     * @var TalkManager
     */
    private $talkManager;


    /**
     * ProgramControl constructor.
     * @param EventInfoProvider $infoProvider
     * @param TalkManager $talkManager
     */
    public function __construct(EventInfoProvider $infoProvider, TalkManager $talkManager)
    {

        $this->infoProvider = $infoProvider;
        $this->talkManager = $talkManager;
    }


    /**
     * @throws \Nette\Utils\JsonException
     * @throws \App\Model\InvalidEnumeratorSetException
     * @throws \Exception
     */
    public function render()
    {
        $this->template->setFile(__DIR__ . '/Program.latte');

        $this->template->dates = $this->infoProvider->getDates();

        $this->template->items = $this->getRenderableItems();
        bdump($this->template->items);

        $this->template->categories = $this->talkManager->getCategories();
        $this->template->rooms = $this->talkManager->getRooms();

        $this->template->render();
    }


    /**
     * @return array
     */
    private function getSortedItems()
    {
        $program = $this->talkManager->findAllProgram();

        $rooms = [];

        /** @var Program $programItem */
        foreach ($program as $programItem) {
            if (!isset($rooms[$programItem->room])) {
                $rooms[$programItem->room] = [];
            }

            $minutes = $this->dateIntervalToMinutes($programItem->time);
            $rooms[$programItem->room][$minutes] = new InternalProgramEnvelope($programItem);
        }

        foreach ($rooms as $key => $cat) {
            ksort($rooms[$key]);
        }

        return $rooms;
    }


    /**
     * @return array
     * @throws \Exception
     */
    public function getRenderableItems()
    {
        $renderStart = new \DateInterval(sprintf('PT%dH', self::HOUR_START));
        $renderEnd = new \DateInterval(sprintf('PT%dH', self::HOUR_END));

        $sortedItems = $this->getSortedItems();

        $renderableItems = [];
        foreach ($sortedItems as $roomKey => $roomItems) {
            $renderableItems[$roomKey] = [];
            $items = [];
            $prevEnd = $renderStart;

            /** @var InternalProgramEnvelope $program */
            foreach ($roomItems as $minutes => $program) {
                $spaceMinutes = $program->computePreviousSpaceMinutes($prevEnd);

                if ($spaceMinutes < 0) {
                    Debugger::log(
                        sprintf('Talk "%s" nelze vykreslit, překrývá se s jiným (nebo je před začátkem)', $program->title),
                        ILogger::WARNING
                    );
                    continue;
                }

                if ($spaceMinutes > 0) {
                    $items[] = $this->getSpacer($prevEnd, $spaceMinutes);
                }

                $items[] = $program;
                $prevEnd = $program->getEndTime();

            }
            $renderableItems[$roomKey] = $items;
        }

        return $renderableItems;
    }


    private function getSpacer(\DateInterval $start, $minutes)
    {
        return new InternalProgramVirtual('space', $start, $minutes);
    }


    /**
     * @param \DateInterval $dateInterval
     * @return int
     */
    private function dateIntervalToMinutes(\DateInterval $dateInterval)
    {
        $hours = intval($dateInterval->h);

        $minutes = intval($dateInterval->m);

        $minutes += max(0, $hours - self::HOUR_START) * 60;

        return $minutes;
    }
}
