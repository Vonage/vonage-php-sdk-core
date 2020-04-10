<?php

namespace Nexmo\Redact;

use Nexmo\Client\APIExceptionHandler;
use Nexmo\Client\APIResource;
use Nexmo\Client\ClientAwareInterface;
use Nexmo\Client\ClientAwareTrait;

class Client implements ClientAwareInterface
{
    /**
     * @deprecated This object no longer needs to be client aware
     */
    use ClientAwareTrait;

    /**
     * @var APIResource
     */
    protected $api;

    /**
     * @todo Stop having this use its own formatting for exceptions
     */
    public function __construct(APIResource $api)
    {
        $this->api = $api;

        // This API has been using a different exception response format, so reset it if we can
        $exceptionHandler = $this->api->getExceptionErrorHandler();
        if ($exceptionHandler instanceof APIExceptionHandler) {
            $exceptionHandler->setRfc7807Format("%s - %s. See %s for more information");
        }
        $this->api->setExceptionErrorHandler($exceptionHandler);
    }

    public function transaction(string $id, string $product, array $options = []) : void
    {
        $api = clone $this->api;
        $api->setBaseUri('/v1/redact/transaction');

        $body = ['id' => $id, 'product' => $product] + $options;
        $api->create($body);
    }
}
