<?php

declare(strict_types=1);

namespace Vonage\Verify;

use DateTimeImmutable;

readonly class CheckAttempt
{
    public const VALID = 'VALID';
    public const INVALID = 'INVALID';

    public function __construct(
        public string $code,
        public DateTimeImmutable $dateReceived,
        public string $status,
        public ?string $ipAddress,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['code'],
            dateReceived: new DateTimeImmutable($data['date_received']),
            status: $data['status'],
            ipAddress: isset($data['ip_address']) && $data['ip_address'] !== '' ? $data['ip_address'] : null,
        );
    }
}
