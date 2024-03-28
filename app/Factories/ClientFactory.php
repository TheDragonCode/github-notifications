<?php

declare(strict_types=1);

namespace DragonCode\GithubNotifications\Factories;

use Github\AuthMethod;
use Github\Client;
use Github\HttpClient\Builder;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory;
use Http\Client\Common\Plugin\RetryPlugin;

class ClientFactory
{
    public static function make(string $token): Client
    {
        $builder = static::builder();
        $builder->addPlugin(static::retryPlugin());

        $client = static::client($builder);
        $client->authenticate($token, AuthMethod::ACCESS_TOKEN);

        return $client;
    }

    protected static function client(Builder $builder): Client
    {
        return new Client($builder);
    }

    protected static function builder($psr = new HttpFactory()): Builder
    {
        return new Builder(static::httpClient(), $psr, $psr);
    }

    protected static function httpClient(): GuzzleClient
    {
        return new GuzzleClient(['connect_timeout' => 10, 'timeout' => 30]);
    }

    protected static function retryPlugin(): RetryPlugin
    {
        return new RetryPlugin(['retries' => 3]);
    }
}
