<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Str;
use Fruitcake\Cors\CorsService;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\Response
     *
     * @author guozhenyi
     * @date 2019-05-10
     */
    public function render($request, Exception $e)
    {
//        return parent::render($request, $e);

        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        // 本地应用环境，打印Exception Stack信息
        if (app()->environment('local')) {
            return parent::render($request, $e);
        }

        $code = $this->getCode($e);
        $message = $this->getMessage($e);

        if ($e instanceof \PDOException && !env('APP_DEBUG', false)) {
            $code = 500;
            $message = 'Db Service Exception';
        }

        if ($e instanceof HttpException) {
            $response = response()->json([
                'code' => $code,
                'data' => ['msg' => ''],
                'message' => $message
            ], $code, [], JSON_UNESCAPED_UNICODE);
        } elseif ($e instanceof ValidationException) {
            $response = response()->json([
                'code' => $code,
                'data' => [
                    'msg' => $e->validator->getMessageBag()
                ],
                'message' => $message
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            $response = response()->json([
                'code' => $code,
                'data' => ['msg' => ''],
                'message' => $message
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        $cors = new CorsService(config('cors'));

        if ($cors->isCorsRequest($request)) {
            return $cors->addActualRequestHeaders($response, $request);
        }

        return $response;
    }


    /**
     * 获得Exception错误码
     *
     * @param \Exception $e
     * @return int|mixed
     *
     * @author guozhenyi
     * @date 2019-03-27
     */
    public function getCode(Exception $e)
    {
        if ($e instanceof HttpException) {
            return (int)$e->getStatusCode();
        }

        if ($e->getCode() != 0 && $e->getCode() != 200) {
            return (int)$e->getCode();
        }

        return 500;
    }


    /**
     * 获得Exception错误信息
     *
     * @param \Exception $e
     * @return string
     *
     * @author guozhenyi
     * @date 2019-03-27
     */
    public function getMessage(Exception $e)
    {
        if (empty($e->getMessage())) {
            $className = preg_replace('/(?:Http)Exception/', '', get_class($e));

            return Str::snake(substr($className, strrpos($className, '\\') + 1));
        }

        return $e->getMessage();
    }



}
