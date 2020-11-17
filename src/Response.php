<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage;

use Countable;
use Iterator;
use InvalidArgumentException;
use Vonage\Response\Message;

use function json_decode;

/**
 * Wrapper for Vonage API Response, provides access to the count and status of
 * the messages.
 */
class Response implements Countable, Iterator
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * @todo Remove manual test, and throw JSON error instead in next major release
     * @var string $data
     */
    public function __construct($data)
    {
        if (!is_string($data)) {
            throw new InvalidArgumentException('expected response data to be a string');
        }

        $this->data = json_decode($data, true);
    }

    public function getMessages(): array
    {
        return $this->data['messages'] ?? [];
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     *
     * @link http://php.net/manual/en/countable.count.php
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count(): int
    {
        return (int)$this->data['message-count'];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     */
    public function current(): Message
    {
        if (!isset($this->messages[$this->position])) {
            $this->messages[$this->position] = new Message($this->data['messages'][$this->position]);
        }

        return $this->messages[$this->position];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * Returns true on success or false on failure.
     */
    public function valid(): bool
    {
        return isset($this->data['messages'][$this->position]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
