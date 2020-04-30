<?php
declare(strict_types=1);

namespace Nexmo\Account;

class SecretCollection
{
    /**
     * @var array<string, array>
     */
    protected $data;

    /**
     * @param array<int, Secret> $secrets Secrets composing this collection
     * @param array<string, array> $links External HAL links
     */
    public function __construct(array $secrets, array $links)
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

    /**
     * @return array<int, Secret>
     */
    public function getSecrets() : array
    {
        return $this->data['secrets'];
    }

    /**
     * @return array<string, array>
     */
    public function getLinks() : array
    {
        return $this->data['_links'];
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
