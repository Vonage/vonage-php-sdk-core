<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Verify;

use Nexmo\Client\APIClient;
use Nexmo\Client\APIResource;
use Nexmo\Client\ClientAwareInterface;
use Nexmo\Client\ClientAwareTrait;

class Client implements APIClient
{
    /**
     * @var APIResource
     */
    protected $api;

    public function __construct(APIResource $api)
    {
        $this->api = $api;
    }

    public function getAPIResource(): APIResource
    {
        return $this->api;
    }

    public function start(Request $verification) : StartResponse
    {
        $response = $this->api->create($verification->toArray(), '/json');

        return new StartResponse($response);
    }

    public function search(string $requestId) : Verification
    {
        $params = [
            'request_id' => $requestId
        ];

        $data = $this->api->create($params, '/search/json');

        return new Verification($data);
    }

    public function cancel(string $requestId)
    {
        return $this->control($requestId, 'cancel');
    }

    public function trigger(string $requestId)
    {
        return $this->control($requestId, 'trigger_next_event');
    }

    public function check(string $requestId, string $code, string $ip = null) : CheckConfirmation
    {
        $params = [
            'request_id' => $requestId,
            'code' => $code
        ];

        if (!is_null($ip)) {
            $params['ip'] = $ip;
        }

        $data = $this->api->create($params, '/check/json');

        return new CheckConfirmation($data);
    }

    protected function control(string $requestId, string $cmd) : ControlResponse
    {
        $params = [
            'request_id' => $requestId,
            'cmd' => $cmd
        ];

        $data = $this->api->create($params, '/control/json');

        return new ControlResponse($data);
    }
}
