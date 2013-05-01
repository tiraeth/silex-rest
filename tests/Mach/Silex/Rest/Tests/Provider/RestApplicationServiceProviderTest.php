<?php

/*
 * This file is part of the Silex REST package.
 *
 * (c) Marcin Chwedziak <marcin@chwedziak.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mach\Silex\Rest\Tests\Provider;

use Mach\Silex\Rest\Provider\RestApplicationServiceProvider;
use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;

class RestApplicationServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testRestServiceAvailability()
    {
        $app = new Application();
        $app->register(new ServiceControllerServiceProvider());
        $app->register(new RestApplicationServiceProvider());

        $this->assertInstanceOf('Mach\Silex\Rest\RestService', $app['rest']);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Register ServiceControllerServiceProvider first.
     */
    public function testServiceControllerServiceProviderRequirement()
    {
        $app = new Application();
        $app->register(new RestApplicationServiceProvider());
    }

    public function testServiceCreatesResource()
    {
        $app = new Application();
        $app->register(new ServiceControllerServiceProvider());
        $app->register(new RestApplicationServiceProvider());

        $resource = $app['rest']->resource('/items');

        $this->assertInstanceOf('Mach\Silex\Rest\Resource', $resource);
    }
}