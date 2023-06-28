<?php

namespace VonageTest\Subaccount\Request;

use Vonage\Subaccount\Request\NumberTransferRequest;
use VonageTest\VonageTestCase;

class NumberTransferRequestTest extends VonageTestCase
{
    public function testGettersAndSetters(): void
    {
        $apiKey = 'YOUR_API_KEY';
        $from = '1234567890';
        $to = '0987654321';
        $number = '9876543210';
        $country = 'US';

        $request = new NumberTransferRequest($apiKey, $from, $to, $number, $country);

        $this->assertEquals($apiKey, $request->getApiKey());
        $this->assertEquals($from, $request->getFrom());
        $this->assertEquals($to, $request->getTo());
        $this->assertEquals($number, $request->getNumber());
        $this->assertEquals($country, $request->getCountry());

        $newFrom = '0987654321';
        $newTo = '1234567890';
        $newNumber = '1234567890';
        $newCountry = 'GB';

        $request->setFrom($newFrom);
        $request->setTo($newTo);
        $request->setNumber($newNumber);
        $request->setCountry($newCountry);

        $this->assertEquals($newFrom, $request->getFrom());
        $this->assertEquals($newTo, $request->getTo());
        $this->assertEquals($newNumber, $request->getNumber());
        $this->assertEquals($newCountry, $request->getCountry());
    }

    public function testArrayHydration(): void
    {
        $data = [
            'from' => '1234567890',
            'to' => '0987654321',
            'number' => '9876543210',
            'country' => 'US',
        ];

        $request = new NumberTransferRequest('', '', '', '', '');
        $request->fromArray($data);

        $this->assertEquals($data['from'], $request->getFrom());
        $this->assertEquals($data['to'], $request->getTo());
        $this->assertEquals($data['number'], $request->getNumber());
        $this->assertEquals($data['country'], $request->getCountry());

        $arrayData = $request->toArray();

        $this->assertEquals($data, $arrayData);
    }
}
