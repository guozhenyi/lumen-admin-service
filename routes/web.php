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

// 文件模块
$router->group(['namespace' => 'File'], function () use ($router) {
    $router->post('v1/uploads', 'FileController@upload'); //上传图片
});

// 登录模块
$router->group(['namespace' => 'Auth'], function () use ($router) {
    $router->get('v1/device', 'AuthController@device');      // 分配设备号
    $router->get('v1/captcha', 'AuthController@captcha');    // 获得图形验证码
    $router->post('v1/auth', 'AuthController@auth');         // 登录
});

// 登录模块 强制登录
$router->group(['namespace' => 'Auth', 'middleware' => 'auth.must'], function () use ($router) {
    $router->get('v1/myInfo', 'AuthController@myInfo');  // 用户信息
    $router->put('v1/changePassword', 'AuthController@changePassword');  // 用户修改密码
    $router->post('v1/signOut', 'AuthController@signOut');  // 注销
});

// 系统设置
$router->group(['namespace' => 'SysSet', 'middleware' => 'auth.must'], function () use ($router) {
    $router->get('v1/menu/index', 'MenuController@index');    // 菜单列表
    $router->post('v1/menu/post', 'MenuController@store');     // 菜单新增
    $router->put('v1/menu/update', 'MenuController@update');   // 菜单修改
    $router->delete('v1/menu/delete', 'MenuController@destroy'); // 菜单删除
    $router->get('v1/menu/parent/index', 'MenuController@parent');   // 获得父级菜单列表
    $router->get('v1/menu/sibling/index', 'MenuController@sibling');   // 获得同级菜单列表

});



