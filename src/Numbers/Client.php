<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Numbers;

use Http\Client\Common\Exception\ClientErrorException;
use Nexmo\Client\ClientAwareInterface;
use Nexmo\Client\ClientAwareTrait;
use Psr\Http\Message\ResponseInterface;
use Nexmo\Client\Exception;
use Zend\Diactoros\Request;

class Client implements ClientAwareInterface
{
    use ClientAwareTrait;

    public function update($number, $id = null)
    {
        if(!is_null($id)){
            $update = $this->get($id);
        }

        if($number instanceof Number){
            $body = $number->getRequestData();
            if(!isset($update) AND !isset($body['country'])){
                $data = $this->get($number->getId());
                $body['msisdn'] = $data->getId();
                $body['country'] = $data->getCountry();
            }
        } else {
            $body = $number;
        }

        if(isset($update)){
            $body['msisdn'] = $update->getId();
            $body['country'] = $update->getCountry();
        }

        $request = new Request(
            \Nexmo\Client::BASE_REST . '/number/update',
            'POST',
            'php://temp',
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        );

        $request->getBody()->write(http_build_query($body));
        $response = $this->client->send($request);

        if('200' != $response->getStatusCode()){
            throw $this->getException($response);
        }

        if(isset($update) AND ($number instanceof Number)){
            return $this->get($number);
        }

        if($number instanceof Number){
            return $this->get($number);
        }

        return $this->get($body['msisdn']);
    }

    public function get($number)
    {
        if($number instanceof Number){
            $query = ['pattern' => $number->getId()];
        } else {
            $query = ['pattern' => $number];
        }

        $request = new Request(
            \Nexmo\Client::BASE_REST . '/account/numbers?' . http_build_query($query),
            'GET',
            'php://temp'
        );

        $response = $this->client->send($request);

        if($response->getStatusCode() != '200'){
            throw $this->getException($response);
        }

        $body = json_decode($response->getBody()->getContents(), true);
        if(empty($body)){
            throw new Exception\Request('number not found', 404);
        }

        if(!isset($body['count']) OR !isset($body['numbers'])){
            throw new Exception\Exception('unexpected response format');
        }

        if($body['count'] != '1'){
            throw new Exception\Request('number not found', 404);
        }

        if(!($number instanceof Number)){
            $number = new Number();
        }

        $number->jsonUnserialize($body['numbers'][0]);

        return $number;
    }



    protected function getException(ResponseInterface $response)
    {
        $body = json_decode($response->getBody()->getContents(), true);
        $status = $response->getStatusCode();

        if($status >= 400 AND $status < 500) {
            $e = new Exception\Request($body['error-code-label'], $status);
        } elseif($status >= 500 AND $status < 600) {
            $e = new Exception\Server($body['error-code-label'], $status);
        } else {
            $e = new Exception\Exception('Unexpected HTTP Status Code');
            throw $e;
        }

        return $e;
    }

}