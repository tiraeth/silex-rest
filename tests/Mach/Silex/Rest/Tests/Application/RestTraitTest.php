<?php


/*
 * This file is part of the Silex REST package.
 *
 * (c) Marcin Chwedziak <marcin@chwedziak.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mach\Silex\Rest\Tests\Application;

use Mach\Silex\Rest\Application\RestTrait;
use Mach\Silex\Rest\Provider\RestApplicationServiceProvider;
use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;

/**
 * @requires PHP 5.4
 */
class RestTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testResource()
    {
        $this->assertInstanceOf('Mach\Silex\Rest\Resource', $this->createApplication()->resource('/items'));
    }

    public function createApplication()
    {
        $app = new RestApplication();
        $app->register(new ServiceControllerServiceProvider());
        $app->register(new RestApplicationServiceProvider());

        return $app;
    }
}