<?php

declare(strict_types=1);

namespace Vonage\Conversation\ConversationObjects;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class UpdateConversationRequest implements ArrayHydrateInterface
{
    protected CreateConversationRequest $conversationRequest;

    public function __construct(
        protected string $id,
        protected array $data
    ) {
        $this->conversationRequest = new CreateConversationRequest(null, null, null);
        $this->conversationRequest->fromArray($this->data);
    }

    public function fromArray(array $data): static
    {
        $this->conversationRequest->fromArray($data);

        return $this;
    }

    public function toArray(): array
    {
        return $this->conversationRequest->toArray();
    }
}
