<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Message;

use ArrayAccess;
use Countable;
use DateTime;
use Iterator;
use RuntimeException;
use Vonage\Client\Exception\Exception;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Entity\JsonResponseTrait;
use Vonage\Entity\Psr7Trait;
use Vonage\Entity\RequestArrayTrait;

/**
 * Abstract Message
 *
 * Extended by concrete message types (text, binary, etc).
 */
class Message implements MessageInterface, Countable, ArrayAccess, Iterator, ArrayHydrateInterface
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

    protected $autodetectEncoding = false;

    protected $data = [];

    /**
     * @param string $idOrTo Message ID or E.164 (international) formatted number to send the message
     * @param null|string $from Number or name the message is from
     * @param array $additional Additional API Params
     */
    public function __construct(string $idOrTo, $from = null, $additional = [])
    {
        if (is_null($from)) {
            $this->id = $idOrTo;

            return;
        }

        $this->requestData['to'] = $idOrTo;
        $this->requestData['from'] = (string)$from;

        if (static::TYPE) {
            $this->requestData['type'] = static::TYPE;
        }

        $this->requestData = array_merge($this->requestData, $additional);
    }

    /**
     * Boolean indicating if you would like to receive a Delivery Receipt
     *
     * @param bool $dlr
     * @return RequestArrayTrait|$this
     * @throws \Exception
     */
    public function requestDLR($dlr = true)
    {
        return $this->setRequestData('status-report-req', $dlr ? 1 : 0);
    }

    /**
     * Webhook endpoint the delivery receipt is sent to for this message
     * This overrides the setting in the Dashboard, and should be a full URL
     *
     * @param string $callback
     * @return RequestArrayTrait|$this
     * @throws \Exception
     */
    public function setCallback(string $callback)
    {
        return $this->setRequestData('callback', $callback);
    }

    /**
     * Optional reference of up to 40 characters
     *
     * @param string $ref
     * @return RequestArrayTrait|$this
     * @throws \Exception
     */
    public function setClientRef(string $ref)
    {
        return $this->setRequestData('client-ref', $ref);
    }

    /**
     * The Mobile Country Code Mobile Network Code (MCCMNC) this number is registered with
     *
     * @param string $network
     * @return RequestArrayTrait|$this
     * @throws \Exception
     */
    public function setNetwork(string $network)
    {
        return $this->setRequestData('network-code', $network);
    }

    /**
     * The duration in milliseconds the delivery of an SMS will be attempted
     * By default this is set to 72 hours, but can be overridden if needed.
     * Vonage recommends no shorter than 30 minutes, and to keep at default
     * when possible.
     *
     * @param int $ttl
     * @return RequestArrayTrait|Message
     * @throws \Exception
     */
    public function setTTL(int $ttl)
    {
        return $this->setRequestData('ttl', $ttl);
    }

    /**
     * The Data Coding Scheme value of this message
     * Should be 0, 1, 2, or 3
     *
     * @param int $class
     * @return RequestArrayTrait|Message
     * @throws \Exception
     */
    public function setClass(int $class)
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
     * @return int|void
     * @throws \Exception
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
     * @param null $index
     * @return mixed|string|null
     * @throws \Exception
     */
    public function getMessageId($index = null): ?string
    {
        return $this->id ?? $this->getMessageData('message-id', $index);
    }

    /**
     * @param null $index
     * @return mixed|null
     * @throws \Exception
     */
    public function getStatus($index = null)
    {
        return $this->getMessageData('status', $index);
    }

    /**
     * @param null $index
     * @return mixed|null
     * @throws \Exception
     */
    public function getFinalStatus($index = null)
    {
        return $this->getMessageData('final-status', $index);
    }

    /**
     * @param null $index
     * @return mixed|null
     * @throws \Exception
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
     * @param null $index
     * @return mixed|null
     * @throws \Exception
     */
    public function getRemainingBalance($index = null)
    {
        return $this->getMessageData('remaining-balance', $index);
    }

    /**
     * @param null $index
     * @return mixed|null
     * @throws \Exception
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
     * @param null $index
     * @return mixed|null
     * @throws \Exception
     */
    public function getNetwork($index = null)
    {
        return $this->getMessageData('network', $index);
    }

    /**
     * @return mixed|void
     * @throws \Exception
     */
    public function getDeliveryStatus()
    {
        @$data = $this->getResponseData();

        //check if this is data from a send request
        //(which also has a status, but it's not the same)
        if (isset($data['messages'])) {
            return;
        }

        return $this->data['status'];
    }

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->data['from'];
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->data['body'];
    }

    /**
     * @return DateTime
     * @throws \Exception
     */
    public function getDateReceived(): DateTime
    {
        return new DateTime($this->data['date-received']);
    }

    /**
     * @return mixed
     */
    public function getDeliveryError()
    {
        return $this->data['error-code'];
    }

    /**
     * @return mixed
     */
    public function getDeliveryLabel()
    {
        return $this->data['error-code-label'];
    }

    /**
     * @return bool
     */
    public function isEncodingDetectionEnabled(): bool
    {
        return $this->autodetectEncoding;
    }

    /**
     * @param $name
     * @param null $index
     * @return mixed|null
     * @throws \Exception
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

    /**
     * @return string|null
     */
    protected function detectEncoding(): ?string
    {
        if (!isset($this->requestData['text'])) {
            return static::TYPE;
        }

        // Auto detect unicode messages
        $detector = new EncodingDetector;

        if ($detector->requiresUnicodeEncoding($this->requestData['text'])) {
            return Unicode::TYPE;
        }

        return static::TYPE;
    }

    /**
     * @param mixed $offset
     * @return bool
     * @throws Exception
     * @throws \Exception
     */
    public function offsetExists($offset): bool
    {
        trigger_error(
            "Array access for " . get_class($this) . " is deprecated, please use getter methods",
            E_USER_DEPRECATED
        );

        $response = $this->getResponseData();

        if (isset($this->index)) {
            $response = $response['items'][$this->index];
        }

        $request = $this->getRequestData();
        $dirty = $this->getRequestData(false);

        if (isset($response[$offset]) || isset($request[$offset]) || isset($dirty[$offset])) {
            return true;
        }

        //provide access to split messages by virtual index
        if (is_int($offset) && $offset < $this->count()) {
            return true;
        }

        return false;
    }

    /**
     * @param mixed $offset
     * @return mixed
     * @throws Exception
     * @throws \Exception
     */
    public function offsetGet($offset)
    {
        trigger_error(
            "Array access for " . get_class($this) . " is deprecated, please use getter methods",
            E_USER_DEPRECATED
        );

        $response = $this->getResponseData();

        if (isset($this->index)) {
            $response = $response['items'][$this->index];
        }

        $request = $this->getRequestData();
        $dirty = $this->getRequestData(false);

        if (isset($response[$offset])) {
            return $response[$offset];
        }

        //provide access to split messages by virtual index, if there is data
        if (isset($response['messages'])) {
            if (is_int($offset) && isset($response['messages'][$offset])) {
                return $response['messages'][$offset];
            }

            $index = $this->count() - 1;

            if (isset($response['messages'][$index][$offset])) {
                return $response['messages'][$index][$offset];
            }
        }

        return $request[$offset] ?? $dirty[$offset] ?? null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        throw $this->getReadOnlyException($offset);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        throw $this->getReadOnlyException($offset);
    }

    /**
     * @param $offset
     * @return RuntimeException
     */
    protected function getReadOnlyException($offset): RuntimeException
    {
        return new RuntimeException(sprintf(
            'can not modify `%s` using array access',
            $offset
        ));
    }

    /**
     * @return mixed|null
     * @throws \Exception
     */
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

    /**
     * @return bool|float|int|string|null
     */
    public function key()
    {
        if (!isset($this->response)) {
            return null;
        }

        return $this->current;
    }

    /**
     * @return bool|null
     * @throws \Exception
     */
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

    /**
     * @param array $data
     */
    public function fromArray(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
