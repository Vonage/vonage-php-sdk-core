<?php
declare(strict_types=1);
namespace Nexmo\Account;

use Nexmo\Entity\Hydrator\ArrayHydrateInterface;

/**
 * This class will no longer be accessible via array keys past v2
 * @todo Have the JSON unserialize/serialize keys match with $this->data keys
 */
class Balance implements \JsonSerializable, ArrayHydrateInterface
{
    /**
     * @var bool
     */
    protected $autoReload;

    /**
     * @var float
     */
    protected $balance;

    public function __construct(float $balance, bool $autoReload)
    {
        $this->balance = $balance;
        $this->autoReload = $autoReload;
    }

    public function getBalance() : float
    {
        return $this->balance;
    }

    public function getAutoReload() : bool
    {
        return $this->autoReload;
    }

    /**
     * @return array<string, float|bool>
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @param array<string, float|bool> $data Data about the account balance
     */
    public function fromArray(array $data) : void
    {
        $this->balance = (float) $data['value'] ?? null;
        $this->autoReload = $data['autoReload'] ?? null;
    }

    /**
     * @return array<string, float|bool>
     */
    public function toArray(): array
    {
        return [
            'balance' => $this->getBalance(),
            'autoReload' => $this->getAutoReload(),
        ];
    }
}
