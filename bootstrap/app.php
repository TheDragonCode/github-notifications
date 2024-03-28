<?php

use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Exceptions\Handler;
use LaravelZero\Framework\Application;
use LaravelZero\Framework\Kernel;

$app = new Application(dirname(__DIR__));

$app->singleton(ConsoleKernel::class, Kernel::class);
$app->singleton(ExceptionHandler::class, Handler::class);

return $app;
