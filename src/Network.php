<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage;

use JsonSerializable;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Entity\EntityInterface;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Entity\JsonResponseTrait;
use Vonage\Entity\JsonSerializableInterface;
use Vonage\Entity\JsonSerializableTrait;
use Vonage\Entity\NoRequestResponseTrait;

use function ltrim;
use function preg_replace;
use function strtolower;

/**
 * This class will no longer be accessible via array access, nor contain request/response information after v2.
 */
class Network implements
    EntityInterface,
    JsonSerializable,
    JsonSerializableInterface,
    ArrayHydrateInterface
{
    use JsonSerializableTrait;
    use NoRequestResponseTrait;
    use JsonResponseTrait;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param string|int $networkCode
     * @param string|int $networkName
     */
    public function __construct($networkCode, $networkName)
    {
        $this->data['network_code'] = (string)$networkCode;
        $this->data['network_name'] = (string)$networkName;
    }

    public function getCode(): string
    {
        return $this->data['network_code'];
    }

    public function getName(): string
    {
        return $this->data['network_name'];
    }

    public function getOutboundSmsPrice()
    {
        return $this->data['sms_price'] ?? $this->data['price'];
    }

    public function getOutboundVoicePrice()
    {
        return $this->data['voice_price'] ?? $this->data['price'];
    }

    public function getPrefixPrice()
    {
        return $this->data['mt_price'];
    }

    public function getCurrency()
    {
        return $this->data['currency'];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function fromArray(array $data): void
    {
        // Convert CamelCase to snake_case as that's how we use array access in every other object
        $storage = [];

        foreach ($data as $k => $v) {
            $k = strtolower(ltrim(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $k), '_'));
            $storage[$k] = $v;
        }

        $this->data = $storage;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
