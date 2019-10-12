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
    $router->get('media/{code}', 'FileController@showMedia'); // 访问图片

    $router->post('v1/ueditor', 'UeditorController@ueditor'); //百度上传图片
    $router->get('v1/ueditor', 'UeditorController@ueditor'); //百度上传图片
});

// 用户
$router->group(['namespace' => 'User'], function () use ($router) {
    $router->get('v1/device', 'UserController@device');      // 获得设备号
    $router->get('v1/captcha', 'UserController@captcha');    // 获得图形验证码
    $router->post('v1/authenticate', 'UserController@authenticate'); // 登录
});

// 用户 强制登录
$router->group(['namespace' => 'User', 'middleware' => 'auth.must'], function () use ($router) {
    $router->get('v1/myInfo', 'UserController@myInfo');  // 用户信息
    $router->put('v1/changePassword', 'UserController@changePassword');  // 用户修改密码
    $router->post('v1/signOut', 'UserController@signOut');  // 注销
});

// 文章管理
$router->group(['namespace' => 'Article', 'middleware' => 'auth.must'], function() use ($router) {
//    $router->get('');
    $router->post('v1/article/post', 'ArticleController');




});







