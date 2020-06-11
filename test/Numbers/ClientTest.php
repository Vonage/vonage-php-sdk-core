<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Numbers;

use Prophecy\Argument;
use Nexmo\Numbers\Client;
use Nexmo\Numbers\Number;
use Nexmo\Client\Exception;
use Zend\Diactoros\Response;
use Nexmo\Client\APIResource;
use PHPUnit\Framework\TestCase;
use Nexmo\Client as NexmoClient;
use NexmoTest\Psr7AssertionTrait;
use Nexmo\Client\Exception\Request;
use Nexmo\Entity\Filter\KeyValueFilter;
use Nexmo\Numbers\Filter\OwnedNumbers;
use Psr\Http\Message\RequestInterface;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

    /**
     * @var APIResource
     */
    protected $apiClient;

    protected $nexmoClient;

    /**
     * @var Client
     */
    protected $numberClient;

    public function setUp()
    {
        $this->nexmoClient = $this->prophesize('Nexmo\Client');
        $this->nexmoClient->getRestUrl()->willReturn('https://rest.nexmo.com');

        $this->apiClient = new APIResource();
        $this->apiClient->setBaseUrl('https://rest.nexmo.com')
            ->setIsHAL(false)
            ->setClient($this->nexmoClient->reveal())
        ;

        $this->numberClient = new Client($this->apiClient);
        $this->numberClient->setClient($this->nexmoClient->reveal());
    }

    /**
     * @dataProvider updateNumber
     */
    public function testUpdateNumber($payload, $rawData)
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($rawData) {
            $this->assertEquals('/number/update', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            
            $this->assertRequestFormBodyContains('country', $rawData['country'], $request);
            $this->assertRequestFormBodyContains('msisdn', $rawData['msisdn'], $request);
            $this->assertRequestFormBodyContains('moHttpUrl', $rawData['moHttpUrl'], $request);
            $this->assertRequestFormBodyContains('voiceCallbackType', $rawData['voiceCallbackType'], $request);
            $this->assertRequestFormBodyContains('voiceCallbackValue', $rawData['voiceCallbackValue'], $request);
            $this->assertRequestFormBodyContains('voiceStatusCallbackUrl', $rawData['voiceStatusCallbackUrl'], $request);

            return true;
        }))->willReturn($this->getResponse('update-number'));

        $number = $this->numberClient->update($payload);

        $this->assertSame($number->toArray(), $payload->toArray());
    }

    public function updateNumber()
    {
        $rawData = json_decode(file_get_contents(__DIR__ . '/responses/list.json'), true)['numbers'];
        
        $number = new Number();
        $number->fromArray($rawData[0]);

        $number2 = new Number();
        $number2->fromArray($rawData[1]);
        
        return [
            [clone $number, $rawData[0]],
            [clone $number2, $rawData[1]],
        ];
    }

    public function testGetNumber()
    {
        $id = '1415550100';
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($id) {
            $this->assertEquals('/account/numbers', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            $this->assertRequestQueryContains('pattern', $id, $request);
            return true;
        }))->willReturn($this->getResponse('single'));

        $number = $this->numberClient->get($id);

        $this->assertInstanceOf('Nexmo\Numbers\Number', $number);
        $this->assertSame($id, $number->getId());
    }

    public function testSearchAvailablePassesThroughWhitelistedOptions()
    {
        $options = [
            'pattern' => '1',
            'search_pattern' => '2',
            'features' => 'SMS,VOICE',
            'size' => '100',
            'index' => '19'
        ];

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($options) {
            $this->assertEquals('/number/search', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());

            // Things that are whitelisted should be shown
            foreach ($options as $name => $value) {
                $this->assertRequestQueryContains($name, $value, $request);
            }

            return true;
        }))->willReturn($this->getResponse('available-numbers'));

        @$this->numberClient->searchAvailable('US', new KeyValueFilter($options));
    }

    /**
     * Make sure that unknown parameters fail validation
     */
    public function testUnknownParameterValueForSearchThrowsException()
    {
        $this->expectException(Request::class);
        $this->expectExceptionMessage("Unknown option: 'foo'");

        @$this->numberClient->searchAvailable('US', new KeyValueFilter(['foo' => 'bar']));
    }

    public function testSearchAvailableReturnsNumberList()
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/number/search', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse('available-numbers'));

        $numbers = $this->numberClient->searchAvailable('US');

        $this->assertInternalType('array', $numbers);
        $this->assertInstanceOf('Nexmo\Numbers\Number', $numbers[0]);
        $this->assertInstanceOf('Nexmo\Numbers\Number', $numbers[1]);

        $this->assertSame('14155550100', $numbers[0]->getId());
        $this->assertSame('14155550101', $numbers[1]->getId());
    }

    /**
     * A search can return an empty set `[]` result when no numbers are found
     */
    public function testSearchAvailableReturnsEmptyNumberList()
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/number/search', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse('empty'));

        $numbers = @$this->numberClient->searchAvailable('US');

        $this->assertInternalType('array', $numbers);
        $this->assertEmpty($numbers);
    }

    public function testSearchOwnedErrorsOnUnknownSearchParameters()
    {

        $this->expectException(Exception\Request::class);
        $this->expectExceptionMessage("Unknown option: 'foo'");
        
        @$this->numberClient->searchOwned(new KeyValueFilter([
            'pattern' => '1415550100',
            'foo' => 'bar',
        ]));
    }

    public function testSearchOwnedPassesInAllowedAdditionalParameters()
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/numbers', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            $this->assertRequestQueryContains('index', '1', $request);
            $this->assertRequestQueryContains('size', '100', $request);
            $this->assertRequestQueryContains('search_pattern', '0', $request);
            $this->assertRequestQueryContains('has_application', 'false', $request);
            $this->assertRequestQueryContains('pattern', '1415550100', $request);
            return true;
        }))->willReturn($this->getResponse('single'));

        $this->numberClient->searchOwned(new OwnedNumbers([
            'pattern' => '1415550100',
            'index' => 1,
            'size' => '100',
            'search_pattern' => 0,
            'has_application' => false,
        ]));
    }

    public function testSearchOwnedReturnsSingleNumber()
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/numbers', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse('single'));

        $numbers = $this->numberClient->searchOwned(new OwnedNumbers(['pattern' => '1415550100']));

        $this->assertInternalType('array', $numbers);
        $this->assertInstanceOf('Nexmo\Numbers\Number', $numbers[0]);

        $this->assertSame('1415550100', $numbers[0]->getId());
    }

    public function testPurchaseNumberWithNumberObject()
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/number/buy', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse('post'));

        $number = new Number('1415550100', 'US');
        $this->numberClient->purchase($number->getMsisdn(), $number->getCountry());
    }

    public function testPurchaseNumberWithNumberAndCountry()
    {
        // When providing a number string, the first thing that happens is a GET request to fetch number details
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            return $request->getUri()->getPath() === '/account/numbers';
        }))->willReturn($this->getResponse('single'));

        // Then we purchase the number
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            if ($request->getUri()->getPath() === '/number/buy') {
                $this->assertEquals('/number/buy', $request->getUri()->getPath());
                $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
                $this->assertEquals('POST', $request->getMethod());
                return true;
            }
            return false;
        }))->willReturn($this->getResponse('post'));
        $this->numberClient->purchase('1415550100', 'US');
    }

    /**
     * @dataProvider purchaseNumberErrorProvider
     */
    public function testPurchaseNumberErrors($number, $country, $responseFile, $expectedHttpCode, $expectedException, $expectedExceptionMessage)
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/number/buy', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse($responseFile, $expectedHttpCode));

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->numberClient->purchase($number, $country);
    }

    public function purchaseNumberErrorProvider()
    {
        $r = [];

        $r['mismatched number/country'] = ['14155510100', 'GB', 'method-failed', 420, Exception\Request::class, 'method failed'];
        $r['user already owns number'] = ['14155510100', 'GB', 'method-failed', 420, Exception\Request::class, 'method failed'];
        $r['someone else owns the number'] = ['14155510100', 'GB', 'method-failed', 420, Exception\Request::class, 'method failed'];

        return $r;
    }

    public function testCancelNumberWithNumberObject()
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/number/cancel', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse('cancel'));

        $number = new Number('1415550100', 'US');
        $this->numberClient->cancel($number->getMsisdn(), $number->getCountry());
    }

    public function testCancelNumberWithNumberString()
    {
        // When providing a number string, the first thing that happens is a GET request to fetch number details
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            return $request->getUri()->getPath() === '/account/numbers';
        }))->willReturn($this->getResponse('single'));


        // Then we get a POST request to cancel
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            if ($request->getUri()->getPath() === '/number/cancel') {
                $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
                $this->assertEquals('POST', $request->getMethod());
                return true;
            }
            return false;
        }))->willReturn($this->getResponse('cancel'));

        $this->numberClient->cancel('1415550100', 'US');
    }

    public function testCancelNumberWithNumberAndCountryString()
    {
        // When providing a number string, the first thing that happens is a GET request to fetch number details
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            return $request->getUri()->getPath() === '/account/numbers';
        }))->willReturn($this->getResponse('single'));


        // Then we get a POST request to cancel
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            if ($request->getUri()->getPath() === '/number/cancel') {
                $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
                $this->assertEquals('POST', $request->getMethod());
                return true;
            }
            return false;
        }))->willReturn($this->getResponse('cancel'));

        $this->numberClient->cancel('1415550100', 'US');
    }

    public function testCancelNumberError()
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/number/cancel', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse('method-failed', 420));

        $this->expectException(Exception\Request::class);
        $this->expectExceptionMessage('method failed');

        $num = new Number('1415550100', 'US');
        $this->numberClient->cancel($num->getMsisdn(), $num->getCountry());
    }

    /**
     * Make sure that integer values that fail validation throw properly
     */
    public function testInvalidIntegerValueForSearchThrowsException()
    {
        $this->expectException(Request::class);
        $this->expectExceptionMessage("Invalid value: 'size' must be an integer");

        @$this->numberClient->searchOwned(null, ['size' => 'bob']);
    }

    /**
     * Make sure that boolean values that fail validation throw properly
     */
    public function testInvalidBooleanValueForSearchThrowsException()
    {
        $this->expectException(Request::class);
        $this->expectExceptionMessage("Invalid value: 'has_application' must be a boolean value");

        @$this->numberClient->searchOwned(null, ['has_application' => 'bob']);
    }

    /**
     * Get the API response we'd expect for a call to the API.
     *
     * @param string $type
     * @return Response
     */
    protected function getResponse($type = 'success', $status = 200)
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'r'), $status);
    }
}
