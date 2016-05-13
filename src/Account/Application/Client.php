<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Account\Application;


use Nexmo\Client\ClientAwareInterface;
use Nexmo\Client\ClientAwareTrait;

class Client implements ClientAwareInterface
{
    use ClientAwareTrait;
}