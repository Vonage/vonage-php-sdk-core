<?php

declare(strict_types=1);

namespace Vonage\Application;

use Vonage\Entity\EntityInterface;

/**
 * @deprecated This interface will be removed in the next major version.
 */
interface ApplicationInterface extends EntityInterface
{
    public function getId(): ?string;
}
