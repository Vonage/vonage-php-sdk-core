<?php

declare(strict_types=1);

namespace Vonage\Redact;

use Psr\Http\Client\ClientExceptionInterface;
use Vonage\Client\APIExceptionHandler;
use Vonage\Client\APIResource;
use Vonage\Client\ClientAwareTrait;
use Vonage\Client\Exception\Exception as ClientException;

class Client
{
    /**
     * @todo Stop having this use its own formatting for exceptions
     */
    public function __construct(protected APIResource $api)
    {
    }

    private function resolveApi(): APIResource
    {
        // This API has been using a different exception response format, so reset it if we can
        // @todo Move this somewhere more appropriate, current has to be here,
        // because we can't otherwise guarantee there is an API object
        $exceptionHandler = $this->api->getExceptionErrorHandler();

        if ($exceptionHandler instanceof APIExceptionHandler) {
            $exceptionHandler->setRfc7807Format("%s - %s. See %s for more information");
        }

        $this->api->setExceptionErrorHandler($exceptionHandler);

        return $this->api;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException
     */
    public function transaction(string $id, string $product, array $options = []): void
    {
        $api = $this->resolveApi();
        $api->setBaseUri('/v1/redact/transaction');

        $body = ['id' => $id, 'product' => $product] + $options;
        $api->create($body);
    }
}
