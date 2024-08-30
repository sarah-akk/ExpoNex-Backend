<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckExhibitionOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        if (request()->user()->role_id !== 3)
            return response([
                'status' => 'failed',
                'error' => 'Forbidden.',
            ], 403);
        return $next($request);
    }
}
