<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Voice\Webhook;

use DateTimeImmutable;
use Exception;

class Notification
{
    /**
     * @var array<string, mixed>
     */
    protected $payload;

    /**
     * @var string
     */
    protected $conversationUuid;

    /**
     * @var DateTimeImmutable
     */
    protected $timestamp;

    /**
     * Notification constructor.
     *
     * @param array $data
     * @throws Exception
     */
    public function __construct(array $data)
    {
        if (is_string($data['payload'])) {
            $data['payload'] = json_decode($data['payload'], true);
        }

        $this->payload = $data['payload'];
        $this->conversationUuid = $data['conversation_uuid'];
        $this->timestamp = new DateTimeImmutable($data['timestamp']);
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @return string
     */
    public function getConversationUuid(): string
    {
        return $this->conversationUuid;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }
}
