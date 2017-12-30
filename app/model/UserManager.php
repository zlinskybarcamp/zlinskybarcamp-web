<?php

namespace App\Model;

use Nette;
use Nette\Security\Passwords;

/**
 * Users management.
 */
class UserManager implements Nette\Security\IAuthenticator
{
    use Nette\SmartObject;

    const
        TABLE_NAME = 'users',
        COLUMN_ID = 'id',
        COLUMN_PASSWORD_HASH = 'password',
        COLUMN_EMAIL = 'email',
        COLUMN_ROLE = 'role';


    /** @var Nette\Database\Context */
    private $database;


    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }


    /**
     * Performs an authentication.
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     * @throws Nette\Utils\JsonException
     */
    public function authenticate(array $credentials)
    {
        list($email, $password) = $credentials;

        $row = $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_EMAIL, $email)
            ->fetch();

        if (!$row) {
            throw new Nette\Security\AuthenticationException('The email is incorrect.', self::IDENTITY_NOT_FOUND);
        } elseif (!Passwords::verify($password, $row[self::COLUMN_PASSWORD_HASH])) {
            throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
        } elseif (Passwords::needsRehash($row[self::COLUMN_PASSWORD_HASH])) {
            $row->update([
                self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
            ]);
        }

        $arr = $row->toArray();

        if (isset($arr[self::COLUMN_ROLE])) {
            $arr[self::COLUMN_ROLE] = (array) Nette\Utils\Json::decode($arr[self::COLUMN_ROLE]);
        } else {
            $arr[self::COLUMN_ROLE] = [];
        }

        unset($arr[self::COLUMN_PASSWORD_HASH]);
        return new Nette\Security\Identity($arr[self::COLUMN_ID], $arr[self::COLUMN_ROLE], $arr);
    }


    /**
     * Adds new user.
     * @param  string
     * @param  string
     * @param  string
     * @return void
     * @throws DuplicateNameException
     */
    public function add($email, $password)
    {
        try {
            $this->database->table(self::TABLE_NAME)->insert([
                self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
                self::COLUMN_EMAIL => $email,
            ]);
        } catch (Nette\Database\UniqueConstraintViolationException $e) {
            throw new DuplicateNameException;
        }
    }
}
