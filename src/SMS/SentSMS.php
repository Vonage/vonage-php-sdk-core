<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
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

    /**
     * SentSMS constructor.
     *
     * @param array $data
     */
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

    /**
     * @return string|null
     */
    public function getAccountRef(): ?string
    {
        return $this->accountRef;
    }

    /**
     * @return string|null
     */
    public function getClientRef(): ?string
    {
        return $this->clientRef;
    }

    /**
     * @return string
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * @return string
     */
    public function getMessagePrice(): string
    {
        return $this->messagePrice;
    }

    /**
     * @return string
     */
    public function getNetwork(): string
    {
        return $this->network;
    }

    /**
     * @return string
     */
    public function getRemainingBalance(): string
    {
        return $this->remainingBalance;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->to;
    }
}
