<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Exceptions\XTokenExpiredException;

class MustAuthMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next)
    {
        // 检查是否传token
        if (!defined('X_TOKEN')) {
            throw new XTokenExpiredException();
        }

        $this->defineUserId($request);

        return $next($request);
    }

}

