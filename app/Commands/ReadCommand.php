<?php

namespace DragonCode\GithubNotifications\Commands;

use DragonCode\GithubNotifications\Factories\ClientFactory;
use DragonCode\GithubNotifications\Services\GitHub;
use Github\ResultPager;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;

use function Laravel\Prompts\confirm;

class ReadCommand extends Command
{
    protected $signature = 'read'
        . ' {repository? : Full or partial repository name}'
        . ' {--i|without-issues : Exclude issues from processing}'
        . ' {--s|without-pulls : Exclude Pull Requests from processing}'
        . ' {--o|with-open : Process including open Issues and Pull Requests}'
        . ' {--token= : Specifies the token to use}';

    protected $description = 'Marks as read all notifications based on specified conditions';

    public function handle(): void
    {
        $repository = $this->repository();

        $this->welcome($repository);

        if ($this->hasContinue()) {
            $this->read($repository);
        }
    }

    protected function welcome(?string $repository): void
    {
        $this->show($repository ?: 'All Notifications');
    }

    protected function hasContinue(): bool
    {
        return confirm('Continue');
    }

    protected function read(?string $repository): void
    {
        $this->gitHub()
            ->repository($repository)
            ->withoutIssues($this->withoutIssues())
            ->withoutPulls($this->withoutPulls())
            ->withOpen($this->withOpen())
            ->when(
                $this->shouldBeAll($repository),
                fn (GitHub $gitHub) => $gitHub->markAll(),
                fn (GitHub $gitHub) => $gitHub->mark()
            );
    }

    protected function shouldBeAll(?string $repository): bool
    {
        return empty($repository)
            && ! $this->withoutIssues()
            && ! $this->withoutPulls()
            && $this->withOpen();
    }

    protected function gitHub(): GitHub
    {
        $client = ClientFactory::make($this->token());

        return app(GitHub::class, [
            'output'    => $this->components,
            'github'    => $client,
            'paginator' => new ResultPager($client),
        ]);
    }

    public function show(string $value): void
    {
        $this->components->info($value);
    }

    protected function repository(): ?string
    {
        return $this->argument('repository');
    }

    protected function withoutIssues(): bool
    {
        return $this->option('without-issues');
    }

    protected function withoutPulls(): bool
    {
        return $this->option('without-pulls');
    }

    protected function withOpen(): bool
    {
        return $this->option('with-open');
    }

    protected function token(): string
    {
        if ($token = $this->detectToken()) {
            return $token;
        }

        throw new InvalidOptionException('Unable to resolve the token to use.');
    }

    protected function detectToken(): ?string
    {
        return $this->option('token') ?: ($_SERVER['GITHUB_TOKEN'] ?? null);
    }
}
