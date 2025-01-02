<?php

declare(strict_types=1);

namespace DragonCode\GithubNotifications\Services;

use Illuminate\Support\Str;

use function Termwind\render;

class Output
{
    public static function info(string $value, int|string $title = 'INFO'): void
    {
        static::line($value, $title, 'blue-300');
    }

    public static function success(string $value, int|string $title = 'SUCCESS'): void
    {
        static::line($value, $title, 'green-300');
    }

    public static function comment(string $value, int|string $title = 'COMMENT'): void
    {
        static::line($value, $title, 'gray-300');
    }

    protected static function line(string $value, int|string $title, string $color): void
    {
        $padding = static::padding($title);

        render(
            <<<HTML
                    <div class="py-1 ml-1">
                        <div class="px-$padding bg-$color text-black">
                            $title
                        </div>
                        <em class="ml-1">
                          $value
                        </em>
                    </div>
                HTML
        );
    }

    protected static function padding(int|string $value): int
    {
        return match (Str::length((string) $value)) {
            1, 2 => 3,
            3, 4 => 2,
            default => 1
        };
    }
}
