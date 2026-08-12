<?php

declare(strict_types=1);

namespace Vonage\Verify;

use DateTimeImmutable;
use Exception;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class Verification implements ArrayHydrateInterface
{
    public const FAILED = 'FAILED';
    public const SUCCESSFUL = 'SUCCESSFUL';
    public const EXPIRED = 'EXPIRED';
    public const IN_PROGRESS = 'IN PROGRESS';

    protected ?string $requestId = null;
    protected ?string $number = null;
    protected ?string $brand = null;
    protected ?string $country = null;
    protected ?string $senderId = null;
    protected ?int $codeLength = null;
    protected ?string $language = null;
    protected ?string $requireType = null;
    protected ?int $pinExpiry = null;
    protected ?int $nextEventWait = null;
    protected ?int $workflowId = null;
    protected ?string $accountId = null;
    protected ?string $price = null;
    protected ?string $currency = null;
    protected ?string $status = null;
    protected ?string $dateSubmitted = null;
    protected ?string $dateFinalized = null;
    protected ?string $firstEventDate = null;
    protected ?string $lastEventDate = null;
    protected array $checks = [];

    public function __construct(
        ?string $number = null,
        ?string $brand = null,
        ?string $requestId = null,
    ) {
        $this->number = $number;
        $this->brand = $brand;
        $this->requestId = $requestId;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getSenderId(): ?string
    {
        return $this->senderId;
    }

    public function setSenderId(string $senderId): static
    {
        $this->senderId = $senderId;

        return $this;
    }

    public function getCodeLength(): ?int
    {
        return $this->codeLength;
    }

    public function setCodeLength(int $codeLength): static
    {
        $this->codeLength = $codeLength;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function getRequireType(): ?string
    {
        return $this->requireType;
    }

    public function setRequireType(string $requireType): static
    {
        $this->requireType = $requireType;

        return $this;
    }

    public function getPinExpiry(): ?int
    {
        return $this->pinExpiry;
    }

    public function setPinExpiry(int $pinExpiry): static
    {
        $this->pinExpiry = $pinExpiry;

        return $this;
    }

    public function getNextEventWait(): ?int
    {
        return $this->nextEventWait;
    }

    public function setNextEventWait(int $nextEventWait): static
    {
        $this->nextEventWait = $nextEventWait;

        return $this;
    }

    public function getWorkflowId(): ?int
    {
        return $this->workflowId;
    }

    public function setWorkflowId(int $workflowId): static
    {
        $this->workflowId = $workflowId;

        return $this;
    }

    public function getAccountId(): ?string
    {
        return $this->accountId;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @return Check[]
     */
    public function getChecks(): array
    {
        return array_map(
            static fn(array $check) => new Check($check),
            $this->checks
        );
    }

    /**
     * @throws Exception
     */
    public function getSubmitted(): ?DateTimeImmutable
    {
        return $this->dateSubmitted ? new DateTimeImmutable($this->dateSubmitted) : null;
    }

    /**
     * @throws Exception
     */
    public function getFinalized(): ?DateTimeImmutable
    {
        return $this->dateFinalized ? new DateTimeImmutable($this->dateFinalized) : null;
    }

    /**
     * @throws Exception
     */
    public function getFirstEvent(): ?DateTimeImmutable
    {
        return $this->firstEventDate ? new DateTimeImmutable($this->firstEventDate) : null;
    }

    /**
     * @throws Exception
     */
    public function getLastEvent(): ?DateTimeImmutable
    {
        return $this->lastEventDate ? new DateTimeImmutable($this->lastEventDate) : null;
    }

    public function toArray(): array
    {
        $data = array_filter([
            'request_id' => $this->requestId,
            'number' => $this->number,
            'brand' => $this->brand,
            'country' => $this->country,
            'sender_id' => $this->senderId,
            'code_length' => $this->codeLength,
            'lg' => $this->language,
            'require_type' => $this->requireType,
            'pin_expiry' => $this->pinExpiry,
            'next_event_wait' => $this->nextEventWait,
            'workflow_id' => $this->workflowId,
            'account_id' => $this->accountId,
            'price' => $this->price,
            'currency' => $this->currency,
            'status' => $this->status,
            'date_submitted' => $this->dateSubmitted,
            'date_finalized' => $this->dateFinalized,
            'first_event_date' => $this->firstEventDate,
            'last_event_date' => $this->lastEventDate,
            'checks' => $this->checks ?: null,
        ], static fn($value) => $value !== null);

        return $data;
    }

    public function fromArray(array $data): void
    {
        $this->requestId = $data['request_id'] ?? null;
        $this->number = $data['number'] ?? null;
        $this->brand = $data['brand'] ?? null;
        $this->country = $data['country'] ?? null;
        $this->senderId = $data['sender_id'] ?? null;
        $this->codeLength = isset($data['code_length']) ? (int) $data['code_length'] : null;
        $this->language = $data['lg'] ?? null;
        $this->requireType = $data['require_type'] ?? null;
        $this->pinExpiry = isset($data['pin_expiry']) ? (int) $data['pin_expiry'] : null;
        $this->nextEventWait = isset($data['next_event_wait']) ? (int) $data['next_event_wait'] : null;
        $this->workflowId = isset($data['workflow_id']) ? (int) $data['workflow_id'] : null;
        $this->accountId = $data['account_id'] ?? null;
        $this->price = $data['price'] ?? null;
        $this->currency = $data['currency'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->dateSubmitted = $data['date_submitted'] ?? null;
        $this->dateFinalized = $data['date_finalized'] ?? null;
        $this->firstEventDate = $data['first_event_date'] ?? null;
        $this->lastEventDate = $data['last_event_date'] ?? null;
        $this->checks = $data['checks'] ?? [];
    }
}
