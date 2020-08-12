<?php
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

    protected $current = 0;

    public function __construct(array $apiResponse)
    {
        $this->data = $apiResponse;
    }

    public function count()
    {
        return (int) $this->data['message-count'];
    }

    public function current() : SentSMS
    {
        return new SentSMS($this->data['messages'][$this->current]);
    }

    public function key()
    {
        return $this->current;
    }

    public function next()
    {
        $this->current++;
    }

    public function rewind()
    {
        $this->current = 0;
    }

    public function valid()
    {
        return isset($this->data['messages'][$this->current]);
    }
}
