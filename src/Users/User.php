<?php

namespace Vonage\Users;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class User implements ArrayHydrateInterface
{
    protected ?string $id = null;
    protected ?string $name = null;
    protected ?string $displayName = null;
    protected ?string $imageUrl = null;
    protected ?array $properties = null;
    protected ?array $channels = null;
    protected ?string $selfLink = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): static
    {
        $this->displayName = $displayName;
        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    public function getProperties(): ?array
    {
        return $this->properties;
    }

    public function setProperties(array $properties): static
    {
        $this->properties = $properties;

        return $this;
    }

    public function getChannels(): array
    {
        return $this->channels;
    }

    public function setChannels(array $channels): static
    {
        $this->channels = $channels;
        return $this;
    }

    public function getSelfLink(): ?string
    {
        return $this->selfLink;
    }

    public function setSelfLink(string $selfLink): static
    {
        $this->selfLink = $selfLink;

        return $this;
    }

    public function fromArray(array $data): static
    {
        if (isset($data['id'])) {
            $this->setId($data['id']);
        }

        if (isset($data['name'])) {
            $this->setName($data['name']);
        }

        if (isset($data['display_name'])) {
            $this->setDisplayName($data['display_name']);
        }

        if (isset($data['image_url'])) {
            $this->setImageUrl($data['image_url']);
        }

        if (isset($data['properties'])) {
            $this->setProperties($data['properties']);
        }

        if (isset($data['channels'])) {
            $this->setChannels($data['channels']);
        }

        if (isset($data['_links']['self']['href'])) {
            $this->setSelfLink($data['_links']['self']['href']);
        }

        return $this;
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->id !== null) {
            $data['id'] = $this->getId();
        }

        if ($this->name !== null) {
            $data['name'] = $this->getName();
        }

        if ($this->displayName !== null) {
            $data['display_name'] = $this->getDisplayName();
        }

        if ($this->imageUrl !== null) {
            $data['image_url'] = $this->getImageUrl();
        }

        if ($this->properties !== null) {
            $data['properties'] = $this->getProperties();
        }

        if ($this->channels !== null) {
            $data['channels'] = $this->getChannels();
        }

        if ($this->selfLink !== null) {
            $data['_links']['self']['href'] = $this->getSelfLink();
        }

        return $data;
    }
}
