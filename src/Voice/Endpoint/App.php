<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Voice\Endpoint;

class App implements EndpointInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * App constructor.
     *
     * @param string $user
     */
    public function __construct(string $user)
    {
        $this->id = $user;
    }

    /**
     * @param string $user
     * @return App
     */
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

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}
