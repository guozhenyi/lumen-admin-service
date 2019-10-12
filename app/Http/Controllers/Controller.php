<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var object
     */
    protected $repository;

    /**
     * 操作者信息
     *
     * @var object
     */
    protected $operator;

    /**
     * @var array
     */
    protected $data = [
        'msg' => ''
    ];


    /**
     * 统一JSON响应方法，特殊响应除外
     *
     * @param mixed $data
     * @param int $code
     * @param string $message
     * @param int $status
     * @param array $headers
     * @return \Illuminate\Http\JsonResponse
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2018-06-09
     */
    protected function respond($data, $code = 200, $message = '', $status = 200, array $headers = [])
    {
        if (isset($data['error_code'])) {
            $res = [
                'code' => $data['error_code'],
                'data' => isset($data['error_data']) ? $data['error_data'] : $this->data,
                'message' => isset($data['error_message']) ? $data['error_message'] : '',
            ];

            return response()->json($res, $status, $headers, JSON_UNESCAPED_UNICODE);
        }

        if (empty($data)) {
            $data = $this->data;
        }

        return response()->json(
            [
                'code' => $code,
                'data' => $data,
                'message' => $message
            ],
            $status, $headers, JSON_UNESCAPED_UNICODE
        );
    }


}
