<?php

declare(strict_types=1);

namespace DragonCode\GithubNotifications\Services;

use Illuminate\Support\Facades\Process as BaseProcess;

class Process
{
    public static function run(string $command): ?array
    {
        $result = BaseProcess::run($command);

        if ($result->failed() || ! static::validateJson($result->output())) {
            return null;
        }

        return json_decode($result->output(), true);
    }

    protected static function validateJson(string $json): bool
    {
        json_decode($json);

        return json_last_error() === JSON_ERROR_NONE;
    }
}
