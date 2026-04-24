<?php

declare(strict_types=1);

namespace Vonage\Application;

class MessagesConfig
{
    public const INBOUND = 'inbound_url';
    public const STATUS = 'status_url';

    protected array $webhooks = [];

    public function setWebhook($type, Webhook $url): self
    {
        $this->webhooks[$type] = $url;

        return $this;
    }

    public function getWebhook($type)
    {
        return $this->webhooks[$type] ?? null;
    }
}
