<?php

declare(strict_types=1);

namespace Vonage\Verify;

use DateTimeImmutable;

class CheckAttempt
{
    public const VALID = 'VALID';
    public const INVALID = 'INVALID';

    public function __construct(
        public readonly string $code,
        public readonly DateTimeImmutable $dateReceived,
        public readonly string $status,
        public readonly ?string $ipAddress,
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
