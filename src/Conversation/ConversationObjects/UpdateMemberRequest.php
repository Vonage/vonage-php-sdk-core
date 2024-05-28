<?php

declare(strict_types=1);

namespace Vonage\Conversation\ConversationObjects;

use InvalidArgumentException;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class UpdateMemberRequest implements ArrayHydrateInterface
{
    public const MEMBER_STATE_INVITED = 'invited';
    public const MEMBER_STATE_LEFT = 'left';

    protected array $allowedStates = [self::MEMBER_STATE_INVITED, self::MEMBER_STATE_LEFT];
    protected array $data;
    protected ?string $from = null;

    public function __construct(
        protected string $memberId,
        protected string $conversationId,
        protected string $state
    ) {
        if (! in_array($state, $this->allowedStates, true)) {
            throw new \InvalidArgumentException($state . 'is not a valid state');
        }

        $this->data['state'] = $this->state;
    }

    public function fromArray(array $data): UpdateMemberRequest
    {
        $this->data = $data;

        return $this;
    }

    public function toArray(): array
    {
        if ($this->getState() === self::MEMBER_STATE_LEFT && !$this->getLeavingReason()) {
            throw new \RuntimeException('Leaving reason is required for members with status left');
        }

        $returnArray = [];

        if ($this->getState()) {
            $returnArray['state'] = $this->getState();
        }

        if ($this->getFrom()) {
            $returnArray['from'] = $this->getFrom();
        }

        if ($this->getLeavingReason()) {
            $returnArray['reason'] = $this->getLeavingReason();
        }

        return $returnArray;
    }

    protected function getLeavingReason(): ?array
    {
        return $this->data['leavingReason'];
    }

    public function getFrom(): ?string
    {
        return $this->from;
    }

    public function setFrom(?string $from): UpdateMemberRequest
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @param array{code: string, text: string} $leavingReason
     *
     * @return $this
     */
    public function setLeavingReason(array $leavingReason): UpdateMemberRequest
    {
        if (array_diff(array_keys($leavingReason), ['code', 'text'])) {
            throw new InvalidArgumentException(
                "Invalid keys found in leavingReason array. Only 'code' and 'text' keys are allowed."
            );
        }

        $this->data['leavingReason'] = $leavingReason;

        return $this;
    }

    public function getMemberId(): string
    {
        return $this->memberId;
    }

    public function setMemberId(string $memberId): UpdateMemberRequest
    {
        $this->memberId = $memberId;

        return $this;
    }

    public function getConversationId(): string
    {
        return $this->conversationId;
    }

    public function setConversationId(string $conversationId): UpdateMemberRequest
    {
        $this->conversationId = $conversationId;

        return $this;
    }

    public function getState(): string
    {
        return $this->data['state'];
    }

    public function setState(string $state): UpdateMemberRequest
    {
        $this->data['state'] = $state;

        return $this;
    }
}
