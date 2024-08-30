<?php

use Illuminate\Foundation\Application;
use App\Http\Middleware\CheckCompanyOwner;
use App\Http\Middleware\RequestFromServer;
use App\Http\Middleware\CheckExhibitionOwner;
use App\Http\Middleware\CheckAdminAvailability;
use Illuminate\Foundation\Configuration\Exceptions;

use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix('api/v1/log')
                ->group(base_path('routes/api/v1/log.php'));
                
            Route::middleware('api')
                ->prefix('api/v1/user')
                ->group(base_path('routes/api/v1/user.php'));

            Route::middleware('api')
                ->prefix('api/v1/auth')
                ->group(base_path('routes/api/v1/auth.php'));

            Route::middleware('api')
                ->prefix('api/v1/company')
                ->group(base_path('routes/api/v1/company.php'));

            Route::middleware('api')
                ->prefix('api/v1/document')
                ->group(base_path('routes/api/v1/document.php'));

            Route::middleware('api')
                ->prefix('api/v1/exhibition')
                ->group(base_path('routes/api/v1/exhibition.php'));

            Route::middleware('api')
                ->prefix('api/v1/investor')
                ->group(base_path('routes/api/v1/investor.php'));

            Route::middleware('api')
                ->prefix('api/v1/admin')
                ->group(base_path('routes/api/v1/admin.php'));

            Route::middleware('api')
                ->prefix('api/v1/category')
                ->group(base_path('routes/api/v1/category.php'));

            Route::middleware('api')
                ->prefix('api/v1/product')
                ->group(base_path('routes/api/v1/product.php'));

            Route::middleware('api')
                ->prefix('api/v1/owner')
                ->group(base_path('routes/api/v1/owner.php'));

            Route::middleware('api')
                ->prefix('api/v1/transactions')
                ->group(base_path('routes/api/v1/transactions.php'));

            // Route::middleware('api')
            //     ->prefix('api/v1/company/post')
            //     ->group(base_path('routes/api/v1/post.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware
            ->alias([
                'server-request' => RequestFromServer::class,
                'check_permissions' => CheckAdminAvailability::class,
                'check_company_onwer' => CheckCompanyOwner::class,
                'check_exhibition_onwer' => CheckExhibitionOwner::class,
            ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
