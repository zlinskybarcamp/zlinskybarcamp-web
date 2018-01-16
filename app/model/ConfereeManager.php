<?php

namespace App\Model;

use Nette\Database;
use Nette\Utils\DateTime;
use Nette\Utils\Json;

class ConfereeManager
{

    const TABLE_NAME = 'conferee';
    const COLUMN_ID = 'id';
    const COLUMN_VALUE = 'value';
    /**
     * @var Database\Context
     */
    private $database;


    public function __construct(Database\Context $database)
    {
        $this->database = $database;
    }


    public function fromForm($values)
    {
        $data = [
            'name' => $values->name,
            'email' => $values->email,
            'bio' => $values->bio,
            'allow_mail'=> $values->allow_mail,
            'consens' => $values->consens ? new DateTime() : null,
            'extended' => Json::encode([
                'extendedOrganization'=> $values->extendedOrganization,
                'extendedAddress'=> $values->extendedAddress,
            ]),
        ];
        
        $this->save($data);
    }


    public function save($data)
    {
        $this->database->table(self::TABLE_NAME)
            ->insert($data);
    }
}
