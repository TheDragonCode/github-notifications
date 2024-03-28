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
use Illuminate\Support\Str;

class GitHub
{
    protected ?string $repository = null;
    protected bool $withoutIssues = false;
    protected bool $withoutPulls = false;
    protected bool $withOpen = false;

    public function __construct(
        protected Factory $output,
        protected Client $github,
        protected ResultPager $paginator,
    ) {
    }

    public function repository(?string $repository): self
    {
        $this->repository = $repository;

        return $this;
    }

    public function withoutIssues(bool $withoutIssues): self
    {
        $this->withoutIssues = $withoutIssues;

        return $this;
    }

    public function withoutPulls(bool $withoutPulls): self
    {
        $this->withoutPulls = $withoutPulls;

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
        $this->notifications()->markRead();
    }

    public function mark(): void
    {
        if (!$items = $this->paginated()) {
            $this->output->info('Nothing to mark');

            return;
        }

        foreach ($items as $data) {
            $notification = new NotificationData($data);

            $data = $notification->type === 'Issue'
                ? $this->issue($notification)
                : $this->pullRequest($notification);

            $item = new ItemData($data);

            $this->shouldSkip($notification, $item)
                ? $this->output->twoColumnDetail($notification->fullName, 'SKIP')
                : $this->output->task($notification->fullName, fn () => $this->markAsRead($notification));
        }
    }

    protected function markAsRead(NotificationData $data): bool
    {
        $this->github->notification()->markThreadRead($data->id);
    }

    protected function paginated(): array
    {
        return $this->paginator->fetchAll($this->notifications(), 'all');
    }

    protected function notifications(): Notification
    {
        return $this->github->notifications();
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
        if ($this->repository && Str::startsWith($notification->fullName, $this->repository)) {
            return true;
        }

        if ($this->withoutIssues && $notification->type === 'Issue') {
            return true;
        }

        if ($this->withoutPulls && $notification->type === 'PullRequest') {
            return true;
        }

        if ($this->withOpen && $item->isOpen) {
            return true;
        }

        if ($item->isOpen || !$item->isMerged) {
            return true;
        }

        return false;
    }
}
