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

class Error
{
    /**
     * @var string
     */
    protected $conversationUuid;

    /**
     * @var string
     */
    protected $reason;

    /**
     * @var DateTimeImmutable
     */
    protected $timestamp;

    /**
     * @throws Exception
     */
    public function __construct(array $event)
    {
        $this->conversationUuid = $event['conversation_uuid'];
        $this->reason = $event['reason'];
        $this->timestamp = new DateTimeImmutable($event['timestamp']);
    }

    public function getConversationUuid(): string
    {
        return $this->conversationUuid;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }
}
