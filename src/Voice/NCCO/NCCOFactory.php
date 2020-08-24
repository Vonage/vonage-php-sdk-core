<?php
declare(strict_types=1);

namespace Vonage\Voice\NCCO;

use Vonage\Voice\NCCO\Action\Talk;
use Vonage\Voice\NCCO\Action\Input;
use Vonage\Voice\NCCO\Action\Record;
use Vonage\Voice\NCCO\Action\Stream;
use Vonage\Voice\NCCO\Action\Connect;
use Vonage\Voice\Endpoint\EndpointFactory;
use Vonage\Voice\NCCO\Action\Conversation;
use Vonage\Voice\NCCO\Action\ActionInterface;
use Vonage\Voice\NCCO\Action\Notify;

class NCCOFactory
{
    public function build($data) : ActionInterface
    {
        switch ($data['action']) {
            case 'connect':
                $factory = new EndpointFactory();
                $endpoint = $factory->create($data['endpoint'][0]);

                return Connect::factory($endpoint, $data);
            case 'conversation':
                return Conversation::factory($data['name'], $data);
            case 'input':
                return Input::factory($data);
            case 'notify':
                return Notify::factory($data['payload'], $data);
            case 'record':
                return Record::factory($data);
            case 'stream':
                return Stream::factory($data['streamUrl'], $data);
            case 'talk':
                return Talk::factory($data['text'], $data);
            default:
                throw new \InvalidArgumentException("Unknown NCCO Action " . $data['action']);
        }
    }
}
