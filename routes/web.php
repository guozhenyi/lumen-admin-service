<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/', function () use ($router) {
    return response()->json(['code'=> 200]);
});

// 文件资源
$router->group(['namespace' => 'File'], function () use ($router) {
    $router->post('v1/uploads', 'FileController@upload'); //上传图片
});


