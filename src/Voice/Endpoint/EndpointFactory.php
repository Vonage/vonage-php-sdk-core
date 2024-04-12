<?php

declare(strict_types=1);

namespace Vonage\Voice\Endpoint;

use RuntimeException;
use Vonage\Entity\Factory\FactoryInterface;

class EndpointFactory implements FactoryInterface
{

    public function create(array $data): ?EndpointInterface
    {
        return match ($data['type']) {
            'app' => App::factory($data['user']),
            'phone' => Phone::factory($data['number'], $data),
            'sip' => SIP::factory($data['uri'], $data),
            'vbc' => VBC::factory($data['extension']),
            'websocket' => Websocket::factory($data['uri'], $data),
            default => throw new RuntimeException('Unknown endpoint type'),
        };
    }
}
