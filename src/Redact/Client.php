<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Redact;

use Psr\Http\Client\ClientExceptionInterface;
use Vonage\Client\APIClient;
use Vonage\Client\APIExceptionHandler;
use Vonage\Client\APIResource;
use Vonage\Client\ClientAwareInterface;
use Vonage\Client\ClientAwareTrait;
use Vonage\Client\Exception\Exception as ClientException;

use function is_null;

class Client implements ClientAwareInterface, APIClient
{
    /**
     * @deprecated This object no longer needs to be client aware
     */
    use ClientAwareTrait;

    /**
     * @todo Stop having this use its own formatting for exceptions
     */
    public function __construct(protected ?APIResource $api = null)
    {
    }

    /**
     * Shim to handle older instantiations of this class
     * Will change in v3 to just return the required API object
     */
    public function getApiResource(): APIResource
    {
        if (is_null($this->api)) {
            $api = new APIResource();
            $api->setClient($this->getClient())
                ->setBaseUri('/v1/redact')
                ->setCollectionName('');
            $this->api = $api;

            // This API has been using a different exception response format, so reset it if we can
            // @todo Move this somewhere more appropriate, current has to be here,
            // because we can't otherwise guarantee there is an API object
            $exceptionHandler = $this->api->getExceptionErrorHandler();

            if ($exceptionHandler instanceof APIExceptionHandler) {
                $exceptionHandler->setRfc7807Format("%s - %s. See %s for more information");
            }

            $this->api->setExceptionErrorHandler($exceptionHandler);
        }
        return $this->api;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException
     */
    public function transaction(string $id, string $product, array $options = []): void
    {
        $api = $this->getApiResource();
        $api->setBaseUri('/v1/redact/transaction');

        $body = ['id' => $id, 'product' => $product] + $options;
        $api->create($body);
    }
}
