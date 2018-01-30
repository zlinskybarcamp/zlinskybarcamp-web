<?php

namespace App\Model;

use Nette\Database;
use Nette\Utils\Json;

class ConfigManager
{
    const TABLE_NAME = 'config';
    const COLUMN_ID = 'id';
    const COLUMN_VALUE = 'value';


    /**
     * @var Database\Context
     */
    private $database;

    /**
     * @var array|null
     */
    private $configs;


    /**
     * ConfigManager constructor.
     * @param Database\Context $database
     */
    public function __construct(Database\Context $database)
    {
        $this->database = $database;
    }


    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     * @throws \Nette\Utils\JsonException
     */
    public function get($key, $default = null)
    {
        $configs = $this->load();

        if (isset($configs[$key])) {
            return $configs[$key];
        } else {
            return $default;
        }
    }


    /**
     * @param bool $force
     * @return array
     * @throws \Nette\Utils\JsonException
     */
    private function load($force = false)
    {
        if ($this->configs !== null || $force) {
            return $this->configs;
        }

        $this->configs = [];

        $configs = $this->database->table(self::TABLE_NAME);

        foreach ($configs as $row) {
            $key = $row[self::COLUMN_ID];
            $value = $row[self::COLUMN_VALUE];
            $this->configs[$key] = Json::decode($value, Json::FORCE_ARRAY);
        }

        return $this->configs;
    }


    /**
     * @param string $key
     * @param mixed $value
     * @throws \Nette\Utils\JsonException
     */
    public function set($key, $value)
    {
        $configs = $this->load();

        $this->configs[$key] = $value;

        $this->saveOne($key, $value);
    }


    /**
     * @param string $key
     * @param mixed $value
     * @throws \Nette\Utils\JsonException
     */
    private function saveOne($key, $value)
    {
        $json = Json::encode($value);

        $tableName = self::TABLE_NAME;

        $values = [
            [
                'id' => $key,
                'value' => $json,
            ]
        ];

        $updateStatement = [
            'value' => new Database\SqlLiteral("VALUES(`value`)")
        ];

        $this->database->query(
            'INSERT INTO ?name ?values ON DUPLICATE KEY UPDATE ?;',
            $tableName,
            $values,
            $updateStatement
        );
    }
}
