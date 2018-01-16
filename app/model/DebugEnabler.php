<?php

namespace App\Model;

use Nette\InvalidStateException;
use Nette\Utils\Random;

class DebugEnabler
{
    /**
     * @var string
     */
    private static $workDir = null;

    /**
     * @var string
     */
    private static $tokenFile = '/debug/token.bin';

    /**
     * @var string
     */
    private static $debugCookieName = 'debug-token';


    /**
     * @return bool
     */
    public static function isDebug()
    {
        return self::isDebugByEnv() || self::isDebugByToken();
    }


    /**
     * @return bool
     */
    public static function isDebugByEnv()
    {
        return intval(getenv('NETTE_DEBUG')) === 1;
    }


    /**
     * @return bool
     */
    public static function isDebugByToken()
    {
        return isset($_COOKIE[self::$debugCookieName])
            && ($_COOKIE[self::$debugCookieName] === self::getToken());
    }


    /**
     * @return string
     */
    private static function getToken()
    {
        $tokenFile = self::getTokenFile();
        if (!file_exists($tokenFile)) {
            return self::createToken($tokenFile);
        }

        return file_get_contents($tokenFile);
    }


    /**
     * @return string
     */
    private static function getTokenFile()
    {
        if (self::$workDir === null) {
            throw new InvalidStateException('WorkDir is not defined');
        }
        $tokenFile = self::$workDir . self::$tokenFile;
        return $tokenFile;
    }


    /**
     * @return string
     */
    private static function generateToken()
    {
        return Random::generate(30);
    }


    /**
     * @param string $workDir
     */
    public static function setWorkDir($workDir)
    {
        self::$workDir = $workDir;
    }


    /**
     *
     */
    public static function turnOn()
    {
        $token = self::getToken();
        setcookie(
            self::$debugCookieName,
            $token,
            (time() + 3600),
            '/',
            '',
            true,
            true
        );
    }


    /**
     *
     */
    public static function turnOff()
    {
        setcookie(
            self::$debugCookieName,
            '',
            (time() - 3600),
            '/',
            '',
            true,
            true
        );
    }


    /**
     * @param string $tokenFile
     * @return string
     */
    private static function createToken($tokenFile)
    {
        $token = self::generateToken();
        $dirname = dirname($tokenFile);
        if (!file_exists($dirname)) {
            mkdir($dirname, 0777, true);
        }
        file_put_contents($tokenFile, $token);
        return $token;
    }
}
