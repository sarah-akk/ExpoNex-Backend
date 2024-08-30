<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminAvailability
{
    public function handle(Request $request, Closure $next): Response
    {
        // dd(request()->user()->permissions);
        $routeName = Route::currentRouteName();
        if (request()->user()->permissions && in_array($routeName, request()->user()->permissions))
            return $next($request);
        else
            return response([
                'status' => 'failed',
                'errors' => 'Not Allowed'
            ], 403);
    }
}
