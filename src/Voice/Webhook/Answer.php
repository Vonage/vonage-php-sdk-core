<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Voice\Webhook;

class Answer
{
    /**
     * @var string
     */
    protected $conversationUuid;

    /**
     * @var string
     */
    protected $from;

    /**
     * @var string
     */
    protected $to;

    /**
     * @var string
     */
    protected $uuid;

    /**
     * Answer constructor.
     *
     * @param array $event
     */
    public function __construct(array $event)
    {
        $this->from = $event['from'];
        $this->to = $event['to'];
        $this->uuid = $event['uuid'] ?? $event['call_uuid'];
        $this->conversationUuid = $event['conversation_uuid'];
    }

    /**
     * @return string
     */
    public function getConversationUuid(): string
    {
        return $this->conversationUuid;
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->to;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }
}
