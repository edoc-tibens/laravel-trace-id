<?php

namespace Edoc\LaravelTraceId;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as IlluminateResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TraceIdMiddleware
{
    public static function headerName(): string
    {
        return Config::get('laravel-trace-id.header_name', 'x-edoc-trace-id');
    }

    public static ?string $traceId;

    public static function get(Request $request = null): string
    {
        if (!isset(self::$traceId)) {
            self::$traceId = $request->header(TraceIdMiddleware::headerName()) ?? Str::uuid()->toString();
        }
        return self::$traceId;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $traceId = TraceIdMiddleware::get($request);

        Log::shareContext(
            [
                'trace_id' => $traceId
            ]
        );

        /** @var Response $response */
        $response = $next($request);

        if (($response instanceof IlluminateResponse) || ($response instanceof RedirectResponse)) {
            $response->header(TraceIdMiddleware::headerName(), $traceId);
        }

        return $response;
    }
}
