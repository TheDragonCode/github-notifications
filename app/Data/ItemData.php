<?php

declare(strict_types=1);

namespace DragonCode\GithubNotifications\Data;

class ItemData extends Data
{
    public ?int $ownerId = null;

    public bool $isOpen = false;

    public bool $isMerged = true;

    public function __construct(?array $data)
    {
        if (is_null($data)) {
            return;
        }

        $this->ownerId = $this->get($data, 'user.id');

        $this->isOpen   = $this->get($data, 'state') === 'open';
        $this->isMerged = (bool) $this->get($data, 'merged');
    }
}
