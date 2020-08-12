<?php
declare(strict_types=1);

namespace Vonage\Voice\Endpoint;

class App implements EndpointInterface
{
    /**
     * @var string
     */
    protected $id;

    public function __construct(string $user)
    {
        $this->id = $user;
    }

    public static function factory(string $user) : App
    {
        return new App($user);
    }

    /**
     * @return array{type: string, user: string}
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return array{type: string, user: string}
     */
    public function toArray() : array
    {
        return [
            'type' => 'app',
            'user' => $this->id,
        ];
    }

    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }
}
