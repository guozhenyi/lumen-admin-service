<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ShouldAuthMiddleware extends BaseMiddleware
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
        if (!defined('X_TOKEN')) {
            return $next($request);
        }

        $this->defineUserId($request);

        return $next($request);
    }

}

