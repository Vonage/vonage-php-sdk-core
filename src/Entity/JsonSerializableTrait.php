<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Entity;

use JsonSerializable;
use Vonage\Client\Exception\Exception as ClientException;

use function sprintf;

/**
 * Implements getRequestData from EntityInterface based on the entity's jsonSerialize().
 *
 * @see EntityInterface::getRequestData()
 * @deprecated Each model will handle serializing to/from JSON via hydrators
 */
trait JsonSerializableTrait
{
    /**
     * Get an array of params to use in an API request.
     *
     * @param bool $sent
     *
     * @throws ClientException
     */
    public function getRequestData($sent = true)
    {
        if (!($this instanceof EntityInterface)) {
            throw new ClientException(
                sprintf(
                    '%s can only be used if the class implements %s',
                    __TRAIT__,
                    EntityInterface::class
                )
            );
        }

        if (!($this instanceof JsonSerializable)) {
            throw new ClientException(
                sprintf(
                    '%s can only be used if the class implements %s',
                    __TRAIT__,
                    JsonSerializable::class
                )
            );
        }

        //TODO, figure out what the request data actually was
        $sent && $this->getRequest();

        return $this->jsonSerialize();
    }
}
