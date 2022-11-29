<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Network\Number;

use BadMethodCallException;
use InvalidArgumentException;
use Vonage\Client\Response\Response as BaseResponse;

use function array_merge;
use function count;

class Response extends BaseResponse
{
    protected $callbacks = [];

    public function __construct(array $data, array $callbacks = [])
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

    public function getCallbackTotal()
    {
        return $this->data['callback_total_parts'];
    }

    public function isComplete(): bool
    {
        return count($this->callbacks) === $this->getCallbackTotal();
    }

    public function getPrice()
    {
        return $this->data['request_price'];
    }

    public function getBalance()
    {
        return $this->data['remaining_balance'];
    }

    public function getNumber()
    {
        return $this->data['number'];
    }

    public function getId()
    {
        return $this->data['request_id'];
    }

    public function getStatus()
    {
        return $this->data['status'];
    }

    /**
     * @param $name
     * @param $args
     *
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

    public function getCallbacks(): array
    {
        return $this->callbacks;
    }

    public static function addCallback(Response $response, callable $callback): Response
    {
        $callbacks = $response->getCallbacks();
        $callbacks[] = $callback;

        return new static($response->getData(), $callbacks);
    }
}
