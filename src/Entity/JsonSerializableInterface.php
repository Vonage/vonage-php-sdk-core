<?php

declare(strict_types=1);

namespace Vonage\Entity;

use JsonSerializable;

/**
 * Identifies the Entity as using JsonSerializable to prepare request data.
 */
interface JsonSerializableInterface extends JsonSerializable
{
}
