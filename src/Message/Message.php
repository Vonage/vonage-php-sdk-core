<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Message;

use Countable;
use DateTime;
use Exception;
use Iterator;
use RuntimeException;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Entity\JsonResponseTrait;
use Vonage\Entity\Psr7Trait;
use Vonage\Entity\RequestArrayTrait;

use function array_merge;
use function count;
use function get_class;
use function is_int;
use function is_null;
use function sprintf;
use function trigger_error;

/**
 * Abstract Message
 *
 * Extended by concrete message types (text, binary, etc).
 */
class Message implements MessageInterface, Countable, Iterator, ArrayHydrateInterface
{
    use Psr7Trait;
    use JsonResponseTrait;
    use RequestArrayTrait;
    use CollectionTrait;

    public const TYPE = null;

    public const CLASS_FLASH = 0;

    protected $responseParams = [
        'status',
        'message-id',
        'to',
        'remaining-balance',
        'message-price',
        'network'
    ];

    protected $current = 0;

    protected $id;

    /**
     * @var bool
     */
    protected $autodetectEncoding = false;

    /**
     * @var array
     */
    protected $data = [];

    public function __construct(string $idOrTo, ?string $from = null, array $additional = [])
    {
        if (is_null($from)) {
            $this->id = $idOrTo;

            return;
        }

        $this->requestData['to'] = $idOrTo;
        $this->requestData['from'] = $from;

        if (static::TYPE) {
            $this->requestData['type'] = static::TYPE;
        }

        $this->requestData = array_merge($this->requestData, $additional);
    }

    /**
     * Boolean indicating if you would like to receive a Delivery Receipt
     *
     * @throws Exception
     */
    public function requestDLR($dlr = true): self
    {
        return $this->setRequestData('status-report-req', $dlr ? 1 : 0);
    }

    /**
     * Webhook endpoint the delivery receipt is sent to for this message
     * This overrides the setting in the Dashboard, and should be a full URL
     *
     * @throws Exception
     */
    public function setCallback(string $callback): self
    {
        return $this->setRequestData('callback', $callback);
    }

    /**
     * Optional reference of up to 40 characters
     *
     * @throws Exception
     */
    public function setClientRef(string $ref): self
    {
        return $this->setRequestData('client-ref', $ref);
    }

    /**
     * The Mobile Country Code Mobile Network Code (MCCMNC) this number is registered with
     *
     * @throws Exception
     */
    public function setNetwork(string $network): self
    {
        return $this->setRequestData('network-code', $network);
    }

    /**
     * The duration in milliseconds the delivery of an SMS will be attempted
     * By default this is set to 72 hours, but can be overridden if needed.
     * Vonage recommends no shorter than 30 minutes, and to keep at default
     * when possible.
     *
     * @throws Exception
     */
    public function setTTL(int $ttl): self
    {
        return $this->setRequestData('ttl', $ttl);
    }

    /**
     * The Data Coding Scheme value of this message
     * Should be 0, 1, 2, or 3
     *
     * @throws Exception
     */
    public function setClass(int $class): self
    {
        return $this->setRequestData('message-class', $class);
    }

    public function enableEncodingDetection(): void
    {
        $this->autodetectEncoding = true;
    }

    public function disableEncodingDetection(): void
    {
        $this->autodetectEncoding = false;
    }

    /**
     * @throws Exception
     */
    public function count(): int
    {
        $data = $this->getResponseData();

        if (!isset($data['messages'])) {
            return 0;
        }

        return count($data['messages']);
    }

    /**
     * @param null|mixed $index
     *
     * @throws Exception
     */
    public function getMessageId($index = null): ?string
    {
        return $this->id ?? $this->getMessageData('message-id', $index);
    }

    /**
     * @param null|mixed $index
     *
     * @throws Exception
     */
    public function getStatus($index = null)
    {
        return $this->getMessageData('status', $index);
    }

    /**
     * @param null|mixed $index
     *
     * @throws Exception
     */
    public function getFinalStatus($index = null)
    {
        return $this->getMessageData('final-status', $index);
    }

    /**
     * @param null|mixed $index
     *
     * @throws Exception
     */
    public function getTo($index = null)
    {
        $data = @$this->getResponseData();

        //check if this is data from a send request
        //(which also has a status, but it's not the same)
        if (isset($data['messages'])) {
            return $this->getMessageData('to', $index);
        }

        return $this->data['to'];
    }

    /**
     * @param null|mixed $index
     *
     * @throws Exception
     */
    public function getRemainingBalance($index = null)
    {
        return $this->getMessageData('remaining-balance', $index);
    }

    /**
     * @param null|mixed $index
     *
     * @throws Exception
     */
    public function getPrice($index = null)
    {
        $data = $this->getResponseData();

        //check if this is data from a send request
        //(which also has a status, but it's not the same)
        if (isset($data['messages'])) {
            return $this->getMessageData('message-price', $index);
        }

        return $this->data['price'];
    }

    /**
     * @param null|mixed $index
     *
     * @throws Exception
     */
    public function getNetwork($index = null)
    {
        return $this->getMessageData('network', $index);
    }

    /**
     * @throws Exception
     */
    public function getDeliveryStatus()
    {
        @$data = $this->getResponseData();

        //check if this is data from a send request
        //(which also has a status, but it's not the same)
        if (isset($data['messages'])) {
            return null;
        }

        return $this->data['status'];
    }

    public function getFrom()
    {
        return $this->data['from'];
    }

    public function getBody()
    {
        return $this->data['body'];
    }

    /**
     * @throws Exception
     */
    public function getDateReceived(): DateTime
    {
        return new DateTime($this->data['date-received']);
    }

    public function getDeliveryError()
    {
        return $this->data['error-code'];
    }

    public function getDeliveryLabel()
    {
        return $this->data['error-code-label'];
    }

    public function isEncodingDetectionEnabled(): bool
    {
        return $this->autodetectEncoding;
    }

    /**
     * @param null|mixed $index
     *
     * @throws Exception
     */
    protected function getMessageData($name, $index = null)
    {
        if (!isset($this->response)) {
            return null;
        }

        $data = $this->getResponseData();

        if (is_null($index)) {
            $index = $this->count() - 1;
        }

        if (isset($data['messages'])) {
            return $data['messages'][$index][$name];
        }

        return $data[$name] ?? null;
    }

    protected function preGetRequestDataHook(): void
    {
        // If $autodetectEncoding is true, we want to set the `type`
        // field in our payload
        if ($this->isEncodingDetectionEnabled()) {
            $this->requestData['type'] = $this->detectEncoding();
        }
    }

    protected function detectEncoding(): ?string
    {
        if (!isset($this->requestData['text'])) {
            return static::TYPE;
        }

        // Auto detect unicode messages
        $detector = new EncodingDetector();

        if ($detector->requiresUnicodeEncoding($this->requestData['text'])) {
            return Unicode::TYPE;
        }

        return static::TYPE;
    }

    protected function getReadOnlyException($offset): RuntimeException
    {
        return new RuntimeException(
            sprintf(
                'can not modify `%s` using array access',
                $offset
            )
        );
    }

    /**
     * @throws Exception
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        if (!isset($this->response)) {
            return null;
        }

        $data = $this->getResponseData();
        return $data['messages'][$this->current];
    }

    public function next(): void
    {
        $this->current++;
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        if (!isset($this->response)) {
            return null;
        }

        return $this->current;
    }

    /**
     * @throws Exception
     */
    #[\ReturnTypeWillChange]
    public function valid(): ?bool
    {
        if (!isset($this->response)) {
            return null;
        }

        $data = $this->getResponseData();
        return isset($data['messages'][$this->current]);
    }

    public function rewind(): void
    {
        $this->current = 0;
    }

    public function fromArray(array $data): void
    {
        $this->data = $data;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
