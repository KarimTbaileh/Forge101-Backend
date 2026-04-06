<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // تسجيل Middleware جديد باستخدام array
        $middleware->alias([
            'auth.supabase' => \App\Http\Middleware\SupabaseAuth::class,
        ]);

        // لو بدك يشتغل على كل Route Globally:
        // $middleware->prepend(\App\Http\Middleware\SupabaseAuth::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
