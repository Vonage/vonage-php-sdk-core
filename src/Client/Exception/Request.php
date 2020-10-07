<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Client\Exception;

use Vonage\Entity\HasEntityTrait;
use Vonage\Entity\Psr7Trait;

class Request extends Exception
{
    use HasEntityTrait;
    use Psr7Trait;
}
