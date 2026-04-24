<?php

declare(strict_types=1);

namespace Vonage\Verify;

use DateTimeImmutable;

readonly class Verification
{
    public const STATUS_FAILED = 'FAILED';
    public const STATUS_SUCCESSFUL = 'SUCCESS';
    public const STATUS_EXPIRED = 'EXPIRED';
    public const STATUS_IN_PROGRESS = 'IN PROGRESS';
    public const STATUS_CANCELLED = 'CANCELLED';

    /**
     * @param CheckAttempt[] $checks
     */
    public function __construct(
        public string $requestId,
        public string $accountId,
        public string $status,
        public string $number,
        public string $price,
        public string $currency,
        public string $senderId,
        public DateTimeImmutable $dateSubmitted,
        public ?DateTimeImmutable $dateFinalized,
        public DateTimeImmutable $firstEventDate,
        public DateTimeImmutable $lastEventDate,
        public array $checks,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $checks = [];
        foreach ($data['checks'] ?? [] as $checkData) {
            $checks[] = CheckAttempt::fromArray($checkData);
        }

        return new self(
            requestId: $data['request_id'],
            accountId: $data['account_id'],
            status: $data['status'],
            number: $data['number'],
            price: $data['price'],
            currency: $data['currency'],
            senderId: $data['sender_id'],
            dateSubmitted: new DateTimeImmutable($data['date_submitted']),
            dateFinalized: isset($data['date_finalized']) && $data['date_finalized'] !== ''
                ? new DateTimeImmutable($data['date_finalized'])
                : null,
            firstEventDate: new DateTimeImmutable($data['first_event_date']),
            lastEventDate: new DateTimeImmutable($data['last_event_date']),
            checks: $checks,
        );
    }
}
