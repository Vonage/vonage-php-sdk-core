<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Entity;

use Psr\Http\Message\ResponseInterface;

/**
 * @deprecated This data will be better exposed at the model level
 */
trait JsonResponseTrait
{
    protected $responseJson;

    public function getResponseData()
    {
        if (!($this instanceof EntityInterface)) {
            throw new \Exception(sprintf(
                '%s can only be used if the class implements %s',
                __TRAIT__,
                EntityInterface::class
            ));
        }

        if (($response = @$this->getResponse()) && ($response instanceof ResponseInterface)) {
            if ($response->getBody()->isSeekable()) {
                $response->getBody()->rewind();
            }

            $body = $response->getBody()->getContents();
            $this->responseJson = json_decode($body, true);
            return $this->responseJson;
        }

        return [];
    }
}
