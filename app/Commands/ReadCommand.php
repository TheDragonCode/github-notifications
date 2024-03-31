<?php

namespace DragonCode\GithubNotifications\Commands;

use DragonCode\GithubNotifications\Factories\ClientFactory;
use DragonCode\GithubNotifications\Services\GitHub;
use DragonCode\GithubNotifications\Services\Output;
use Github\ResultPager;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;

use function Laravel\Prompts\confirm;

class ReadCommand extends Command
{
    protected $signature = 'read'
        . ' {repository?* : Full or partial repository names}'
        . ' {--i|except-issues : Exclude issues from processing}'
        . ' {--p|except-pulls : Exclude Pull Requests from processing}'
        . ' {--o|with-open : Process including open Issues and Pull Requests}'
        . ' {--token= : Specifies the token to use}';

    protected $description = 'Marks as read all notifications based on specified conditions';

    public function handle(): void
    {
        $repositories = $this->repositories();

        $this->welcome($repositories);

        if ($this->hasContinue()) {
            $this->read($repositories);
        }
    }

    protected function welcome(array $repositories): void
    {
        if ($repositories) {
            Output::info('You specified the following repository name masks:');

            $this->components->bulletList($repositories);

            return;
        }

        Output::info('Mark as read all notifications except open ones');
    }

    protected function hasContinue(): bool
    {
        return confirm('Continue');
    }

    protected function read(array $repositories): void
    {
        $this->gitHub()
            ->repositories($repositories)
            ->exceptIssues($this->exceptIssues())
            ->exceptPulls($this->exceptPulls())
            ->withOpen($this->withOpen())
            ->when(
                $this->shouldBeAll($repositories),
                fn (GitHub $gitHub) => $gitHub->markAll(),
                fn (GitHub $gitHub) => $gitHub->mark()
            );
    }

    protected function shouldBeAll(array $repositories): bool
    {
        return empty($repositories)
            && ! $this->exceptIssues()
            && ! $this->exceptPulls()
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

    protected function repositories(): array
    {
        return collect($this->argument('repository'))
            ->filter()
            ->unique()
            ->sort()
            ->all();
    }

    protected function exceptIssues(): bool
    {
        return $this->option('except-issues');
    }

    protected function exceptPulls(): bool
    {
        return $this->option('except-pulls');
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
