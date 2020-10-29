<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Network\Number;

use BadMethodCallException;
use InvalidArgumentException;
use Vonage\Client\Response\Response as BaseResponse;

class Response extends BaseResponse
{
    protected $callbacks = [];

    /**
     * Response constructor.
     *
     * @param array $data
     * @param array $callbacks
     */
    public function __construct(array $data, $callbacks = [])
    {
        //add expected keys
        $this->expected = array_merge($this->expected, [
            'request_id',
            'number',
            'request_price',
            'remaining_balance',
            'callback_total_parts'
        ]);

        parent::__construct($data);

        foreach ($callbacks as $callback) {
            if (!($callback instanceof Callback)) {
                throw new InvalidArgumentException('callback must be of type: Vonage\Network\Number\Callback');
            }

            if ($callback->getId() !== $this->getId()) {
                throw new InvalidArgumentException('callback id must match request id');
            }
        }

        $this->callbacks = $callbacks;
    }

    /**
     * @return mixed
     */
    public function getCallbackTotal()
    {
        return $this->data['callback_total_parts'];
    }

    /**
     * @return bool
     */
    public function isComplete(): bool
    {
        return count($this->callbacks) === $this->getCallbackTotal();
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->data['request_price'];
    }

    /**
     * @return mixed
     */
    public function getBalance()
    {
        return $this->data['remaining_balance'];
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->data['number'];
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->data['request_id'];
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->data['status'];
    }

    /**
     * @param $name
     * @param $args
     * @return mixed
     * @todo This looks somewhat illogical
     */
    public function __call($name, $args)
    {
        if (empty($this->callbacks)) {
            throw new BadMethodCallException('can not check for response data without callback data');
        }

        foreach ($this->callbacks as $callback) {
            if ($last = $callback->$name()) {
                return $last;
            }
        }

        /** @noinspection PhpUndefinedVariableInspection */
        return $last;
    }

    /**
     * @return array
     */
    public function getCallbacks(): array
    {
        return $this->callbacks;
    }

    /**
     * @param Response $response
     * @param Callback $callback
     * @return Response
     */
    public static function addCallback(Response $response, callable $callback): Response
    {
        $callbacks = $response->getCallbacks();
        $callbacks[] = $callback;

        return new static($response->getData(), $callbacks);
    }
}
