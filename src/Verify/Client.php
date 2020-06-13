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
use Nexmo\Client\Exception;

class Client implements ClientAwareInterface, APIClient
{
    use ClientAwareTrait;

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

        $this->checkError($response);
        return new StartResponse($response);
    }

    public function search(string $requestId) : Verification
    {
        $params = [
            'request_id' => $requestId
        ];

        $data = $this->api->create($params, '/search/json');

        $this->checkError($data);
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

        $data = $this->checkError($data);
        return new CheckConfirmation($data);
    }

    protected function control(string $requestId, string $cmd) : ControlResponse
    {
        $params = [
            'request_id' => $requestId,
            'cmd' => $cmd
        ];

        $data = $this->api->create($params, '/control/json');
        $this->checkError($data);

        return new ControlResponse($data);
    }

    protected function checkError(array $data)
    {
        if (!isset($data['status'])) {
            $e = new Exception\Request('unexpected response from API');
            $e->setEntity($data);
            throw $e;
        }

        //normalize errors (client vrs server)
        switch ($data['status']) {
            // These exist because `status` is valid in both the error
            // response and a success response, but serve different purposes
            // in each case
            case 'IN PROGRESS':
            case 'SUCCESS':
            case 'FAILED':
            case 'EXPIRED':
            case 'CANCELLED':
            case '0':
                return $data;
            case '5':
                $e = new Exception\Server($data['error_text'], $data['status']);
                $e->setEntity($data);
                break;
            default:
                $e = new Exception\Request($data['error_text'], $data['status']);
                $e->setEntity($data);
                break;
        }

        $e->setEntity($data);
        throw $e;
    }
}
