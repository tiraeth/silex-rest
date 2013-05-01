<?php

/*
 * This file is part of the Silex REST package.
 *
 * (c) Marcin Chwedziak <marcin@chwedziak.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mach\Silex\Rest\Provider;

use Mach\Silex\Rest\RestService;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Silex\ServiceControllerResolver;

class RestApplicationServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        if (!($app['resolver'] instanceof ServiceControllerResolver)) {
            throw new \RuntimeException('Register ServiceControllerServiceProvider first.');
        }

        foreach (array('cget', 'post', 'get', 'put', 'patch', 'delete') as $method) {
            $app['rest.methods.' . $method] = $method;
        }

        $app['rest'] = $app->share(function($app){
            return new RestService($app);
        });
    }

    public function boot(Application $app)
    {
    }
}