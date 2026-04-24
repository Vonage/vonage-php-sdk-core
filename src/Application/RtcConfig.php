<?php

declare(strict_types=1);

namespace Vonage\Application;

use function trigger_error;

class RtcConfig
{
    public const EVENT = 'event_url';

    protected array $webhooks = [];

    public function setWebhook($type, Webhook $webhook): self
    {
        $this->webhooks[$type] = $webhook;

        return $this;
    }

    public function getWebhook($type)
    {
        return $this->webhooks[$type] ?? null;
    }
}
