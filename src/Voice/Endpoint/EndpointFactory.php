<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
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
        switch ($data['type']) {
            case 'app':
                return App::factory($data['user']);
            case 'phone':
                return Phone::factory($data['number'], $data);
            case 'sip':
                return SIP::factory($data['uri'], $data);
            case 'vbc':
                return VBC::factory($data['extension']);
            case 'websocket':
                return Websocket::factory($data['uri'], $data);
            default:
                throw new RuntimeException('Unknown endpoint type');
        }
    }
}
