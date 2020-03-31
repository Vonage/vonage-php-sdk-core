<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo;

use Psr\Http\Message\ResponseInterface;

interface StreamingInterface
{
    /**
     * Takes a streamable response and returns a resource
     *
     * @return resource A streamable response resource
     */
    public function stream(ResponseInterface $response);
}
