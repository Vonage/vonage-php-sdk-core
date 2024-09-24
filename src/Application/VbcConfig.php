<?php

declare(strict_types=1);

namespace Vonage\Application;

class VbcConfig
{
    protected bool $enabled = false;

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
