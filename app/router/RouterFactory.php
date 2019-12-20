<?php

declare(strict_types=1);

namespace App;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

class RouterFactory
{
    use Nette\StaticClass;


    /**
     * @return Nette\Application\IRouter
     */
    public static function createRouter()
    {

        $adminRouter = new RouteList('Admin');
        $adminRouter[] = new Route('admin/<presenter>/<action>', 'Dashboard:default');

        $router = new RouteList;
        $router[] = $adminRouter;

        $router[] = new Route('<action>', 'Homepage:default');
        $router[] = new Route('sign/facebook', [
            'presenter' => 'Sign',
            'action' => 'federatedLogin',
            'platform' => 'facebook',
        ]);
        $router[] = new Route('sign/facebook/callback', [
            'presenter' => 'Sign',
            'action' => 'federatedCallback',
            'platform' => 'facebook',
        ]);
        $router[] = new Route('<presenter>/<action>[/<id \d+>]');

        return $router;
    }
}
