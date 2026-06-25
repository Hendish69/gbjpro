<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register custom middleware
        $middleware->alias([
            'permission' => App\Http\Middleware\CheckPermission::class,
            'auto.logout' => App\Http\Middleware\AutoLogoutMiddleware::class, // Tambahkan ini
        ]);
         // Tambahkan auto logout ke web middleware group
        $middleware->web(append: [
            \App\Http\Middleware\AutoLogoutMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
