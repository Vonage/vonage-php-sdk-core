<?php

namespace Vonage\ProactiveConnect\Objects;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class SalesforceList extends ListBaseObject implements ArrayHydrateInterface
{
    protected ?string $description = null;
    protected ?array $tags = null;
    protected ?array $attributes = null;
    protected array $datasource = [
        'type' => 'salesforce',
        'integration_id' => '',
        'soql' => ''
    ];

    public function __construct(protected string $name)
    {
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): SalesforceList
    {
        $this->description = $description;

        return $this;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): SalesforceList
    {
        $this->tags = $tags;

        return $this;
    }

    public function getAttributes(): ?array
    {
        return $this->attributes;
    }

    public function setAttributes(?array $attributes): SalesforceList
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

    public function setName(string $name): SalesforceList
    {
        $this->name = $name;

        return $this;
    }

    public function setSalesforceIntegrationId(string $integrationId): SalesforceList
    {
        $this->datasource['integration_id'] = $integrationId;

        return $this;
    }

    public function setSalesforceSoql(string $query): SalesforceList
    {
        $this->datasource['soql'] = $query;

        return $this;
    }

    public function toArray(): array
    {
        if (empty($this->getDatasource()['integration_id'])) {
            throw new \InvalidArgumentException('integration_id needs to be set on datasource on a Salesforce list');
        }

        if (empty($this->getDatasource()['soql'])) {
            throw new \InvalidArgumentException('soql needs to be set on datasource on a Salesforce list');
        }

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
