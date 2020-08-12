<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Message\Response;

use Vonage\Client\Response\Response;
use Vonage\Client\Response\Error;
use Vonage\Client\Response\ResponseInterface;

class Collection extends Response implements ResponseInterface, \Countable, \Iterator
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
     * @var Message[]
     */
    protected $messages = array();

    /**
     * @var int
     */
    protected $position = 0;

    public function __construct(array $data)
    {
        $this->expected = array('message-count', 'messages');
        $return = parent::__construct($data);

        $this->count = $data['message-count'];

        if (count($data['messages']) != $data['message-count']) {
            throw new \RuntimeException('invalid message count');
        }

        foreach ($data['messages'] as $message) {
            if (0 != $message['status']) {
                $this->messages[] = new Error($message);
            } else {
                $this->messages[] = new Message($message);
            }
        }

        $this->data = $data;

        return $return;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function isSuccess()
    {
        foreach ($this->messages as $message) {
            if ($message instanceof Error) {
                return false;
            }
        }

        return true;
    }

    public function count()
    {
        return $this->count;
    }

    /**
     * @link http://php.net/manual/en/iterator.current.php
     * @return Message
     */
    public function current()
    {
        return $this->messages[$this->position];
    }

    /**
     * @link http://php.net/manual/en/iterator.next.php
     * @return void
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * @link http://php.net/manual/en/iterator.key.php
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean
     */
    public function valid()
    {
        return $this->position < $this->count;
    }

    /**
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void
     */
    public function rewind()
    {
        $this->position = 0;
    }
}
