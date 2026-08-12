<?php

declare(strict_types=1);

namespace Vonage\Messages\Channel\RCS\Suggestions;

trait FallbackUrlTrait
{
    protected $fallbackurl;

    public function setFallbackUrl(string $fallbackurl): void
    {
        $this->fallbackurl = $fallbackurl;
    }

    public function getFallbackUrl(): string
    {
        return $this->fallbackurl;
    }
}
