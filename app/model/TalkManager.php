<?php

namespace App\Model;

use App\Orm\Orm;
use App\Orm\Program;
use App\Orm\ProgramRepository;
use App\Orm\Talk;
use App\Orm\TalkRepository;
use Nette\Database\Context;
use Nette\Database\ForeignKeyConstraintViolationException;
use Nette\Database\Table\ActiveRow;

class TalkManager
{
    const TABLE_TALK_VOTES_NAME = 'talk_votes';
    const COLUMN_USER_ID = 'user_id';
    const COLUMN_TALK_ID = 'talk_id';

    /** @var TalkRepository $talkRepository */
    private $talkRepository;
    /** @var ProgramRepository $talkRepository */
    private $programRepository;
    /** @var Context */
    private $database;
    /**
     * @var EnumeratorManager
     */
    private $enumerator;


    /**
     * TalkManager constructor.
     * @param Orm $orm
     * @param Context $database
     * @param EnumeratorManager $enumerator
     */
    public function __construct(Orm $orm, Context $database, EnumeratorManager $enumerator)
    {
        $this->talkRepository = $orm->talk;
        $this->programRepository = $orm->program;

        $this->database = $database;
        $this->enumerator = $enumerator;
    }


    /**
     * @param Talk $talk
     */
    public function save(Talk $talk)
    {
        $this->talkRepository->persistAndFlush($talk);
    }


    /**
     * @param Program $program
     */
    public function saveProgram(Program $program)
    {
        $this->programRepository->persistAndFlush($program);
    }


    /**
     * @return array
     * @throws InvalidEnumeratorSetException
     * @throws \Nette\Utils\JsonException
     */
    public function getCategories()
    {
        return $this->enumerator->getPairs(EnumeratorManager::SET_TALK_CATEGORIES);
    }


    /**
     * @return array
     * @throws InvalidEnumeratorSetException
     * @throws \Nette\Utils\JsonException
     */
    public function getDurations()
    {
        return $this->enumerator->getPairs(EnumeratorManager::SET_TALK_DURATIONS);
    }


    /**
     * @return array
     * @throws InvalidEnumeratorSetException
     * @throws \Nette\Utils\JsonException
     */
    public function getRooms()
    {
        return $this->enumerator->getPairs(EnumeratorManager::SET_TALK_ROOMS);
    }


    /**
     * @param int $userId
     * @return array
     */
    public function getUserVotes($userId)
    {
        $talkIds = [];
        $res = $this->database->table(self::TABLE_TALK_VOTES_NAME)
            ->where(self::COLUMN_USER_ID, $userId);

        /** @var ActiveRow $row */
        foreach ($res as $row) {
            $talkId = $row->talk_id;
            $talkIds[$talkId] = $talkId;
        }

        return $talkIds;
    }


    /**
     * @param int $userId
     * @param int $talkId
     * @throws ForeignKeyConstraintViolationException
     */
    public function addVote($userId, $talkId)
    {
        $this->database->table(self::TABLE_TALK_VOTES_NAME)
            ->insert([
                self::COLUMN_USER_ID => (int)$userId,
                self::COLUMN_TALK_ID => (int)$talkId,
            ]);

        $this->recountVote($talkId);
    }


    /**
     * @param int $userId
     * @param int $talkId
     */
    public function removeVote($userId, $talkId)
    {
        $this->database->table(self::TABLE_TALK_VOTES_NAME)
            ->where([
                self::COLUMN_USER_ID => (int)$userId,
                self::COLUMN_TALK_ID => (int)$talkId,
            ])->delete();

        $this->recountVote($talkId);
    }


    /**
     * @param int $talkId
     */
    protected function recountVote($talkId)
    {
        $result = $this->database
            ->query('SELECT COUNT(*) as `count` FROM `talk_votes` WHERE `talk_id` = ?', $talkId)
            ->fetch();
        $sum = $result['count'];

        /** @var Talk $talk */
        $talk = $this->talkRepository->getById($talkId);
        $talk->votes = $sum;
        $this->talkRepository->persistAndFlush($talk);
    }


    /**
     * @param int $id
     * @return Talk|null
     */
    public function getById($id)
    {
        return $this->talkRepository->getById($id);
    }


    /**
     * @param $id
     * @return Program|null
     */
    public function getProgramById($id)
    {
        return $this->programRepository->getById($id);
    }


    /**
     * @return \Nextras\Orm\Collection\ICollection
     */
    public function findActive()
    {
        return $this->talkRepository->findBy([
            'enabled' => true
        ]);
    }


    /**
     * @return \Nextras\Orm\Collection\ICollection
     */
    public function findAll()
    {
        return $this->talkRepository->findAll();
    }


    /**
     * @return \Nextras\Orm\Collection\ICollection
     */
    public function findAllProgram()
    {
        return $this->programRepository->findAll();
    }


    /**
     * @return array
     */
    public function getDurationChoice()
    {
        $choice = [];
        foreach (range(5, 120, 5) as $min) {
            $choice[$min]= "$min minut";
        }
        return $choice;
    }
}
