<?php

use App\Enums\AuditEventType;
use App\Enums\AuditResult;
use App\Services\AuditLogger;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($e->getStatusCode() === 403 && Auth::check()) {
                try {
                    app(AuditLogger::class)->log(
                        AuditEventType::AccessDenied,
                        AuditResult::Denied,
                        actorId: (int) Auth::id(),
                        targetType: 'Route',
                        after: [
                            'path' => '/'.ltrim($request->path(), '/'),
                            'method' => $request->method(),
                        ],
                        reason: 'http_403',
                    );
                } catch (\Throwable $t) {
                    report($t);
                }
            }

            return null;
        });
    })->create();