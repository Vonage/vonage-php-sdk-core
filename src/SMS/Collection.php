<?php

declare(strict_types=1);

namespace Vonage\SMS;

use Countable;
use Iterator;

class Collection implements Countable, Iterator
{
    /**
     * @var int
     */
    protected $current = 0;

    /**
     * @param array<string, int|array<string, mixed>> $data
     */
    public function __construct(protected array $data)
    {
    }

    public function count(): int
    {
        return (int)$this->data['message-count'];
    }

    public function current(): SentSMS
    {
        return new SentSMS($this->data['messages'][$this->current]);
    }

    /**
     * @return bool|float|int|string|null
     */
    #[\ReturnTypeWillChange]
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

    public function valid(): bool
    {
        return isset($this->data['messages'][$this->current]);
    }

    public function getAllMessagesRaw(): array
    {
        return $this->data;
    }
}
