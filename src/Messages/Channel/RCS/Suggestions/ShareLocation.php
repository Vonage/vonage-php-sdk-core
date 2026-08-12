<?php

declare(strict_types=1);

namespace Vonage\Messages\Channel\RCS\Suggestions;

class ShareLocation extends Suggestion
{
    public function getType(): string
    {
        return Suggestion::SUGGESTION_TYPE_SHARE_LOCATION;
    }
}
