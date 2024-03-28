<?php

declare(strict_types=1);

namespace DragonCode\GithubNotifications\Data;

class ItemData extends Data
{
    public int $id;

    public bool $isOpen;

    public bool $isMerged;

    public function __construct(array $data)
    {
        $this->id = (int) $this->get($data, 'number');

        $this->isOpen   = $this->get($data, 'state') === 'open';
        $this->isMerged = (bool) $this->get($data, 'merged');
    }
}
