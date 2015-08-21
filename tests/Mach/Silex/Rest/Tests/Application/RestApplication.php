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
use Silex\Application;

class RestApplication extends Application
{
    use RestTrait;
}
