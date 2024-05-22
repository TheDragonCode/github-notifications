<?php

declare(strict_types=1);

namespace DragonCode\GithubNotifications\Services;

class Token
{
    public static function detect(): ?string
    {
        return static::fromServer() ?? static::fromComposer();
    }

    protected static function fromServer(): ?string
    {
        return $_SERVER['GITHUB_TOKEN'] ?? null;
    }

    protected static function fromComposer(): ?string
    {
        foreach (static::composerPath() as $path) {
            if (! $data = Process::run('cat ' . $path)) {
                continue;
            }

            if ($token = $data['github-oauth']['github.com'] ?? null) {
                return $token;
            }
        }

        return null;
    }

    protected static function composerPath(): array
    {
        return [
            '~/.composer/auth.json',
            '~/.config/.composer/auth.json',
            '~/.config/composer/auth.json',
            '~/AppData/Roaming/Composer/auth.json',
            '~/composer/auth.json',
            '%USERPROFILE%/AppData/Roaming/Composer/auth.json',
        ];
    }
}
