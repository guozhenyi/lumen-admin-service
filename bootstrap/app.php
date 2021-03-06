<?php

require_once __DIR__.'/../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(__DIR__.'/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    realpath(__DIR__.'/../')
);

// $app->withFacades();

// $app->withEloquent();


/*
 * 载入配置文件
 * @author guozhenyi
 * @date 2019-03-27
 */
$app->configure('cors');
$app->configure('database');


/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

/*
 * 全局中间件
 * @author guozhenyi
 * @date 2019-03-27
 */
$app->middleware([
    \Barryvdh\Cors\HandleCors::class,
    \App\Http\Middleware\BeforeMiddleware::class,
]);

 $app->routeMiddleware([
//     'auth' => App\Http\Middleware\Authenticate::class,
     'auth.must' => App\Http\Middleware\MustAuthMiddleware::class,
 ]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

// $app->register(App\Providers\AppServiceProvider::class);
// $app->register(App\Providers\AuthServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);

/*
 * 自定义服务
 * @author guozhenyi
 * @date 2019-03-27
 */
$app->register(Barryvdh\Cors\ServiceProvider::class);
$app->register(Illuminate\Redis\RedisServiceProvider::class);


/*
 * 自定义配置日志按天记录
 * @author guozhenyi
 * @date 2019-03-27
 */
$app->configureMonologUsing(function(Monolog\Logger $monoLog) use ($app){
    return $monoLog->pushHandler(
        (new Monolog\Handler\RotatingFileHandler($app->storagePath().'/logs/lumen.log'))
            ->setFormatter(new Monolog\Formatter\LineFormatter(null, null, true, true))
    );
});


/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
});

return $app;
