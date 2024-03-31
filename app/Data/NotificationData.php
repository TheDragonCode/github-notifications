<?php

declare(strict_types=1);

namespace DragonCode\GithubNotifications\Data;

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

        $this->title = sprintf(
            '<fg=gray>%d.</> %s: <fg=gray>%s #%d</>',
            $index,
            $this->fullName,
            $this->type,
            $this->issueId
        );
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
}
