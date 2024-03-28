<?php

declare(strict_types=1);

namespace DragonCode\GithubNotifications\Data;

use Illuminate\Support\Arr;

abstract class Data
{
    protected function get(array $data, string $key): mixed
    {
        return Arr::get($data, $key);
    }
}
