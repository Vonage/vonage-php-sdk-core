<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Entity;

use Exception;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

use function json_decode;
use function sprintf;

/**
 * @deprecated This data will be better exposed at the model level
 */
trait JsonResponseTrait
{
    protected $responseJson;

    /**
     * @throws Exception
     *
     * @return array|mixed
     */
    public function getResponseData()
    {
        if (!($this instanceof EntityInterface)) {
            throw new RuntimeException(
                sprintf(
                    '%s can only be used if the class implements %s',
                    __TRAIT__,
                    EntityInterface::class
                )
            );
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
