<?php

namespace Vonage\Account;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class SecretCollection implements \ArrayAccess
{
    protected $data;

    public function __construct($secrets, $links)
    {
        $this->data = [
            'secrets' => $secrets,
            '_links' => $links
        ];

        foreach ($this->data['secrets'] as $key => $secret) {
            if (!$secret instanceof Secret) {
                $this->data['secrets'][$key] = new Secret($secret);
            }
        }
    }

    public function getSecrets()
    {
        return $this->data['secrets'];
    }

    public function getLinks()
    {
        return $this->data['_links'];
    }

    /**
     * @deprecated Instatiate the object directly
     */
    public static function fromApi($data)
    {
        trigger_error('Please instatiate a Vonage\Account\SecretCollection instead of using fromApi()', E_USER_DEPRECATED);
        $secrets = [];
        foreach ($data['_embedded']['secrets'] as $s) {
            $secrets[] = Secret::fromApi($s);
        }
        return new self($secrets, $data['_links']);
    }

    public function offsetExists($offset)
    {
        trigger_error(
            "Array access for " . get_class($this) . " is deprecated, please use getter methods",
            E_USER_DEPRECATED
        );
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        trigger_error(
            "Array access for " . get_class($this) . " is deprecated, please use getter methods",
            E_USER_DEPRECATED
        );
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new \Exception('SecretCollection::offsetSet is not implemented');
    }

    public function offsetUnset($offset)
    {
        throw new \Exception('SecretCollection::offsetUnset is not implemented');
    }
}
