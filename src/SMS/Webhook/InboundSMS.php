<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\SMS\Webhook;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;

class InboundSMS
{
    public static $requiredFields = [
        'msisdn',
        'to',
        'messageId',
        'text',
        'type',
        'keyword',
        'message-timestamp'
    ];

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var bool
     */
    protected $concat = false;

    /**
     * @var ?int
     */
    protected $concatPart;

    /**
     * @var ?string
     */
    protected $concatRef;

    /**
     * @var ?int
     */
    protected $concatTotal;

    /**
     * @var ?string
     */
    protected $data;

    /**
     * @var string
     */
    protected $keyword;

    /**
     * @var string
     */
    protected $messageId;

    /**
     * @var DateTimeImmutable
     */
    protected $messageTimestamp;

    /**
     * @var string
     */
    protected $msisdn;

    /**
     * @var ?string
     */
    protected $nonce;

    /**
     * @var string
     */
    protected $signature;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var ?int
     */
    protected $timestamp;

    /**
     * @var string
     */
    protected $to;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var ?string
     */
    protected $udh;

    /**
     * InboundSMS constructor.
     *
     * @param array $data
     * @throws Exception
     */
    public function __construct(array $data)
    {
        foreach (static::$requiredFields as $key) {
            if (!array_key_exists($key, $data)) {
                throw new InvalidArgumentException('Incoming SMS missing required data `' . $key . '`');
            }
        }

        $this->apiKey = $data['api-key'] ?? null;
        $this->keyword = $data['keyword'];
        $this->messageId = $data['messageId'];
        $this->messageTimestamp = new DateTimeImmutable($data['message-timestamp']);
        $this->msisdn = $data['msisdn'];
        $this->nonce = $data['nonce'] ?? null;
        $this->signature = $data['sig'] ?? null;
        $this->text = $data['text'];
        $this->to = $data['to'];
        $this->type = $data['type'];

        if (array_key_exists('concat', $data)) {
            $this->concat = true;
            $this->concatPart = (int)$data['concat-part'];
            $this->concatRef = $data['concat-ref'];
            $this->concatTotal = (int)$data['concat-total'];
        }

        if ($this->type === 'binary' && array_key_exists('data', $data)) {
            $this->data = $data['data'];
            $this->udh = $data['udh'];
        }

        if (array_key_exists('timestamp', $data)) {
            $this->timestamp = (int)$data['timestamp'];
        }
    }

    /**
     * @return string|null
     */
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    /**
     * @return bool
     */
    public function getConcat(): bool
    {
        return $this->concat;
    }

    /**
     * @return int|null
     */
    public function getConcatPart(): ?int
    {
        return $this->concatPart;
    }

    /**
     * @return string|null
     */
    public function getConcatRef(): ?string
    {
        return $this->concatRef;
    }

    /**
     * @return int|null
     */
    public function getConcatTotal(): ?int
    {
        return $this->concatTotal;
    }

    /**
     * @return string|null
     */
    public function getData(): ?string
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getKeyword(): string
    {
        return $this->keyword;
    }

    /**
     * @return string
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * Time the message was accepted and delivery receipt was generated
     *
     * @return DateTimeImmutable
     */
    public function getMessageTimestamp(): DateTimeImmutable
    {
        return $this->messageTimestamp;
    }

    /**
     * @return string
     */
    public function getMsisdn(): string
    {
        return $this->msisdn;
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->msisdn;
    }

    /**
     * @return string
     */
    public function getNonce(): string
    {
        return $this->nonce;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Return the timestamp used for signature verification
     * If you are looking for the time of message creation, please use
     * `getMessageTimestamp()`
     *
     * @return int|null
     */
    public function getTimestamp(): ?int
    {
        return $this->timestamp;
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
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getUdh(): ?string
    {
        return $this->udh;
    }

    /**
     * @return string
     */
    public function getSignature(): string
    {
        return $this->signature;
    }
}
