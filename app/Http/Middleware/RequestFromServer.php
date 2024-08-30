<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestFromServer
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->client_id != env('PASSPORT_PASSWORD_GRANT_TYPE_CLIENT_ID') ||
            $request->client_secret != env('PASSPORT_PASSWORD_GRANT_TYPE_CLIENT_SECRET')
        )
            abort(404);

        return $next($request);
    }
}
