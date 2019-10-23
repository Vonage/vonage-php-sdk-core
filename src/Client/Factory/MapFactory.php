<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Client\Factory;

use Nexmo\Client;

class MapFactory implements FactoryInterface
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
     * Nexmo Client
     *
     * @var Client
     */
    protected $client;

    public function __construct($map, Client $client)
    {
        $this->map = $map;
        $this->client = $client;
    }

    public function has(string $key)
    {
        return isset($this->map[$key]);
    }

    /**
     * @deprecated Use has() instead
     */
    public function hasApi($api)
    {
        return $this->has($api);
    }

    public function get(string $key)
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        if (!$this->hasApi($key)) {
            throw new \RuntimeException(sprintf(
                'no map defined for `%s`',
                $key
            ));
        }

        if (is_callable($this->map[$key])) {
            $instance = $this->map[$key]($this);
        } else {
            $class = $this->map[$key];
            $instance = new $class();
        }

        if ($instance instanceof Client\ClientAwareInterface) {
            $instance->setClient($this->client);
        }
        $this->cache[$key] = $instance;
        return $instance;
    }

    /**
     * @deprecated Use get() instead
     */
    public function getApi($api)
    {
        return $this->get($api);
    }
}
