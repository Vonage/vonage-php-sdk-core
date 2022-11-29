<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Application;

use function trigger_error;

class VoiceConfig
{
    public const EVENT = 'event_url';
    public const ANSWER = 'answer_url';

    /**
     * @var array
     */
    protected $webhooks = [];

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
