<?php

namespace Nexmo\Account;

use Nexmo\Client\ClientAwareInterface;
use Nexmo\Client\ClientAwareTrait;
use Nexmo\Network;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Request;
use Nexmo\Client\Exception;


class Client implements ClientAwareInterface
{
    use ClientAwareTrait;

    public function getSmsPrice($country)
    {
        $body = $this->makePricingRequest($country, 'sms');
        $smsPrice = new SmsPrice();
        $smsPrice->jsonUnserialize($body);
        return $smsPrice;
    }

    public function getVoicePrice($country)
    {
        $body = $this->makePricingRequest($country, 'voice');
        $voicePrice = new VoicePrice();
        $voicePrice->jsonUnserialize($body);
        return $voicePrice;
    }

    protected function makePricingRequest($country, $pricingType)
    {
        $queryString = http_build_query([
            'country' => $country
        ]);

        $request = new Request(
            \Nexmo\Client::BASE_REST . '/account/get-pricing/outbound/'.$pricingType.'?'.$queryString,
            'GET',
            'php://temp'
        );

        $response = $this->client->send($request);
        $rawBody = $response->getBody()->getContents();

        if ($rawBody === '') {
            throw new Exception\Server('No results found');
        }

        return json_decode($rawBody, true);
    }

    public function getBalance()
    {

        $request = new Request(
            \Nexmo\Client::BASE_REST . '/account/get-balance',
            'GET',
            'php://temp'
        );

        $response = $this->client->send($request);
        $rawBody = $response->getBody()->getContents();

        if ($rawBody === '') {
            throw new Exception\Server('No results found');
        }

        $body = json_decode($rawBody, true);

        $balance = new Balance($body['value'], $body['autoReload']);
        return $balance;
    }

    public function topUp($trx)
    {
        $body = [
            'trx' => $trx
        ];

        $request = new Request(
            \Nexmo\Client::BASE_REST . '/account/top-up'
            ,'POST'
            , 'php://temp'
            , ['content-type' => 'application/x-www-form-urlencoded']
        );

        $request->getBody()->write(http_build_query($body));
        $response = $this->client->send($request);

        if($response->getStatusCode() != '200'){
            throw $this->getException($response, $application);
        }
    }

    protected function getException(ResponseInterface $response, $application = null)
    {
        $body = json_decode($response->getBody()->getContents(), true);
        $status = $response->getStatusCode();

        if($status >= 400 AND $status < 500) {
            $e = new Exception\Request($body['error_title'], $status);
        } elseif($status >= 500 AND $status < 600) {
            $e = new Exception\Server($body['error_title'], $status);
        } else {
            $e = new Exception\Exception('Unexpected HTTP Status Code');
        }

        return $e;
    }

}