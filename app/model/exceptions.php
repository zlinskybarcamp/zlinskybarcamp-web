<?php

namespace App\Model;

class DuplicateNameException extends \Exception
{
}



class IdentityNotFoundException extends \Exception
{
}



class NoUserLoggedIn extends \Exception
{
}



class EntityNotFound extends \Exception
{
}



class UserNotFound extends EntityNotFound
{
}



class ConfereeNotFound extends EntityNotFound
{
}