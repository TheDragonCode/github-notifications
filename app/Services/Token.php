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
            $resolved = static::resolvePath($path);

            if (! $resolved || ! is_file($resolved) || ! is_readable($resolved)) {
                continue;
            }

            $contents = @file_get_contents($resolved);
            if ($contents === false) {
                continue;
            }

            $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

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

    protected static function resolvePath(string $path): ?string
    {
        if (str_contains($path, '%USERPROFILE%')) {
            $userProfile = getenv('USERPROFILE') ?: ($_SERVER['USERPROFILE'] ?? null);

            if ($userProfile) {
                $path = str_replace('%USERPROFILE%', rtrim($userProfile, '\\/'), $path);
            }
        }

        if (str_starts_with($path, '~')) {
            $home = getenv('HOME') ?: ($_SERVER['HOME'] ?? null);

            if ($home) {
                $path = rtrim($home, '\\/') . substr($path, 1);
            }
        }

        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }
}
