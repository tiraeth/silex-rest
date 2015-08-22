# RestApplicationServiceProvider

[![Build Status](https://travis-ci.org/tiraeth/silex-rest.png)](https://travis-ci.org/tiraeth/silex-rest)

__RestApplicationServiceProvider__ for Silex gives developers ability to rapidly create REST applications.

Few words about the conversions are needed. This provider gives you a service to build RESTful routes faster and more consistently across the whole application. While you can create ```GET```, ```POST```, ```PUT```, ```PATCH```, and ```DELETE``` actions, not all can be used with individual items or collections. The endpoint should be defined as a plural name of the item type, e.g. ```/users``` (but you are not limited by the provider and can use whatever convention you like).

* ```GET``` can be used to fetch the whole collection under ```/users``` or a single object under ```/users/{id}```,
* ```POST``` can be used to insert new item under ```/users```,
* ```PUT``` can be used to fully update existing item under ```/users/{id}```,
* ```PATCH``` can be used to partially update existing item under ```/users/{id}```,
* ```DELETE``` can be used to remove existing item under ```/users/{id}```.

Unfortunately there is no option to automatically create custom route for ```PATCH``` action, e.g. ```/users/{id}/activate```, but you can still do it manually by adding such route to ```$app```.

The library requires you to have ```ServiceControllerServiceProvider``` enabled because I recommend you to use a class for a resource's controller. This way you can keep your application well-organized and reuse the controllers in Symfony2, for instance. And if you decide to keep your controllers with Silex only, you can use ```ApplicationAwareController``` which implements ```disable()``` to fastly throw 404 in case of your will to hide some actions from users, and delegates method calls to ```$app``` (passed by constructor) if needed.


## Installation w/ Composer

1. Add requirement using CLI: ```php composer.phar require "mach/silex-rest:~1.0"```.
2. Update the requirement ```php composer.phar update mach/silex-rest```.

Alternatively you can add the requirement manually:

```json
{
    ...
    "require": {
        ...
        "mach/silex-rest": "~1.0",
        ...
    },
    ...
}
```

## Usage

### Registering the provider

```php
<?php

use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;
use Mach\Silex\Rest\Provider\RestApplicationServiceProvider;

$app = new Application();
$app->register(new ServiceControllerServiceProvider());
$app->register(new RestApplicationServiceProvider());

// The service is available under $app['rest']
```

__There is also a trait available for more cleaner usage.__

### Basic usage

#### Registering resource

```php
<?php

$res = $app['rest']->resource('/users');
```

#### Creating collection actions

```php
<?php

$res->cget(function(Request $request){ … });
$res->post(function(Request $request){ … });
```

#### Creating item actions

```php
<?php

$res->get(function(Request $request, $id){ … });
$res->put(function(Request $request, $id){ … });
$res->patch(function(Request $request, $id){ … });
$res->delete(function(Request $request, $id){ … });
```

#### Adding converter

You can use converters with item actions. This can help you convert between ```id``` to the whole entity for example.

```php
<?php

$res->convert('user', function($user, Request $request){ … });
```

#### Adding constraint check to item ID

To check if the provided ID is e.g. an integer, you can add global resource assertion, just like in regular Silex route.

```php
<?php

$res->assertId('\d+');
```

#### Adding ```before``` and ```after``` middlewares

Middlewares are action-specific and you can't (sorry) define a global middleware for all actions.

```php
<?php

$res->before('cget', function(Request $request){ … });
$res->after('post', function(Request $request, Response $response){ … });
```

### Controller class usage

You can use an existing service controller or register one automatically when passing an object to ```resource()```.

#### Using existing controller service

```php
<?php

$app['rest.users.controller'] = $app->share(function($app){
    return new UsersController();
});

$app['rest']->resource('/users', 'rest.users.controller');
```

Your ```UsersController``` class should define ```cget```, ```post```, ```get```, ```put```, ```patch```, and ```delete``` methods. Later I will show you how to change method names across the application.

#### Creating controller service on fly

```php
<?php

$app['rest']->resource('/users', new UsersController());
```

### What more can you do with RestApplicationServiceProvider?

#### Subresources

You can use subroutes for pairing resources together. For example, if you have a relation between Users and Notes (one-to-many) and use user friendly URLs (```/users/2/notes/25```), you can create a subresource.

```php
<?php

$userResource = $app['rest']->resource('/users');
$noteResource = $userResource->subresource('/notes');
```

The variable to which note ID will be bound to will be ```idd```. If you create a subresource to ```/notes``` the next ID will be ```iddd``` (see the convention?). But you __can__ change it. Simply, pass the name as third parameter of subresource:

```php
<?php

$userResource = $app['rest']->resource('/users');
$noteResource = $userResource->subresource('/notes', null, 'nid');
```

#### Overriding default method names for controller classes

While registering the provider, pass variables to the container.

```php
<?php

$app->register(new RestApplicationServiceProvider(), array(
    'rest.methods.cget' => 'all',
    'rest.methods.post' => 'create',
    'rest.methods.get' => 'read',
    'rest.methods.put' => 'update'
    'rest.methods.patch' => 'merge'
));
```

You can also change a chosen method on-fly between ```resource``` and ```subresource``` calls. To change ```GET``` item method, call ```$app['rest.methods.get'] = 'read';``` which will affect further (sub)resource creations.

## License

RestApplicationServiceProvider is licensed under the MIT license.