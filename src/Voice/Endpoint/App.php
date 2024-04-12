<?php

declare(strict_types=1);

namespace Vonage\Voice\Endpoint;

class App implements EndpointInterface
{
    public function __construct(protected string $id)
    {
    }

    public static function factory(string $user): App
    {
        return new App($user);
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
            'type' => 'app',
            'user' => $this->id,
        ];
    }

    public function getId(): string
    {
        return $this->id;
    }
}
