<?php

namespace Edoc\LaravelTraceId;

use Illuminate\Support\ServiceProvider;

class TraceIdServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/trace-id.php' => config_path('trace-id.php'),
        ]);
    }
}
