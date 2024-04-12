<?php

declare(strict_types=1);

namespace Vonage\Voice\Endpoint;

use JsonSerializable;

interface EndpointInterface extends JsonSerializable
{

    public function getId(): string;

    /**
     * @return array<string, array>
     */
    public function toArray(): array;
}
