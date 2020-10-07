<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage;

use Countable;
use InvalidArgumentException;
use Iterator;
use Vonage\Response\Message;

/**
 * Wrapper for Vonage API Response, provides access to the count and status of
 * the messages.
 */
class Response implements Countable, Iterator
{
    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * Response constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        if (!is_string($data)) {
            throw new InvalidArgumentException('expected response data to be a string');
        }

        $this->data = json_decode($data, true);
    }

    /**
     * @return array|mixed
     */
    public function getMessages()
    {
        return $this->data['messages'] ?? [];
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
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
     * @link http://php.net/manual/en/iterator.current.php
     * @return Message
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
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return int
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid(): bool
    {
        return isset($this->data['messages'][$this->position]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * @return mixed
     */
    public function toArray()
    {
        return $this->data;
    }
}
