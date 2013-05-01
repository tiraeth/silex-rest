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

use Mach\Silex\Rest\Provider\RestApplicationServiceProvider;
use Mach\Silex\Rest\Resource;
use Mach\Silex\Rest\Tests\Application\RestApplication;
use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ResourceTest extends \PHPUnit_Framework_TestCase
{
    public function testRestRoutesCreation()
    {
        $app = $this->createApplication();
        $res = $this->createResource($app);

        $this->assertInstanceOf('Silex\Controller', $res->route('cget'));
        $this->assertInstanceOf('Silex\Controller', $res->route('post'));
        $this->assertInstanceOf('Silex\Controller', $res->route('get'));
        $this->assertInstanceOf('Silex\Controller', $res->route('put'));
        $this->assertInstanceOf('Silex\Controller', $res->route('patch'));
        $this->assertInstanceOf('Silex\Controller', $res->route('delete'));
    }

    public function testRestRoutesHandling()
    {
        $app = $this->createApplication();
        $this->createResource($app);

        $this->assertEquals('cget', $app->handle(Request::create('/items'))->getContent());
        $this->assertEquals('post', $app->handle(Request::create('/items', 'POST'))->getContent());
        $this->assertEquals('get-1', $app->handle(Request::create('/items/1'))->getContent());
        $this->assertEquals('put-1', $app->handle(Request::create('/items/1', 'PUT'))->getContent());
        $this->assertEquals('patch-1', $app->handle(Request::create('/items/1', 'PATCH'))->getContent());
        $this->assertEquals('delete-1', $app->handle(Request::create('/items/1', 'DELETE'))->getContent());
    }

    public function testRestSubresourceHandling()
    {
        $app = $this->createApplication();
        $this->createSubresource($this->createResource($app));

        $app->error(function(\Exception $ex){ return 'no-route'; });

        $this->assertEquals('1-cget', $app->handle(Request::create('/items/1/comments'))->getContent());
        $this->assertEquals('1-get-1', $app->handle(Request::create('/items/1/comments/1'))->getContent());

        // The route below will throw an exception (handled by app error handler)
        // because subresource creator did not registered the PUT method
        $this->assertEquals('no-route', $app->handle(Request::create('/items/1/comments/1', 'PUT'))->getContent());
    }

    public function testConvertVariable()
    {
        $app = $this->createApplication();

        $res = $app['rest']->resource('/items')
            ->get(function(Request $request, $item){
                return "get-$item";
            })
            ->convert('item', function($item, Request $request){
                return '00' . $request->attributes->get('id');
            })
        ;

        $this->assertEquals('get-001', $app->handle(Request::create('/items/1'))->getContent());
    }

    public function testAssertId()
    {
        $app = $this->createApplication();

        $app->error(function(\Exception $e){ return 'fail'; });

        $res = $app['rest']->resource('/items')
            ->get(function(Request $request, $id){ return 'ok'; })
            ->assertId('\d+')
        ;

        $this->assertEquals('ok', $app->handle(Request::create('/items/1'))->getContent());
        $this->assertEquals('fail', $app->handle(Request::create('/items/one'))->getContent());
    }

    public function testMiddlewares()
    {
        $app = $this->createApplication();

        $res = $app['rest']->resource('/items')
            ->get(function(Request $request, $id){ return "get-$id"; })
            ->put(function(Request $request, $id){ return "put-$id"; })
            ->before('get', function(Request $request){
                $request->attributes->set('id', 2);
            })
        ;

        $this->assertEquals('get-2', $app->handle(Request::create('/items/1'))->getContent());
        $this->assertEquals('put-1', $app->handle(Request::create('/items/1', 'PUT'))->getContent());

        $res->after('get', function(Request $request, Response $response){
            $response->setContent('do-' . $response->getContent());
        });

        $this->assertEquals('do-get-2', $app->handle(Request::create('/items/1'))->getContent());
        $this->assertEquals('put-1', $app->handle(Request::create('/items/1', 'PUT'))->getContent());
    }

    public function testController()
    {
        $app = new Application();
        $app->register(new ServiceControllerServiceProvider());
        $app->register(new RestApplicationServiceProvider(), array(
            'rest.methods.cget' => 'all',
            'rest.methods.post' => 'create',
        ));

        $app->error(function(\Exception $e){
            return $e->getMessage();
        });

        $res = $app['rest']->resource('/items', new Controller($app));

        // Test route's custom handle method
        $this->assertEquals('cget', $app->handle(Request::create('/items'))->getContent());
        
        // Test disabled route
        $this->assertEquals('Resource does not support this method.', $app->handle(Request::create('/items', 'POST'))->getContent());
    }

    public function testControllerAsService()
    {
        $app = new Application();
        $app->register(new ServiceControllerServiceProvider());
        $app->register(new RestApplicationServiceProvider(), array(
            'rest.methods.cget' => 'all',
            'rest.methods.post' => 'create',
        ));

        $app->error(function(\Exception $e){
            return $e->getMessage();
        });

        $app['items.controller'] = $app->share(function($app){
            return new Controller($app);
        });

        $res = $app['rest']->resource('/items', 'items.controller');

        // Test route's custom handle method
        $this->assertEquals('cget', $app->handle(Request::create('/items'))->getContent());
        
        // Test disabled route
        $this->assertEquals('Resource does not support this method.', $app->handle(Request::create('/items', 'POST'))->getContent());
    }

    protected function createSubresource(Resource $res)
    {
        $subres = $res->subresource('/comments', null, 'cid');

        $subres->cget(function(Request $request, $id){ return "$id-cget"; });
        $subres->get(function(Request $request, $id, $cid){ return "$id-get-$cid"; });

        return $subres;
    }

    protected function createResource(Application $app)
    {
        $res = $app['rest']->resource('/items');

        $res->cget(function(Request $request){ return 'cget'; });
        $res->post(function(Request $request){ return 'post'; });
        $res->get(function(Request $request, $id){ return "get-$id"; });
        $res->put(function(Request $request, $id){ return "put-$id"; });
        $res->patch(function(Request $request, $id){ return "patch-$id"; });
        $res->delete(function(Request $request, $id){ return "delete-$id"; });

        return $res;
    }

    protected function createApplication()
    {
        $app = new Application();
        $app->register(new ServiceControllerServiceProvider());
        $app->register(new RestApplicationServiceProvider());

        return $app;
    }
}