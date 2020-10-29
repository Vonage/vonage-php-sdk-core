<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Voice\Webhook;

use DateTimeImmutable;
use Exception;

class Transfer
{
    /**
     * @var string
     */
    protected $conversationUuidFrom;

    /**
     * @var string
     */
    protected $conversationUuidTo;

    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var DateTimeImmutable
     */
    protected $timestamp;

    /**
     * Transfer constructor.
     *
     * @param array $event
     * @throws Exception
     */
    public function __construct(array $event)
    {
        $this->conversationUuidFrom = $event['conversation_uuid_from'];
        $this->conversationUuidTo = $event['conversation_uuid_to'];
        $this->uuid = $event['uuid'];
        $this->timestamp = new DateTimeImmutable($event['timestamp']);
    }

    /**
     * @return string
     */
    public function getConversationUuidFrom(): string
    {
        return $this->conversationUuidFrom;
    }

    /**
     * @return string
     */
    public function getConversationUuidTo(): string
    {
        return $this->conversationUuidTo;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }
}
