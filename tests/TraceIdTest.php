<?php

namespace Tests;

use Edoc\LaravelTraceId\TraceIdMiddleware;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;

class TraceIdTest extends TestCase
{
    #[Test]
    public function new_trace_id()
    {
        // Given
        $request = Request::create('test');
        $next = function () {
            return new Response('TestResponse');
        };

        // When
        $middleware = new TraceIdMiddleware();
        $response = $middleware->handle($request, $next);

        // Then
        $logContext = Log::sharedContext();
        $this->assertContains('trace_id', array_keys($logContext));
        $this->assertEquals(TraceIdMiddleware::get(), $logContext['trace_id']);

        if ($response instanceof RedirectResponse || $response instanceof Response) {
            $this->assertNotEmpty($response->headers->get(TraceIdMiddleware::headerName()));
        }
    }

    #[Test]
    public function existing_trace_id()
    {
        // Given
        $traceId = "Bla";
        $request = Request::create('test');
        $request->headers->set(TraceIdMiddleware::headerName(), $traceId);
        TraceIdMiddleware::$traceId = null;
        $next = function () {
            return new Response('TestResponse');
        };

        // When
        $middleware = new TraceIdMiddleware();
        $response = $middleware->handle($request, $next);

        // Then
        $logContext = Log::sharedContext();
        $this->assertContains('trace_id', array_keys($logContext));
        $this->assertEquals(TraceIdMiddleware::get(), $logContext['trace_id']);

        if ($response instanceof RedirectResponse || $response instanceof Response) {
            $this->assertNotEmpty($response->headers->get(TraceIdMiddleware::headerName()));
            $this->assertEquals($traceId, $response->headers->get(TraceIdMiddleware::headerName()));
        }
    }
}
