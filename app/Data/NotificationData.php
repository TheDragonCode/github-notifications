<?php

declare(strict_types=1);

namespace DragonCode\GithubNotifications\Data;

class NotificationData extends Data
{
    public int $id;

    public int $issueId;

    public string $type;

    public string $fullName;

    public string $organization;

    public string $repository;

    public function __construct(array $data)
    {
        $this->id = (int)$this->get($data, 'id');
        $this->issueId = $this->issueId($data);

        $this->type = $this->get($data, 'subject.type');

        $this->fullName = $this->get($data, 'repository.full_name');
        $this->organization = $this->get($data, 'repository.owner.login');
        $this->repository = $this->get($data, 'repository.name');
    }

    protected function issueId(array $data): int
    {
        $values = $this->parseUrl($data);

        return (int)end($values);
    }

    protected function parseUrl(array $data): array
    {
        return explode('/', $this->get($data, 'subject.url'));
    }
}
