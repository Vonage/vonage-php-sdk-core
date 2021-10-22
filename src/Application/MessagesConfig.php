<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Application;

use function trigger_error;

class MessagesConfig
{
    public const INBOUND = 'inbound_url';
    public const STATUS = 'status_url';

    /**
     * @var array
     */
    protected $webhooks = [];

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
