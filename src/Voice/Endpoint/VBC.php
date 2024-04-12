<?php

declare(strict_types=1);

namespace Vonage\Voice\Endpoint;

class VBC implements EndpointInterface
{
    public function __construct(protected string $id)
    {
    }

    public static function factory(string $extension): VBC
    {
        return new VBC($extension);
    }

    /**
     * @return array{type: string, user: string}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return array{type: string, user: string}
     */
    public function toArray(): array
    {
        return [
            'type' => 'vbc',
            'extension' => $this->id,
        ];
    }

    public function getId(): string
    {
        return $this->id;
    }
}
