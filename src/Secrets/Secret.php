<?php

namespace Vonage\Secrets;

use DateTimeImmutable;
use DateTimeInterface;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class Secret implements ArrayHydrateInterface
{
    protected DateTimeImmutable $createdAt;

    protected string $id;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->fromArray($data);
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function fromArray(array $data)
    {
        $this->id = $data['id'];
        $this->createdAt = new DateTimeImmutable($data['created_at']);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'created_at' => $this->createdAt->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
