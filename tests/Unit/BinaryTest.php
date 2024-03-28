<?php

use Illuminate\Support\Facades\Process;

it('checks application launch', function () {
    $path = realpath(__DIR__ . '/../../builds/notifications');

    expect($path)->not->toBeEmpty('The binary file doesn\'t exist!');

    $result = Process::run('php ' . $path . ' --version');

    expect($result->successful())->toBeTrue();
    expect($result->output())->toContain(config('app.name'));
});
