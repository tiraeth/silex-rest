<?php

/*
 * This file is part of the Silex REST package.
 *
 * (c) Marcin Chwedziak <marcin@chwedziak.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mach\Silex\Rest;

use Silex\Application;

class RestService
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function resource($path, $controller = null)
    {
        return new Resource($this->app, $path, $controller);
    }
}