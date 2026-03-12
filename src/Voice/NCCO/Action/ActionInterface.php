<?php

declare(strict_types=1);

namespace Vonage\Voice\NCCO\Action;

use JsonSerializable;

interface ActionInterface
{
    /**
     * @return array<string, string>
     */
    public function toNCCOArray(): array;
}
