<?php

namespace Vonage\Messages\MessageObjects;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class TemplateObject implements ArrayHydrateInterface
{
    private string $name;
    private array $parameters;

    public function __construct(string $name, array $parameters)
    {

        $this->name = $name;
        $this->parameters = $parameters;
    }

    public function fromArray(array $data): TemplateObject
    {
        $this->name = $data['name'];

        if (isset($data['parameters'])) {
            $this->parameters = $data['parameters'];
        }

        return $this;
    }

    public function toArray(): array
    {
        $returnArray = [
            'name' => $this->name
        ];

        if ($this->parameters) {
            $returnArray['parameters'] = $this->parameters;
        }

        return $returnArray;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
