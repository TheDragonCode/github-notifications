<?php

declare(strict_types=1);

namespace DragonCode\GithubNotifications\Data;

use Illuminate\Support\Str;

use function Termwind\terminal;

class NotificationData extends Data
{
    public int $id;

    public int $issueId;

    public string $type;

    public string $reason;

    public string $fullName;

    public string $organization;

    public string $repository;

    public string $title;

    public function __construct(array $data, int $index)
    {
        $this->id      = (int) $this->get($data, 'id');
        $this->issueId = $this->issueId($data);

        $this->type   = $this->get($data, 'subject.type');
        $this->reason = $this->get($data, 'reason');

        $this->fullName     = $this->get($data, 'repository.full_name');
        $this->organization = $this->get($data, 'repository.owner.login');
        $this->repository   = $this->get($data, 'repository.name');

        $this->title = $this->title($index);
        $this->title .= $this->caption($data);
    }

    protected function title(int $index): string
    {
        return sprintf(
            '<fg=gray>%d.</> %s: <fg=gray>%s #%d</>',
            $index,
            $this->fullName,
            $this->type,
            $this->issueId
        );
    }

    public function caption(array $data): string
    {
        $title = Str::length(strip_tags($this->title));

        return Str::of(strip_tags((string) $this->get($data, 'subject.title')))
            ->trim()
            ->squish()
            ->limit($this->terminalWidth() - $title - 50)
            ->prepend('<fg=yellow>(')
            ->append(')</>')
            ->prepend(' ')
            ->toString();
    }

    protected function issueId(array $data): int
    {
        if ($values = $this->parseUrl($data)) {
            return (int) end($values);
        }

        return (int) $this->get($data, 'id');
    }

    protected function parseUrl(array $data): ?array
    {
        if ($url = $this->get($data, 'subject.url')) {
            return explode('/', $url);
        }

        return null;
    }

    protected function terminalWidth(): int
    {
        return terminal()->width();
    }
}
