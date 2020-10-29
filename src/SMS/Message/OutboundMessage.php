<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\SMS\Message;

use InvalidArgumentException;

abstract class OutboundMessage implements Message
{
    /**
     * @var ?string
     */
    protected $accountRef;

    /**
     * @var string
     */
    protected $clientRef;

    /**
     * @var ?string
     */
    protected $deliveryReceiptCallback;

    /**
     * @var string
     */
    protected $from;

    /**
     * @var int
     */
    protected $messageClass;

    /**
     * @var bool
     */
    protected $requestDeliveryReceipt = true;

    /**
     * @var string
     */
    protected $to;

    /**
     * TTL of the SMS delivery, in milliseconds
     * @var int
     */
    protected $ttl = 259200000;

    /**
     * Type of message, set by the child class
     * @var string
     */
    protected $type;

    /**
     * OutboundMessage constructor.
     *
     * @param string $to
     * @param string $from
     */
    public function __construct(string $to, string $from)
    {
        $this->to = $to;
        $this->from = $from;
    }

    /**
     * @return array
     */
    abstract public function toArray(): array;

    public function getTtl(): int
    {
        return $this->ttl;
    }

    /**
     * @param int $ttl
     * @return $this
     */
    public function setTtl(int $ttl): self
    {
        if ($ttl < 20000 || $ttl > 604800000) {
            throw new InvalidArgumentException('SMS TTL must be in the range of 20000-604800000 milliseconds');
        }

        $this->ttl = $ttl;

        return $this;
    }

    /**
     * @return bool
     */
    public function getRequestDeliveryReceipt(): bool
    {
        return $this->requestDeliveryReceipt;
    }

    /**
     * @param bool $requestDeliveryReceipt
     * @return $this
     */
    public function setRequestDeliveryReceipt(bool $requestDeliveryReceipt): self
    {
        $this->requestDeliveryReceipt = $requestDeliveryReceipt;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDeliveryReceiptCallback(): ?string
    {
        return $this->deliveryReceiptCallback;
    }

    /**
     * @param string $deliveryReceiptCallback
     * @return $this
     */
    public function setDeliveryReceiptCallback(string $deliveryReceiptCallback): self
    {
        $this->deliveryReceiptCallback = $deliveryReceiptCallback;
        $this->setRequestDeliveryReceipt(true);

        return $this;
    }

    /**
     * @return int
     */
    public function getMessageClass(): int
    {
        return $this->messageClass;
    }

    /**
     * @param int $messageClass
     * @return $this
     */
    public function setMessageClass(int $messageClass): self
    {
        if ($messageClass < 0 || $messageClass > 3) {
            throw new InvalidArgumentException('Message Class must be 0-3');
        }

        $this->messageClass = $messageClass;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientRef(): string
    {
        return $this->clientRef;
    }

    /**
     * @param string $clientRef
     * @return $this
     */
    public function setClientRef(string $clientRef): self
    {
        if (strlen($clientRef) > 40) {
            throw new InvalidArgumentException('Client Ref can be no more than 40 characters');
        }

        $this->clientRef = $clientRef;

        return $this;
    }

    /**
     * This adds any additional options to an individual SMS request
     * This allows the child classes to set their special request options,
     * and then filter through here for additional request options;
     *
     * @param array $data
     * @return array
     */
    protected function appendUniversalOptions(array $data): array
    {
        $data = array_merge($data, [
            'to' => $this->getTo(),
            'from' => $this->getFrom(),
            'type' => $this->getType(),
            'ttl' => $this->getTtl(),
            'status-report-req' => (int)$this->getRequestDeliveryReceipt(),
        ]);

        if ($this->getRequestDeliveryReceipt() && !is_null($this->getDeliveryReceiptCallback())) {
            $data['callback'] = $this->getDeliveryReceiptCallback();
        }

        if (!is_null($this->messageClass)) {
            $data['message-class'] = $this->getMessageClass();
        }

        if ($this->accountRef) {
            $data['account-ref'] = $this->getAccountRef();
        }

        if ($this->clientRef) {
            $data['client-ref'] = $this->getClientRef();
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->to;
    }

    /**
     * @return string|null
     */
    public function getAccountRef(): ?string
    {
        return $this->accountRef;
    }

    /**
     * @param string $accountRef
     * @return $this
     */
    public function setAccountRef(string $accountRef): OutboundMessage
    {
        $this->accountRef = $accountRef;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
