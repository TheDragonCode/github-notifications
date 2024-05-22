<?php

declare(strict_types=1);

namespace DragonCode\GithubNotifications\Services;

use Closure;
use DragonCode\GithubNotifications\Data\ItemData;
use DragonCode\GithubNotifications\Data\NotificationData;
use Github\Api\Notification;
use Github\Client;
use Github\ResultPager;
use Illuminate\Console\View\Components\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class GitHub
{
    protected array $repositories = [];

    protected array $exceptRepositories = [];

    protected bool $exceptIssues = false;

    protected bool $exceptPulls = false;

    protected bool $exceptMentions = false;

    protected bool $withOpen = false;

    protected int $index = 0;

    protected int $marked = 0;

    public function __construct(
        protected Factory $output,
        protected Client $github,
        protected ResultPager $paginator,
    ) {}

    public function currentUser(): ?string
    {
        return Arr::get($this->github->me()->show(), 'name');
    }

    public function repositories(array $repositories): self
    {
        $this->repositories = $repositories;

        return $this;
    }

    public function exceptRepositories(array $except): self
    {
        $this->exceptRepositories = $except;

        return $this;
    }

    public function exceptIssues(bool $except): self
    {
        $this->exceptIssues = $except;

        return $this;
    }

    public function exceptPulls(bool $except): self
    {
        $this->exceptPulls = $except;

        return $this;
    }

    public function exceptMentions(bool $except): self
    {
        $this->exceptMentions = $except;

        return $this;
    }

    public function withOpen(bool $withOpen): self
    {
        $this->withOpen = $withOpen;

        return $this;
    }

    public function when(bool $value, Closure $callback, Closure $default): void
    {
        $value ? $callback($this) : $default($this);
    }

    public function markAll(): void
    {
        $this->output->task('Mark all notifications', fn () => $this->notifications()->markRead());

        Output::success('All notifications have been successfully marked.');
    }

    public function mark(): void
    {
        if (! $items = $this->paginated()) {
            Output::success('No unread notifications');

            return;
        }

        $count = count($items);

        $this->detected($count);
        $this->process($items);
        $this->result($count);
    }

    protected function markAsRead(NotificationData $data): void
    {
        $this->github->notification()->markThreadRead($data->id);

        ++$this->marked;
    }

    protected function process(array $items): void
    {
        foreach ($items as $data) {
            $notification = new NotificationData($data, ++$this->index);

            $item = new ItemData($this->requestByType($notification));

            $this->shouldSkip($notification, $item)
                ? $this->output->twoColumnDetail($notification->title, 'SKIP')
                : $this->output->task($notification->title, fn () => $this->markAsRead($notification));
        }
    }

    protected function paginated(): array
    {
        return $this->paginator->fetchAll($this->notifications(), 'all');
    }

    protected function notifications(): Notification
    {
        return $this->github->notifications();
    }

    protected function requestByType(NotificationData $notification): ?array
    {
        return match ($notification->type) {
            'Issue'       => $this->issue($notification),
            'PullRequest' => $this->pullRequest($notification),
            default       => null
        };
    }

    protected function issue(NotificationData $notification): array
    {
        return $this->github->issues()->show(
            $notification->organization,
            $notification->repository,
            $notification->issueId
        );
    }

    protected function pullRequest(NotificationData $notification): array
    {
        return $this->github->pullRequest()->show(
            $notification->organization,
            $notification->repository,
            $notification->issueId
        );
    }

    protected function shouldSkip(NotificationData $notification, ItemData $item): bool
    {
        if ($this->repositories && ! Str::is($this->repositories, $notification->fullName)) {
            return true;
        }

        if ($this->exceptRepositories && Str::is($this->exceptRepositories, $notification->fullName)) {
            return true;
        }

        if ($this->exceptIssues && $notification->type === 'Issue') {
            return true;
        }

        if ($this->exceptPulls && $notification->type === 'PullRequest') {
            return true;
        }

        if ($this->exceptMentions && $notification->reason === 'mention' && $this->isNotDependabot($item)) {
            return true;
        }

        if ($this->withOpen && $item->isOpen) {
            return false;
        }

        if ($notification->type === 'PullRequest' && $item->isMerged) {
            return false;
        }

        return $item->isOpen;
    }

    protected function result(int $count): void
    {
        $pluralized = $this->marked === 1 ? 'notification' : 'notifications';

        $info = sprintf(
            '%d %s were marked as read and %d were skipped.',
            $this->marked,
            $pluralized,
            $count - $this->marked
        );

        $this->marked ? Output::success($info) : Output::info($info);
    }

    protected function detected(int $count): void
    {
        $pluralized = $this->marked === 1 ? 'notification' : 'notifications';

        Output::info("unread $pluralized detected", $count);
    }

    protected function isNotDependabot(ItemData $item): bool
    {
        return $item->ownerId !== config('bots.dependabot');
    }
}
