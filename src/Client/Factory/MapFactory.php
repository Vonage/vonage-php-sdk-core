<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Client\Factory;

use Psr\Container\ContainerInterface;
use RuntimeException;
use Vonage\Client;

use function is_callable;
use function sprintf;

class MapFactory implements FactoryInterface, ContainerInterface
{
    /**
     * Map of api namespaces to classes.
     *
     * @var array
     */
    protected $map = [];

    /**
     * Map of instances.
     *
     * @var array
     */
    protected $cache = [];

    /**
     * Vonage Client
     *
     * @var Client
     */
    protected $client;

    public function __construct($map, Client $client)
    {
        $this->map = $map;
        $this->client = $client;
    }

    /**
     * @param string $id
     *
     * @noinspection PhpMissingParamTypeInspection
     */
    public function has($id): bool
    {
        return isset($this->map[$id]);
    }

    /**
     * @deprecated Use has() instead
     */
    public function hasApi(string $api): bool
    {
        return $this->has($api);
    }

    /**
     * @param string $id
     *
     * @noinspection PhpMissingParamTypeInspection
     */
    public function get($id)
    {
        if (isset($this->cache[$id])) {
            return $this->cache[$id];
        }

        $instance = $this->make($id);
        $this->cache[$id] = $instance;

        return $instance;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @deprecated Use get() instead
     */
    public function getApi(string $api)
    {
        return $this->get($api);
    }

    public function make($key)
    {
        if (!$this->has($key)) {
            throw new RuntimeException(
                sprintf(
                    'no map defined for `%s`',
                    $key
                )
            );
        }

        if (is_callable($this->map[$key])) {
            $instance = $this->map[$key]($this);
        } else {
            $class = $this->map[$key];
            $instance = new $class();
            if (is_callable($instance)) {
                $instance = $instance($this);
            }
        }

        if ($instance instanceof Client\ClientAwareInterface) {
            $instance->setClient($this->client);
        }

        return $instance;
    }

    public function set($key, $value): void
    {
        $this->map[$key] = $value;
    }
}
