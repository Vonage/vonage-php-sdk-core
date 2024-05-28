<?php

declare(strict_types=1);

namespace Vonage\Conversation\ConversationObjects;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class CreateMemberRequest implements ArrayHydrateInterface
{
    public const MEMBER_STATE_INVITED = 'invited';
    public const MEMBER_STATE_JOINED = 'joined';

    protected ?bool $audioEnabled = null;
    protected ?bool $audioPossible = null;
    protected ?bool $audioEarmuffed = null;
    protected ?bool $audioMuted = null;

    protected array $allowedStates = [self::MEMBER_STATE_INVITED, self::MEMBER_STATE_JOINED];
    protected ?string $knockingId = null;
    protected ?string $memberIdInviting = null;
    protected ?string $from = null;

    public function __construct(
        protected string $state,
        protected Channel $channel,
        protected ?string $userId = null,
        protected ?string $userName = null,
    ) {
        if (is_null($this->userId) && is_null($this->userName)) {
            throw new \InvalidArgumentException('Either a userId or userName must be set');
        }

        if (! in_array($state, $this->allowedStates, true)) {
            throw new \InvalidArgumentException($state . 'is not a valid state');
        }
    }

    public function fromArray(array $data)
    {
        // TODO: Implement fromArray() method.
    }

    public function toArray(): array
    {
        $returnPayload = [];

        $returnPayload['state'] = $this->getState();

        if ($this->getUserId()) {
            $returnPayload['user']['id'] = $this->getUserId();
        }

        if ($this->getUserName()) {
            $returnPayload['user']['name'] = $this->getUserName();
        }

        if ($this->getChannel()) {
            $returnPayload['channel'] = $this->getChannel()->toArray();
        }

        if (!is_null($this->getAudioEnabled())) {
            $returnPayload['media']['audio_settings']['enabled'] = $this->getAudioEnabled();
        }

        if (!is_null($this->getAudioEarmuffed())) {
            $returnPayload['media']['audio_settings']['earmuffed'] = $this->getAudioEarmuffed();
        }

        if (!is_null($this->getAudioMuted())) {
            $returnPayload['media']['audio_settings']['muted'] = $this->getAudioMuted();
        }

        if (!is_null($this->getAudioPossible())) {
            $returnPayload['media']['audio'] = $this->getAudioPossible();
        }

        if ($this->getKnockingId()) {
            $returnPayload['knocking_id'] = $this->getKnockingId();
        }

        if ($this->getMemberIdInviting()) {
            $returnPayload['member_id_inviting'] = $this->getMemberIdInviting();
        }

        if ($this->getFrom()) {
            $returnPayload['from'] = $this->getFrom();
        }

        return $returnPayload;
    }

    public function getChannel(): Channel
    {
        return $this->channel;
    }

    public function setChannel(Channel $channel): CreateMemberRequest
    {
        $this->channel = $channel;

        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): CreateMemberRequest
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(?string $userName): CreateMemberRequest
    {
        $this->userName = $userName;

        return $this;
    }

    public function getKnockingId(): ?string
    {
        return $this->knockingId;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): CreateMemberRequest
    {
        $this->state = $state;

        return $this;
    }

    public function setKnockingId(?string $knockingId): CreateMemberRequest
    {
        $this->knockingId = $knockingId;

        return $this;
    }

    public function getMemberIdInviting(): ?string
    {
        return $this->memberIdInviting;
    }

    public function setMemberIdInviting(?string $memberIdInviting): CreateMemberRequest
    {
        $this->memberIdInviting = $memberIdInviting;

        return $this;
    }

    public function getFrom(): ?string
    {
        return $this->from;
    }

    public function setFrom(?string $from): CreateMemberRequest
    {
        $this->from = $from;

        return $this;
    }

    public function getAudioEnabled(): ?bool
    {
        return $this->audioEnabled;
    }

    public function setAudioEnabled(?bool $audioEnabled): CreateMemberRequest
    {
        $this->audioEnabled = $audioEnabled;

        return $this;
    }

    public function getAudioPossible(): ?bool
    {
        return $this->audioPossible;
    }

    public function setAudioPossible(?bool $audioPossible): CreateMemberRequest
    {
        $this->audioPossible = $audioPossible;

        return $this;
    }

    public function getAudioEarmuffed(): ?bool
    {
        return $this->audioEarmuffed;
    }

    public function setAudioEarmuffed(?bool $audioEarmuffed): CreateMemberRequest
    {
        $this->audioEarmuffed = $audioEarmuffed;

        return $this;
    }

    public function getAudioMuted(): ?bool
    {
        return $this->audioMuted;
    }

    public function setAudioMuted(?bool $audioMuted): CreateMemberRequest
    {
        $this->audioMuted = $audioMuted;

        return $this;
    }
}
