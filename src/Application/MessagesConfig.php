<?php

declare(strict_types=1);

namespace Vonage\Application;

use function trigger_error;

class MessagesConfig
{
    public const INBOUND = 'inbound_url';
    public const STATUS = 'status_url';

    protected array $webhooks = [];

    public function setWebhook($type, $url, $method = null): self
    {
        if (!$url instanceof Webhook) {
            trigger_error(
                'Passing a string URL and method are deprecated, please pass a Webhook object instead',
                E_USER_DEPRECATED
            );

            $url = new Webhook($url, $method);
        }

        $this->webhooks[$type] = $url;

        return $this;
    }

    public function getWebhook($type)
    {
        return $this->webhooks[$type] ?? null;
    }
}
