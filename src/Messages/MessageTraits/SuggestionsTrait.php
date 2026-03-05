<?php

namespace Vonage\Messages\MessageTraits;

use Vonage\Messages\Channel\RCS\Suggestions\RcsSuggestionCollection;

trait SuggestionsTrait
{
    protected ?RcsSuggestionCollection $suggestions = null;

    public function getSuggestions(): RcsSuggestionCollection
    {
        if (!isset($this->suggestions)) {
            $this->suggestions = new RcsSuggestionCollection();
        }

        return $this->suggestions;
    }

    public function setSuggestions(?RcsSuggestionCollection $suggestions): void
    {
        $this->suggestions = $suggestions;
    }
}
