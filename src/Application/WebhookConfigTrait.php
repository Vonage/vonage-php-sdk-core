<?php
declare(strict_types=1);

namespace Nexmo\Application;

trait WebhookConfigTrait
{
    /**
     * @var array<Webhook>
     */
    protected $webhooks = [];

    public function setWebhook(string $type, Webhook $webhook)
    {
        $this->webhooks[$type] = $webhook;
        return $this;
    }

    public function getWebhook(string $type) : ?Webhook
    {
        if (isset($this->webhooks[$type])) {
            return $this->webhooks[$type];
        }

        return null;
    }
}