<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\SMS;

class SentSMS
{
    /**
     * @var ?string
     */
    protected $accountRef;

    /**
     * @var ?string
     */
    protected $clientRef;

    /**
     * @var string
     */
    protected $messageId;

    /**
     * @var string
     */
    protected $messagePrice;

    /**
     * @var string
     */
    protected $network;

    /**
     * @var string
     */
    protected $remainingBalance;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var string
     */
    protected $to;

    public function __construct(array $data)
    {
        $this->accountRef = $data['account-ref'] ?? null;
        $this->clientRef = $data['client-ref'] ?? null;
        $this->to = $data['to'];
        $this->messageId = $data['message-id'];
        $this->status = (int)$data['status'];
        $this->remainingBalance = $data['remaining-balance'];
        $this->messagePrice = $data['message-price'];
        $this->network = $data['network'];
    }

    public function getAccountRef(): ?string
    {
        return $this->accountRef;
    }

    public function getClientRef(): ?string
    {
        return $this->clientRef;
    }

    public function getMessageId(): string
    {
        return $this->messageId;
    }

    public function getMessagePrice(): string
    {
        return $this->messagePrice;
    }

    public function getNetwork(): string
    {
        return $this->network;
    }

    public function getRemainingBalance(): string
    {
        return $this->remainingBalance;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getTo(): string
    {
        return $this->to;
    }
}
