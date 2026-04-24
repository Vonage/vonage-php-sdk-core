<?php

declare(strict_types=1);

namespace Vonage\Verify;

readonly class Check
{
    public function __construct(
        public string $requestId,
        public string $eventId,
        public string $status,
        public string $price,
        public string $currency,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: $data['request_id'],
            eventId: $data['event_id'],
            status: $data['status'],
            price: $data['price'],
            currency: $data['currency'],
        );
    }
}
