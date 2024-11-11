<?php

declare(strict_types=1);

namespace Vonage\Messages;

use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Messages\Channel\BaseMessage;

class Client implements APIClient
{
    public const RCS_STATUS_REVOKED = 'revoked';
    public const WHATSAPP_STATUS_READ = 'read';

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

        if ($message->validatesE164()) {
            if ($this->isValidE164($messageArray['to'])) {
                $messageArray['to'] = $this->stripLeadingPlus($messageArray['to']);
                return $this->getAPIResource()->create($messageArray);
            } else {
                throw new \InvalidArgumentException('Number provided is not a valid E164 number');
            }
        }

        return $this->getAPIResource()->create($messageArray);
    }

    public function updateRcsStatus(string $messageUuid, string $status): bool
    {
        try {
            $this->api->partiallyUpdate($messageUuid, ['status' => $status]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * This method is just a wrapper for updateRcsStatus to make it semantically
     * correct for other uses such as WhatsApp messages
     */
    public function markAsStatus(string $messageUuid, string $status): bool
    {
        return $this->updateRcsStatus($messageUuid, $status);
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
