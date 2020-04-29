<?php

namespace Nexmo\Account;

use Nexmo\Entity\Hydrator\ArrayHydrateInterface;

class SecretCollection implements \ArrayAccess
{
    protected $data;

    public function __construct($secrets, $links)
    {
        $this->data = [
            'secrets' => $secrets,
            '_links' => $links
        ];
    }

    public function getSecrets()
    {
        return $this['secrets'];
    }

    public function getLinks()
    {
        return $this['_links'];
    }

    /**
     * @deprecated Instatiate the object directly
     */
    public static function fromApi($data)
    {
        trigger_error('Please instatiate a Nexmo\Account\SecretCollection instead of using fromApi()', E_USER_DEPRECATED);
        $secrets = [];
        foreach ($data['_embedded']['secrets'] as $s) {
            $secrets[] = Secret::fromApi($s);
        }
        return new self($secrets, $data['_links']);
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
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
