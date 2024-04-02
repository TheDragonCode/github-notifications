<?php

namespace DragonCode\GithubNotifications\Commands;

use DragonCode\GithubNotifications\Factories\ClientFactory;
use DragonCode\GithubNotifications\Services\GitHub;
use DragonCode\GithubNotifications\Services\Output;
use Github\ResultPager;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;

use function Laravel\Prompts\confirm;

class ReadCommand extends Command
{
    protected $signature = 'read'
        . ' {repository?* : Full or partial repository names}'
        . ' {--r|except-repository=* : Exclude repositories from processing}'
        . ' {--i|except-issues : Exclude issues from processing}'
        . ' {--p|except-pulls : Exclude Pull Requests from processing}'
        . ' {--m|except-mentions : Exclude notifications with your mention from processing}'
        . ' {--o|with-open : Process including open Issues and Pull Requests}'
        . ' {--token= : Specifies the token to use}';

    protected $description = 'Marks as read all notifications based on specified conditions';

    public function handle(): void
    {
        $include = $this->repositories();
        $except  = $this->exceptRepositories();

        $this->welcome($include, $except);

        if ($this->hasContinue()) {
            $this->read($include);
        }
    }

    protected function welcome(array $includeRepositories, ?array $exceptRepositories): void
    {
        if ($includeRepositories) {
            $this->bulletList('You specified the following repository name masks:', $includeRepositories);
        }

        if ($exceptRepositories) {
            $this->bulletList('You specified the following masks to exclude repositories:', $exceptRepositories);
        }

        if (! $includeRepositories && ! $exceptRepositories) {
            Output::info('Mark as read all notifications except open ones');
        }
    }

    protected function hasContinue(): bool
    {
        return confirm('Continue');
    }

    protected function read(array $repositories): void
    {
        $this->gitHub()
            ->repositories($repositories)
            ->exceptRepositories($this->exceptRepositories())
            ->exceptIssues($this->exceptIssues())
            ->exceptPulls($this->exceptPulls())
            ->exceptMentions($this->exceptMentions())
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
            && ! $this->exceptRepositories()
            && ! $this->exceptIssues()
            && ! $this->exceptPulls()
            && ! $this->exceptMentions()
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
        return $this->resolvePattern($this->argument('repository'));
    }

    protected function exceptRepositories(): array
    {
        return $this->resolvePattern($this->option('except-repository'));
    }

    protected function exceptIssues(): bool
    {
        return $this->option('except-issues');
    }

    protected function exceptPulls(): bool
    {
        return $this->option('except-pulls');
    }

    protected function exceptMentions(): bool
    {
        return $this->option('except-mentions');
    }

    protected function withOpen(): bool
    {
        return $this->option('with-open');
    }

    protected function bulletList(string $title, array $values): void
    {
        Output::info($title);

        $this->components->bulletList($values);
    }

    protected function resolvePattern(?array $values): array
    {
        return collect($values)
            ->filter()
            ->unique()
            ->map(fn (string $value) => Str::of($value)->trim()->start('*')->finish('*')->toString())
            ->sort()
            ->all();
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
