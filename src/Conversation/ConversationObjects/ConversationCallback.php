<?php

declare(strict_types=1);

namespace Vonage\Conversation\ConversationObjects;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class ConversationCallback implements ArrayHydrateInterface
{
    protected string $method = 'POST';
    protected ?array $params = null;

    public function __construct(
        protected ?string $url = null,
        protected ?string $eventMask = null
    ) {
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): ConversationCallback
    {
        $this->method = $method;

        return $this;
    }

    public function getParams(): ?array
    {
        return $this->params;
    }

    public function setParams(?array $params): ConversationCallback
    {
        $this->params = $params;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): ConversationCallback
    {
        $this->url = $url;

        return $this;
    }

    public function getEventMask(): ?string
    {
        return $this->eventMask;
    }

    public function setEventMask(?string $eventMask): ConversationCallback
    {
        $this->eventMask = $eventMask;

        return $this;
    }

    public function setApplicationId(string $applicationId): static
    {
        $this->params['applicationId'] = $applicationId;

        return $this;
    }

    public function setNccoUrl(string $nccoUrl): static
    {
        $this->params['ncco_url'] = $nccoUrl;

        return $this;
    }

    public function fromArray(array $data): static
    {
        if (isset($data['url'])) {
            $this->setUrl($data['url']);
        }

        if (isset($data['event_mask'])) {
            $this->setEventMask($data['event_mask']);
        }

        if (isset($data['params'])) {
            $this->setParams($data['params']);
        }

        return $this;
    }

    public function toArray(): array
    {
        $returnPayload = [];

        if ($this->getUrl()) {
            $returnPayload['url'] = $this->getUrl();
        }

        if ($this->getEventMask()) {
            $returnPayload['event_mask'] = $this->getEventMask();
        }

        if ($this->getParams()) {
            $returnPayload['params'] = $this->getParams();
        }

        $returnPayload['method'] = $this->getMethod();

        return $returnPayload;
    }
}
