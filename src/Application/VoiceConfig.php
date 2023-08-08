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
    protected ?bool $signedCallbacks = null;
    protected ?int $conversationsTtl = null;
    protected ?string $region = null;

    protected const ALLOWED_REGIONS = [
        'na-east',
        'na-west',
        'eu-west',
        'eu-east',
        'apac-sng',
        'apac-australia'
    ];

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

    public function getSignedCallbacks(): ?bool
    {
        return $this->signedCallbacks;
    }

    public function setSignedCallbacks(?bool $signedCallbacks): static
    {
        $this->signedCallbacks = $signedCallbacks;

        return $this;
    }

    public function getConversationsTtl(): ?int
    {
        return $this->conversationsTtl;
    }

    public function setConversationsTtl(?int $conversationsTtl): static
    {
        $this->conversationsTtl = $conversationsTtl;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): static
    {
        if (!in_array($region, self::ALLOWED_REGIONS, true)) {
            throw new \InvalidArgumentException('Unrecognised Region: ' . $region);
        }

        $this->region = $region;

        return $this;
    }
}
