<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Client\Exception;

use Vonage\Entity\HasEntityTrait;
use Vonage\Entity\Psr7Trait;

class Server extends Exception
{
    use HasEntityTrait;
    use Psr7Trait;
}
