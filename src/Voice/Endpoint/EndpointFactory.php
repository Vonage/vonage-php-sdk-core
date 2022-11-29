<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

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
