<?php

declare(strict_types=1);

namespace Vonage\Voice\NCCO;

use JsonSerializable;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Voice\NCCO\Action\ActionInterface;

class NCCO implements ArrayHydrateInterface, JsonSerializable
{
    /**
     * @var array<ActionInterface>
     */
    protected array $actions = [];

    public function addAction(ActionInterface $action): self
    {
        $this->actions[] = $action;
        return $this;
    }

    public function fromArray(array $data): void
    {
        $factory = new NCCOFactory();

        foreach ($data as $rawNCCO) {
            $action = $factory->build($rawNCCO);
            $this->addAction($action);
        }
    }

    /**
     * @return array<array<string, string>>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return array<array<string, string>>
     */
    public function toArray(): array
    {
        $data = [];

        foreach ($this->actions as $action) {
            $data[] = $action->toNCCOArray();
        }

        return $data;
    }
}
