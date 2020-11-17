<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Message\Response;

use Countable;
use Iterator;
use RuntimeException;
use Vonage\Client\Response\Error;
use Vonage\Client\Response\Response;

use function count;

class Collection extends Response implements Countable, Iterator
{
    /**
     * @var int
     */
    protected $count;

    /**
     * @var array
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

    public function __construct(array $data)
    {
        $this->expected = ['message-count', 'messages'];
        $this->count = $data['message-count'];

        parent::__construct($data);

        if (count($data['messages']) !== $data['message-count']) {
            throw new RuntimeException('invalid message count');
        }

        foreach ($data['messages'] as $message) {
            if (0 !== (int)$message['status']) {
                $this->messages[] = new Error($message);
            } else {
                $this->messages[] = new Message($message);
            }
        }

        $this->data = $data;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function isSuccess(): bool
    {
        foreach ($this->messages as $message) {
            if ($message instanceof Error) {
                return false;
            }
        }

        return true;
    }

    public function count(): int
    {
        return $this->count;
    }

    public function current(): Message
    {
        return $this->messages[$this->position];
    }

    public function next(): void
    {
        $this->position++;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return $this->position < $this->count;
    }

    /**
     * @link http://php.net/manual/en/iterator.rewind.php
     */
    public function rewind(): void
    {
        $this->position = 0;
    }
}
