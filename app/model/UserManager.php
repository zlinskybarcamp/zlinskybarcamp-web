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
        TABLE_USER = 'user',
        TABLE_ROLE = 'user_role',
        COLUMN_ID = 'id',
        COLUMN_PASSWORD_HASH = 'password',
        COLUMN_EMAIL = 'email',
        COLUMN_USER_ID = 'user_id',
        COLUMN_ROLE = 'role';


    /** @var Nette\Database\Context */
    private $database;


    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }


    /**
     * Performs an authentication.
     * @param array $credentials
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials)
    {
        list($email, $password) = $credentials;

        $userRow = $this->database->table(self::TABLE_USER)
            ->where(self::COLUMN_EMAIL, $email)
            ->fetch();

        if (!$userRow) {
            throw new Nette\Security\AuthenticationException('The email is incorrect.', self::IDENTITY_NOT_FOUND);
        } elseif (!Passwords::verify($password, $userRow[self::COLUMN_PASSWORD_HASH])) {
            throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
        } elseif (Passwords::needsRehash($userRow[self::COLUMN_PASSWORD_HASH])) {
            $userRow->update([
                self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
            ]);
        }

        $user = $userRow->toArray();
        unset($user[self::COLUMN_PASSWORD_HASH]);

        $user['roles'] = [];
        foreach ($userRow->related(self::TABLE_ROLE, self::COLUMN_USER_ID) as $roleRow) {
            $user['roles'][] = $roleRow[self::COLUMN_ROLE];
        }

        return new Nette\Security\Identity($user[self::COLUMN_ID], $user['roles'], $user);
    }


    /**
     * Adds new user.
     * @param string $email
     * @param string $password
     * @return void
     * @throws DuplicateNameException
     */
    public function add($email, $password)
    {
        try {
            $this->database->table(self::TABLE_USER)->insert([
                self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
                self::COLUMN_EMAIL => $email,
            ]);
        } catch (Nette\Database\UniqueConstraintViolationException $e) {
            throw new DuplicateNameException();
        }
    }
}
