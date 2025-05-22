<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Throwable; // Added for clarity, often included by default

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // This is where exceptions are typically reported/logged.
            // The default behavior is to log exceptions based on your logging config.
            // You can add custom reporting logic here if needed.
            Log::error('Uncaught Exception: '.$e->getMessage(), ['exception' => $e]);
        });
    }

    /**
     * Report or log an exception.
     *
     * @return void
     */
    public function report(Throwable $exception)
    {
        // Call the parent report method, which handles logging based on register() and config
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $exception)
    {
        // Call the parent render method, which handles rendering HTTP responses for exceptions
        return parent::render($request, $exception);
    }
}
