<?php

namespace Nexmo\Redact;

use Nexmo\Client\APIClient;
use Nexmo\Client\APIResource;

class Client implements APIClient
{
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
    }

    public function getAPIResource(): APIResource
    {
        return $this->api;
    }

    public function transaction(string $id, string $product, array $options = []) : void
    {
        $body = ['id' => $id, 'product' => $product] + $options;
        $this->api->create($body);
    }
}
