<?php

declare(strict_types=1);

namespace Vonage\Messages;

use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Messages\Channel\BaseMessage;

class Client implements APIClient
{
    public function __construct(protected APIResource $api)
    {
    }

    public function getAPIResource(): APIResource
    {
        return $this->api;
    }

    public function send(BaseMessage $message): ?array
    {
        return $this->api->create($message->toArray());
    }
}
