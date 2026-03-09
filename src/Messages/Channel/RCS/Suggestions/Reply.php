<?php

namespace Vonage\Messages\Channel\RCS\Suggestions;

class Reply extends Suggestion
{
    public function getType(): string
    {
        return Suggestion::SUGGESTION_TYPE_REPLY;
    }
}
