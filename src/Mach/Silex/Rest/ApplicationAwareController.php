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

use BadMethodCallException;
use Silex\Application;

abstract class ApplicationAwareController
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this->app, $method)) {
            return call_user_func_array(array($this->app, $method), $arguments);
        }

        throw new BadMethodCallException(sprintf('Method "%s" not found.', $method));
    }

    public function disable()
    {
        $this->abort(404, 'Resource does not support this method.');
    }
}
