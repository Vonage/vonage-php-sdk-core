<?php

declare(strict_types=1);

namespace Vonage\Application;

use Vonage\Entity\EntityInterface;

interface ApplicationInterface extends EntityInterface
{
    public function getId(): ?string;
}
