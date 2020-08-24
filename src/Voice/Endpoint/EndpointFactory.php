<?php
declare(strict_types=1);

namespace Vonage\Voice\Endpoint;

use Vonage\Entity\Factory\FactoryInterface;

class EndpointFactory implements FactoryInterface
{
    /**
     * @return EndpointInterface
     */
    public function create(array $data)
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
                throw new \RuntimeException('Unknown endpoint type');
        }
    }
}
