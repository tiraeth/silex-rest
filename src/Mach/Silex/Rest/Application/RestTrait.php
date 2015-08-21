<?php

/*
 * This file is part of the Silex REST package.
 *
 * (c) Marcin Chwedziak <marcin@chwedziak.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mach\Silex\Rest\Application;

trait RestTrait
{
    public function resource($path, $controller = null)
    {
        return $this['rest']->resource($path, $controller);
    }
}
