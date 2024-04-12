<?php

declare(strict_types=1);

namespace Vonage\Entity\Filter;

class EmptyFilter implements FilterInterface
{

    public function getQuery(): array
    {
        return [];
    }
}
