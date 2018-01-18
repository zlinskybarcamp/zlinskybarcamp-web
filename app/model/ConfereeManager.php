<?php

namespace App\Model;

use Nette\Database;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;
use Nette\Utils\Json;

class ConfereeManager
{

    const TABLE_NAME = 'conferee';

    /**
     * @var Database\Context
     */
    private $database;


    /**
     * ConfereeManager constructor.
     * @param Database\Context $database
     */
    public function __construct(Database\Context $database)
    {
        $this->database = $database;
    }


    /**
     * @param ArrayHash $values
     * @throws \Nette\Utils\JsonException
     */
    public function fromForm($values)
    {
        $data = [
            'name' => $values->name,
            'email' => $values->email,
            'bio' => $values->bio,
            'allow_mail' => $values->allow_mail,
            'consens' => $values->consens ? new DateTime() : null,
            'extended' => Json::encode([
                'company' => $values->extendedCompany,
                'address' => $values->extendedAddress,
            ]),
        ];

        $this->save($data);
    }


    /**
     * @param array $data
     */
    public function save($data)
    {

        $data += [
            'created' => new DateTime()
        ];

        $this->database->table(self::TABLE_NAME)
            ->insert($data);
    }
}
