<?php

declare(strict_types=1);

namespace Vonage\Messages\Channel\RCS;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
use OutOfRangeException;
use SplFixedArray;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class RcsCardCollection implements ArrayAccess, Countable, ArrayHydrateInterface
{
    public const MIN_CARDS = 2;
    public const MAX_CARDS = 10;

    protected SplFixedArray $cards;

    public function __construct($cards)
    {
        $this->cards = new SplFixedArray();
        $this->fromArray($cards);
    }

    public function offsetSet(mixed $key, mixed $value): void
    {
        if ($this->cards->count() === self::MAX_CARDS) {
            throw new OutOfRangeException("You cannot send more than " . self::MAX_CARDS . " cards");
        }

        if (!($value instanceof RcsCardObject)) {
            throw new InvalidArgumentException("You can only pass in RcsCardObjects to RcsCardCollection");
        }

        $this->cards->setSize($this->cards->count() + 1);
        $this->cards[$key] = $value;
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->cards->offsetExists($offset);
    }

    public function offsetGet($offset): mixed
    {
        return $this->cards->offsetGet($offset);
    }

    public function offsetUnset($offset): void
    {
        $this->cards->offsetUnset($offset);
    }

    public function count(): int
    {
        return count($this->cards);
    }

    public function toArray(): array
    {
        return array_map(
            function (RcsCardObject $card) {
                return $card->toArray();
            },
            $this->cards->toArray(),
        );
    }

    public function fromArray(array $cards)
    {
        foreach ($cards as $key => $card) {
            if ($card instanceof RcsCardObject) {
                $this->offsetSet($key, $card);
                continue;
            }
            $this->offsetSet($key, new RcsCardObject(...$card));
        }
    }
}
