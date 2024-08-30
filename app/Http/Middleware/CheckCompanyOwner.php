<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCompanyOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        if (request()->user()->role_id !== 2 || !request()->user()->company->is_approval)
            return response([
                'status' => 'failed',
                'error' => 'Forbidden.',
            ], 403);
        return $next($request);
    }
}
