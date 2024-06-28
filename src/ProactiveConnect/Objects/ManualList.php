<?php

namespace Vonage\ProactiveConnect\Objects;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class ManualList extends ListBaseObject implements ArrayHydrateInterface
{
    protected ?string $description = null;
    protected ?array $tags = null;
    protected ?array $attributes = null;
    protected array $datasource = ['type' => 'manual'];

    public function __construct(protected string $name)
    {
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): ManualList
    {
        $this->description = $description;

        return $this;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): ManualList
    {
        $this->tags = $tags;

        return $this;
    }

    public function getAttributes(): ?array
    {
        return $this->attributes;
    }

    public function setAttributes(?array $attributes): ManualList
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function getDatasource(): array
    {
        return $this->datasource;
    }

    public function fromArray(array $data): static
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ManualList
    {
        $this->name = $name;

        return $this;
    }

    public function toArray(): array
    {
        $returnArray = [
            'name' => $this->getName(),
            'datasource' => $this->getDatasource(),
            'description' => $this->getDescription() ?: null,
            'tags' => $this->getTags() ?: null,
            'attributes' => $this->getAttributes() ?: null
        ];

        return array_filter($returnArray, fn($value) => $value !== null);
    }
}
