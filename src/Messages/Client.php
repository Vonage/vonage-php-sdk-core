<?php

declare(strict_types=1);

namespace Vonage\Messages;

use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Messages\Channel\BaseMessage;

class Client implements APIClient
{
    public const RCS_STATUS_REVOKED = 'revoked';

    public function __construct(protected APIResource $api)
    {
    }

    public function getAPIResource(): APIResource
    {
        return $this->api;
    }

    public function send(BaseMessage $message): ?array
    {
        return $this->getAPIResource()->create($message->toArray());
    }

    public function updateRcsStatus(string $messageUuid, string $status): bool
    {
        try {
            $this->api->partiallyUpdate($messageUuid, ['status' => $status]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }
}
