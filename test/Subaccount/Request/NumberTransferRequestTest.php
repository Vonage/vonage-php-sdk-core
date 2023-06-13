<?php

namespace VonageTest\Subaccount\Request;

use Vonage\Subaccount\Request\NumberTransferRequest;
use VonageTest\VonageTestCase;

class NumberTransferRequestTest extends VonageTestCase
{
    public function testGetApiKey(): void
    {
        $apiKey = 'testApiKey';
        $request = new NumberTransferRequest($apiKey);

        $this->assertEquals($apiKey, $request->getApiKey());
    }

    public function testSetApiKey(): void
    {
        $apiKey = 'testApiKey';
        $request = new NumberTransferRequest('');

        $request->setApiKey($apiKey);

        $this->assertEquals($apiKey, $request->getApiKey());
    }

    public function testFromArray(): void
    {
        $data = [
            'from' => '123456789',
            'to' => '987654321',
            'number' => '5555555555',
            'country' => 'US',
        ];

        $request = new NumberTransferRequest('');
        $request->fromArray($data);

        $this->assertEquals($data['from'], $request->getFrom());
        $this->assertEquals($data['to'], $request->getTo());
        $this->assertEquals($data['number'], $request->getNumber());
        $this->assertEquals($data['country'], $request->getCountry());
    }

    public function testToArray(): void
    {
        $data = [
            'from' => '123456789',
            'to' => '987654321',
            'number' => '5555555555',
            'country' => 'US',
        ];

        $request = new NumberTransferRequest('');
        $request->setFrom($data['from'])
                ->setTo($data['to'])
                ->setNumber($data['number'])
                ->setCountry($data['country']);

        $this->assertEquals($data, $request->toArray());
    }
}
