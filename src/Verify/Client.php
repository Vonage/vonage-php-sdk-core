<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Verify;

use Nexmo\Client\ClientAwareTrait;
use Nexmo\Client\Exception;
use Zend\Diactoros\Request;

class Client
{
    use ClientAwareTrait;

    public function start($verification)
    {
        if(!($verification instanceof Verification)){
            $verification = $this->createVerificationFromArray($verification);
        }

        $params = $verification->getRequestData(false);

        $request = new Request(
            \Nexmo\Client::BASE_API . '/verify/json?' . http_build_query($params)
            ,'POST'
        );

        $verification->setRequest($request);
        $response = $this->client->send($request);
        $verification->setResponse($response);

        //check for valid data, as well as an error response from the API
        $data = $verification->getResponseData();
        if(!isset($data['status'])){
            throw new Exception\Exception('unexpected response from API');
        }

        //normalize errors (client vrs server)
        switch($data['status']){
            case '0':
                return $verification;
            case '5':
                $e = new Exception\Server($data['error_text'], $data['status']);
                break;
            default:
                $e = new Exception\Request($data['error_text'], $data['status']);
                break;
        }

        $e->setEntity($verification);
        throw $e;
    }

    public function search($verification)
    {
        if(!($verification instanceof Verification)){
            $verification = new Verification($verification);
        }

        $request = new Request(
            \Nexmo\Client::BASE_API . '/verify/search/json?' . http_build_query([
                'request_id' => $verification->getRequestId()
            ]),
            'POST'
        );

        $verification->setRequest($request);
        $response = $this->client->send($request);
        $verification->setResponse($response);

        //check for valid data, as well as an error response from the API
        $data = $verification->getResponseData();
        if(!isset($data['status'])){
            throw new Exception\Exception('unexpected response from API');
        }

        //verify API returns text status on success
        if(!is_numeric($data['status'])){
            return $verification;
        }

        //normalize errors (client vrs server)
        switch($data['status']){
            case '5':
                $e = new Exception\Server($data['error_text'], $data['status']);
                break;
            default:
                $e = new Exception\Request($data['error_text'], $data['status']);
                break;
        }

        $e->setEntity($verification);
        throw $e;
    }

    public function cancel($verification)
    {
        return $this->control($verification, 'cancel');
    }

    public function trigger($verification)
    {
        return $this->control($verification, 'trigger_next_event');
    }

    public function check($verification, $code, $ip = null)
    {
        if(!($verification instanceof Verification)){
            $verification = new Verification($verification);
        }

        $params = [
            'request_id' => $verification->getRequestId(),
            'code' => $code
        ];

        if(!is_null($ip)){
            $params['ip'] = $ip;
        }

        $request = new Request(
            \Nexmo\Client::BASE_API . '/verify/check/json?' . http_build_query($params),
            'POST'
        );

        $response = $this->client->send($request);

        if(!$verification->getRequest()){
            $verification->setRequest($request);
        }

        if(!$verification->getResponse()) {
            $verification->setResponse($response);
        }

        if($response->getBody()->isSeekable()){
            $response->getBody()->rewind();
        }

        //check for valid data, as well as an error response from the API
        $data = json_decode($response->getBody()->getContents(), true);
        if(!isset($data['status'])){
            throw new Exception\Exception('unexpected response from API');
        }

        //normalize errors (client vrs server)
        switch($data['status']){
            case '0':
                return $verification;
            case '5':
                $e = new Exception\Server($data['error_text'], $data['status']);
                break;
            default:
                $e = new Exception\Request($data['error_text'], $data['status']);
                break;
        }

        $e->setEntity($verification);
        throw $e;
    }

    protected function control($verification, $cmd)
    {
        if(!($verification instanceof Verification)){
            $verification = new Verification($verification);
        }

        $request = new Request(
            \Nexmo\Client::BASE_API . '/verify/control/json?' . http_build_query([
                'request_id' => $verification->getRequestId(),
                'cmd' => $cmd
            ]),
            'POST'
        );

        $response = $this->client->send($request);

        if(!$verification->getRequest()){
            $verification->setRequest($request);
        }

        if(!$verification->getResponse()) {
            $verification->setResponse($response);
        }

        if($response->getBody()->isSeekable()){
            $response->getBody()->rewind();
        }

        //check for valid data, as well as an error response from the API
        $data = json_decode($response->getBody()->getContents(), true);
        if(!isset($data['status'])){
            throw new Exception\Exception('unexpected response from API');
        }

        //normalize errors (client vrs server)
        switch($data['status']){
            case '0':
                return $verification;
            case '5':
                $e = new Exception\Server($data['error_text'], $data['status']);
                break;
            default:
                $e = new Exception\Request($data['error_text'], $data['status']);
                break;
        }

        $e->setEntity($verification);
        throw $e;
    }

    /**
     * @param $array
     * @return Verification
     */
    protected function createVerificationFromArray($array)
    {
        if(!is_array($array)){
            throw new \RuntimeException('verification must implement `' . VerificationInterface::class . '` or be an array`');
        }

        foreach(['number', 'brand'] as $param){
            if(!isset($array[$param])){
                throw new \InvalidArgumentException('missing expected key `' . $param . '`');
            }
        }

        $number = $array['number'];
        $brand  = $array['brand'];

        unset($array['number']);
        unset($array['brand']);

        return new Verification($number, $brand, $array);
    }
}