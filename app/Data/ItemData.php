<?php

declare(strict_types=1);

namespace DragonCode\GithubNotifications\Data;

class ItemData extends Data
{
    public bool $isOpen;

    public bool $isMerged;

    public function __construct(?array $data)
    {
        if (is_null($data)) {
            $this->isOpen   = false;
            $this->isMerged = true;

            return;
        }

        $this->isOpen   = $this->get($data, 'state') === 'open';
        $this->isMerged = (bool) $this->get($data, 'merged');
    }
}
