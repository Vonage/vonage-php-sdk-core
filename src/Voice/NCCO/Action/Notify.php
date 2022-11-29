<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Voice\NCCO\Action;

use InvalidArgumentException;
use Vonage\Voice\Webhook;

use function array_key_exists;

class Notify implements ActionInterface
{
    public function __construct(protected array $payload, protected ?\Vonage\Voice\Webhook $eventWebhook)
    {
    }

    /**
     * @param array<array, mixed> $data
     */
    public static function factory(array $payload, array $data): Notify
    {
        if (array_key_exists('eventUrl', $data)) {
            if (array_key_exists('eventMethod', $data)) {
                $webhook = new Webhook($data['eventUrl'], $data['eventMethod']);
            } else {
                $webhook = new Webhook($data['eventUrl']);
            }
        } else {
            throw new InvalidArgumentException('Must supply at least an eventUrl for Notify NCCO');
        }

        return new Notify($payload, $webhook);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toNCCOArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function toNCCOArray(): array
    {
        $eventWebhook = $this->getEventWebhook();

        return [
            'action' => 'notify',
            'payload' => $this->getPayload(),
            'eventUrl' => [null !== $eventWebhook ? $eventWebhook->getUrl() : null],
            'eventMethod' => null !== $eventWebhook ? $eventWebhook->getMethod() : null,
        ];
    }

    public function getEventWebhook(): ?Webhook
    {
        return $this->eventWebhook;
    }

    public function setEventWebhook(Webhook $eventWebhook): self
    {
        $this->eventWebhook = $eventWebhook;
        return $this;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function addToPayload(string $key, string $value): self
    {
        $this->payload[$key] = $value;
        return $this;
    }
}
