<?php

namespace App\Model;

class DuplicateNameException extends \Exception
{
}



class IdentityNotFoundException extends \Exception
{
}


class AuthenticationException extends \Exception
{
}



class UserNotFoundException extends AuthenticationException
{
}



class PasswordMismatchException extends AuthenticationException
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



class TalkNotFound extends EntityNotFound
{
}