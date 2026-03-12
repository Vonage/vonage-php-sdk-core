<?php

namespace Vonage\Voice\NCCO\Action;

class Transfer implements ActionInterface
{
    public function __construct(
        protected string $conversationId,
        protected ?array $canHear = null,
        protected ?array $canSpeak = null,
        protected ?bool $mute = null,
    ) {}

    /**
     * @param array<array, mixed> $data
     */
    public static function factory(array $data): Transfer
    {
        $transfer = new Transfer($data['conversation_id']);

        if (isset($data['canHear'])) {
            $transfer->setCanHear($data['canHear']);
        }

        if (isset($data['canSpeak'])) {
            $transfer->setCanSpeak($data['canSpeak']);
        }

        if (isset($data['mute'])) {
            $transfer->setMute($data['mute']);
        }

        return $transfer;
    }

    /**
     * @return array<string, mixed>
     */
    public function toNCCOArray(): array
    {
        $returnArray = [
            'action' => 'transfer',
            'conversation_id' => $this->getConversationId(),
        ];

        if (null !== $this->getCanHear()) {
            $returnArray['canHear'] = $this->getCanHear();
        }

        if (null !== $this->getCanSpeak()) {
            $returnArray['canSpeak'] = $this->getCanSpeak();
        }

        if (null !== $this->getMute()) {
            $returnArray['mute'] = $this->getMute();
        }

        return $returnArray;
    }

    public function setConversationId(string $conversationId): void
    {
        $this->conversationId = $conversationId;
    }

    public function getConversationId(): string
    {
        return $this->conversationId;
    }

    public function setCanHear(?array $canHear): void
    {
        $this->canHear = $canHear;
    }

    public function getCanHear(): ?array
    {
        return $this->canHear;
    }

    public function setCanSpeak(?array $canSpeak): void
    {
        $this->canSpeak = $canSpeak;
    }

    public function getCanSpeak(): ?array
    {
        return $this->canSpeak;
    }

    public function setMute(?bool $mute): void
    {
        $this->mute = $mute;
    }

    public function getMute(): ?bool
    {
        return $this->mute;
    }
}
