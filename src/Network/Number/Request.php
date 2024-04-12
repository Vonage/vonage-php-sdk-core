<?php

declare(strict_types=1);

namespace Vonage\Network\Number;

use Vonage\Client\Request\AbstractRequest;
use Vonage\Client\Request\WrapResponseInterface;
use Vonage\Client\Response\Error;
use Vonage\Client\Response\ResponseInterface;

use function implode;

class Request extends AbstractRequest implements WrapResponseInterface
{
    public const FEATURE_TYPE = 'type';
    public const FEATURE_VALID = 'valid';
    public const FEATURE_REACHABLE = 'reachable';
    public const FEATURE_CARRIER = 'carrier';
    public const FEATURE_PORTED = 'ported';
    public const FEATURE_ROAMING = 'roaming';
    public const FEATURE_SUBSCRIBER = 'subscriber';

    /**
     * @var array
     */
    protected $params;

    /**
     * @param $number
     * @param $callback
     */
    public function __construct($number, $callback, array $features = [], $timeout = null, $method = null, $ref = null)
    {
        $this->params['number'] = $number;
        $this->params['callback'] = $callback;
        $this->params['callback_timeout'] = $timeout;
        $this->params['callback_method'] = $method;
        $this->params['client_ref'] = $ref;

        if (!empty($features)) {
            $this->params['features'] = implode(',', $features);
        }
    }

    public function getURI(): string
    {
        return '/ni/json';
    }

    public function wrapResponse(ResponseInterface $response): ResponseInterface
    {
        if ($response->isError()) {
            return new Error($response->getData());
        }

        return new Response($response->getData());
    }
}
