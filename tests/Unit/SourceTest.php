<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Process;

it('checks application launch', function () {
    $result = Process::run('php ' . realpath(__DIR__ . '/../../notifications') . ' --version');

    expect($result->successful())->toBeTrue();
    expect($result->output())->toContain(config('app.name'));
});
