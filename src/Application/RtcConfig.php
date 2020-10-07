<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Application;

class RtcConfig
{
    public const EVENT = 'event_url';

    protected $webhooks = [];

    /**
     * @param $type
     * @param $url
     * @param null $method
     * @return $this
     */
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

    /**
     * @param $type
     * @return mixed
     */
    public function getWebhook($type)
    {
        return $this->webhooks[$type] ?? null;
    }
}
