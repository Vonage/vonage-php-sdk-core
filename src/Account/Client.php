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
}