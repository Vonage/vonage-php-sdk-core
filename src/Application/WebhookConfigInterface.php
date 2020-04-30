<?php
declare(strict_types=1);

namespace Nexmo\Application;

interface WebhookConfigInterface
{
    /**
     * @return self
     */
    public function setWebhook(string $type, Webhook $webhook);

    public function getWebhook(string $type) : ?Webhook;
}
