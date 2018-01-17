<?php

namespace App\Model;

use Nette\Database;
use Nette\Utils\DateTime;
use Nette\Utils\Json;

class TalkManager
{

    const TABLE_NAME = 'talk';

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
            'title' => $values->title,
            'description' => $values->description,
            'purpose' => $values->purpose,
            'extended' => Json::encode([
                'requested_duration' => $values->duration
            ]),
        ];

        $this->save($data);
    }


    public function save($data)
    {
        $data += [
            'created' => new DateTime()
        ];

        $this->database->table(self::TABLE_NAME)
            ->insert($data);
    }


    public function getCategories()
    {
        return [
            'teambuilding' => 'Teambuilding',
            'seo' => 'SEO',
            'media' => 'Marketing a média',
            'leadership' => 'Leadership'
        ];
    }
}
