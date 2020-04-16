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
    public function __construct(APIResource $api = null)
    {
        $this->api = $api;
    }

    /**
     * Shim to handle older instatiations of this class
     * @deprecated Will remove in v3
     */
    protected function getApiResource() : APIResource
    {
        if (is_null($this->api)) {
            $api = new APIResource();
            $api->setClient($this->getClient())
                ->setBaseUri('/v1/redact')
                ->setCollectionName('')
            ;
            $this->api = $api;

            // This API has been using a different exception response format, so reset it if we can
            // @todo Move this somewhere more appropriate, has to be here because we can't otherwise guarantee there is an API object
            $exceptionHandler = $this->api->getExceptionErrorHandler();
            if ($exceptionHandler instanceof APIExceptionHandler) {
                $exceptionHandler->setRfc7807Format("%s - %s. See %s for more information");
            }
            $this->api->setExceptionErrorHandler($exceptionHandler);
        }
        return clone $this->api;
    }

    public function transaction(string $id, string $product, array $options = []) : void
    {
        $api = $this->getApiResource();
        $api->setBaseUri('/v1/redact/transaction');

        $body = ['id' => $id, 'product' => $product] + $options;
        $api->create($body);
    }
}
