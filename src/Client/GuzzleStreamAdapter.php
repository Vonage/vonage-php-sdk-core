<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Client;

use Http\Adapter\Guzzle6\Client;
use Nexmo\StreamingInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleStreamAdapter extends Client implements StreamingInterface
{
    /**
     * @inheritdoc
     */
    public function stream(ResponseInterface $response)
    {
        $body = $response->getBody();

        if (!method_exists($body, 'detach')) {
            throw new \RuntimeException('Not a streamable response');
        }

        return $body->detach();
    }
}
