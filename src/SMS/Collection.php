<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\SMS;

use Countable;
use Iterator;

class Collection implements Countable, Iterator
{
    /**
     * @var array{message-count: int, messages: array<string, mixed>}
     */
    protected $data;

    /**
     * @var int
     */
    protected $current = 0;

    /**
     * Collection constructor.
     *
     * @param array $apiResponse
     */
    public function __construct(array $apiResponse)
    {
        $this->data = $apiResponse;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return (int)$this->data['message-count'];
    }

    /**
     * @return SentSMS
     */
    public function current(): SentSMS
    {
        return new SentSMS($this->data['messages'][$this->current]);
    }

    /**
     * @return bool|float|int|string|null
     */
    public function key()
    {
        return $this->current;
    }

    public function next(): void
    {
        $this->current++;
    }

    public function rewind(): void
    {
        $this->current = 0;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->data['messages'][$this->current]);
    }
}
