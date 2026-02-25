<?php

namespace Vonage\Messages\Channel\RCS\Suggestions;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
use SplFixedArray;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class RcsSuggestionCollection implements ArrayAccess, Countable, ArrayHydrateInterface
{
    protected SplFixedArray $suggestions;

    public function __construct($suggestions = null)
    {
        $this->suggestions = new SplFixedArray();
        if (isset($suggestions)) {
            $this->fromArray($suggestions);
        }
    }

    public function offsetSet(mixed $key, mixed $value): void
    {
        if (!($value instanceof Suggestion)) {
            throw new InvalidArgumentException("You can only pass in Suggestion to RcsSuggestionCollection");
        }

        $this->suggestions->setSize($this->suggestions->count() + 1);
        $this->suggestions[$key ?? ($this->suggestions->getSize() - 1)] = $value;
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->suggestions->offsetExists($offset);
    }

    public function offsetGet($offset): mixed
    {
        return $this->suggestions->offsetGet($offset);
    }

    public function offsetUnset($offset): void
    {
        $this->suggestions->offsetUnset($offset);
    }

    public function count(): int
    {
        return count($this->suggestions);
    }

    public function toArray(): array
    {
        return array_map(
            function (Suggestion $suggestion) {
                return $suggestion->toArray();
            },
            $this->suggestions->toArray(),
        );
    }

    public function jsonSerialize(): mixed
    {
        return $this->suggestions->toArray();
    }

    public function fromArray(array $suggestions)
    {
        foreach ($suggestions as $key => $suggestion) {
            if ($suggestion instanceof Suggestion) {
                $this->offsetSet($key, $suggestion);
                continue;
            }

            $this->offsetSet(
                $key,
                match ($suggestion['type']) {
                    Suggestion::SUGGESTION_TYPE_REPLY => new Reply(...$suggestion),
                    Suggestion::SUGGESTION_TYPE_DIAL => new Dial(...$suggestion),
                    Suggestion::SUGGESTION_TYPE_VIEW_LOCATION => new ViewLocation(...$suggestion),
                    Suggestion::SUGGESTION_TYPE_SHARE_LOCATION => new ShareLocation(...$suggestion),
                    Suggestion::SUGGESTION_TYPE_OPEN_URL => new OpenUrl(...$suggestion),
                    Suggestion::SUGGESTION_TYPE_OPEN_URL_WEBVIEW => new OpenUrlWebView(...$suggestion),
                    Suggestion::SUGGESTION_TYPE_CREATE_CALENDAR_EVENT => new CreateCalendarEvent(...$suggestion),
                },
            );
        }
    }
}
