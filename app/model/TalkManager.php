<?php

namespace App\Model;

use Nette\Database;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;
use Nette\Utils\Json;

class TalkManager
{

    const TABLE_NAME = 'talk';

    /**
     * @var Database\Context
     */
    private $database;


    /**
     * TalkManager constructor.
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
            'title' => $values->title,
            'description' => $values->description,
            'purpose' => $values->purpose,
            'category' => $values->category,
            'company' => $values->company,
            'extended' => Json::encode([
                'requested_duration' => $values->duration,
                'url' => [
                    'www' => $values->url_www,
                    'facebook' => $values->url_facebook,
                    'twitter' => $values->url_twitter,
                    'google' => $values->url_google,
                    'linkedin' => $values->url_linkedin,
                ],
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


    /**
     * @return array
     */
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
