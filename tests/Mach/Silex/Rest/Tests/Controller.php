<?php

/*
 * This file is part of the Silex REST package.
 *
 * (c) Marcin Chwedziak <marcin@chwedziak.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mach\Silex\Rest\Tests;

use Mach\Silex\Rest\ApplicationAwareController;
use Symfony\Component\HttpFoundation\Request;

class Controller extends ApplicationAwareController
{
    public function all(Request $request)
    {
        return 'cget';
    }

    public function create(Request $request)
    {
        $this->disable();
    }
}