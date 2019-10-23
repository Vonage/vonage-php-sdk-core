<?php

namespace Nexmo\User;

use Nexmo\Entity\FilterInterface;

class Filter implements FilterInterface
{
    /**
     * @var string
     */
    protected $conversationId;

    /**
     * @var string
     */
    protected $id;

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function setConversationId(string $conversationId) : void
    {
        $this->conversationId = $conversationId;
    }
    
    public function getQuery() : array
    {
        $query = [];

        if (!empty($this->conversationId)) {
            $query['conversation_id'] = $this->conversationId;
        }

        if (!empty($this->id)) {
            $query['id'] = $this->id;
        }

        return $query;
    }
}
