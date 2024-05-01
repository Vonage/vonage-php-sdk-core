<?php

declare(strict_types=1);

namespace Vonage\Conversation\ConversationObjects;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class NewConversationRequest implements ArrayHydrateInterface
{
    protected ?int $ttl = null;
    protected ?array $customData = null;

    protected ?ConversationNumber $number = null;
    protected ?ConversationCallback $conversationCallback = null;

    public function __construct(
        protected ?string $name,
        protected ?string $displayName,
        protected ?string $imageUrl
    ) {
    }

    public function getTtl(): ?int
    {
        return $this->ttl;
    }

    public function setTtl(?int $ttl): NewConversationRequest
    {
        $this->ttl = $ttl;

        return $this;
    }

    public function getCustomData(): ?array
    {
        return $this->customData;
    }

    public function setCustomData(?array $customData): NewConversationRequest
    {
        $this->customData = $customData;

        return $this;
    }

    public function getNumber(): ?ConversationNumber
    {
        return $this->number;
    }

    public function setNumber(ConversationNumber $number): NewConversationRequest
    {
        $this->number = $number;

        return $this;
    }

    public function getConversationCallback(): ?ConversationCallback
    {
        return $this->conversationCallback;
    }

    public function setConversationCallback(?ConversationCallback $conversationCallback): NewConversationRequest
    {
        $this->conversationCallback = $conversationCallback;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): NewConversationRequest
    {
        $this->name = $name;

        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): NewConversationRequest
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): NewConversationRequest
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function fromArray(array $data)
    {
        if (isset($data['name'])) {
            $this->setName($data['name']);
        }

        if (isset($data['display_name'])) {
            $this->setDisplayName($data['display_name']);
        }

        if (isset($data['image_url'])) {
            $this->setImageUrl($data['image_url']);
        }

        if (isset($data['properties']['ttl'])) {
            $this->setTtl($data['properties']['ttl']);
        }

        if (isset($data['properties']['custom_data'])) {
            $this->setCustomData($data['properties']['custom_data']);
        }

        if (isset($data['numbers'])) {
            $numbers = new ConversationNumber(null);
            $numbers->fromArray($data['numbers']);
            $this->setNumbers($numbers);
        }

        if (isset($data['callback'])) {
            $callback = new Callback();
            $callback->fromArray($data['callback']);
            $this->setConversationCallback($callback);
        }
    }

    public function toArray(): array
    {
        $returnPayload = [];

        if ($this->getName()) {
            $returnPayload['name'] = $this->getName();
        }

        if ($this->getDisplayName()) {
            $returnPayload['display_name'] = $this->getDisplayName();
        }

        if ($this->getImageUrl()) {
            $returnPayload['image_url'] = $this->getImageUrl();
        }

        if ($this->getTtl()) {
            $returnPayload['properties']['ttl'] = $this->getTtl();
        }

        if ($this->getCustomData()) {
            $returnPayload['properties']['custom_data'] = $this->getCustomData();
        }

        if ($this->getNumber()) {
            $returnPayload['numbers'] = $this->getNumber()->toArray();
        }

        if ($this->getConversationCallback()) {
            $returnPayload['callback'] = $this->getConversationCallback()->toArray();
        }

        return $returnPayload;
    }
}
