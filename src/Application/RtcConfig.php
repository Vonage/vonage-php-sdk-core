<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2019 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Application;

class RtcConfig
{
    const EVENT  = 'event_url';

    protected $webhooks = [];

    public function setWebhook($type, $url, $method = null)
    {
        if (!($url instanceof Webhook)) {
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
        if (isset($this->webhooks[$type])) {
            return $this->webhooks[$type];
        }
    }
}
