<?php

declare(strict_types=1);

namespace Vonage\Client;

use RuntimeException;
use Vonage\Client;

trait ScopeAwareTrait
{
    protected ?string $scope = null;

    public function setScope(string $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }
}
