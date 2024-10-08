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
        $messageArray = $message->toArray();

        if ($this->isValidE164($messageArray['to'])) {
            $messageArray['to'] = $this->stripLeadingPlus($messageArray['to']);
            return $this->getAPIResource()->create($messageArray);
        };

        throw new \InvalidArgumentException('Number provided is not a valid E164 number');
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

    protected function stripLeadingPlus(string $phoneNumber): string
    {
        if (str_starts_with($phoneNumber, '+')) {
            return substr($phoneNumber, 1);
        }

        return $phoneNumber;
    }

    public function isValidE164(string $phoneNumber): bool
    {
        $phoneNumber = $this->stripLeadingPlus($phoneNumber);

        $regex = '/^\+?[1-9]\d{1,14}$/';

        return preg_match($regex, $phoneNumber) === 1;
    }
}
