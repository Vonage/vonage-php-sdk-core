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

use function is_string;
use function json_decode;

class Input
{
    /**
     * @var array
     */
    protected $speech;

    /**
     * @var array
     */
    protected $dtmf;

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
     * @var string
     */
    protected $conversationUuid;

    /**
     * @var DateTimeImmutable
     */
    protected $timestamp;

    /**
     * Input constructor.
     *
     * @param array $data
     *
     * @throws Exception
     */
    public function __construct(array $data)
    {
        // GET requests push this in as a string
        if (is_string($data['speech'])) {
            $data['speech'] = json_decode($data['speech'], true);
        }

        $this->speech = $data['speech'];

        // GET requests push this in as a string
        if (is_string($data['dtmf'])) {
            $data['dtmf'] = json_decode($data['dtmf'], true);
        }

        $this->dtmf = $data['dtmf'];
        $this->to = $data['to'];
        $this->from = $data['from'];
        $this->uuid = $data['uuid'];
        $this->conversationUuid = $data['conversation_uuid'];
        $this->timestamp = new DateTimeImmutable($data['timestamp']);
    }

    /**
     * @return array
     */
    public function getSpeech(): array
    {
        return $this->speech;
    }

    /**
     * @return array
     */
    public function getDtmf(): array
    {
        return $this->dtmf;
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
