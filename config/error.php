<?php
/*
 * 错误码列表
 */

return [
    /*
     * 系统级,保持和Http状态码兼容
     */
    200 => 'OK',

    400 => 'Bad Request',            // 客户端通用错误
    500 => 'Internal Server Error',  // 服务端通用错误

    401 => 'Unauthorized',    // 未登录|token过期，并且不能续期，必须重新登录（> 1 week）
    402 => 'miss device',     // 缺失device id，请重新申请一个设备号


    /*
     * 业务层
     */
    40101 => 'JWT Exception',   // include: token has expired and can no longer be refreshed
    40102 => 'JWT: token has expired',
    40103 => 'User Forbidden',  // 用户被禁




];
