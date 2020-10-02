<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Numbers;

use Prophecy\Argument;
use Vonage\Numbers\Client;
use Vonage\Numbers\Number;
use Vonage\Client\Exception;
use Zend\Diactoros\Response;
use Vonage\Client\APIResource;
use PHPUnit\Framework\TestCase;
use VonageTest\Psr7AssertionTrait;
use Vonage\Client\Exception\Request;
use Psr\Http\Message\RequestInterface;
use Vonage\Numbers\Filter\AvailableNumbers;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

    /**
     * @var APIResource
     */
    protected $apiClient;

    protected $vonageClient;

    /**
     * @var Client
     */
    protected $numberClient;

    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize('Vonage\Client');
        $this->vonageClient->getRestUrl()->willReturn('https://rest.nexmo.com');

        $this->numberClient = new Client();
        $this->numberClient->setClient($this->vonageClient->reveal());
    }

    /**
     * @dataProvider updateNumber
     */
    public function testUpdateNumber($payload, $id, $expectedId, $lookup)
    {
        //based on the id provided, may need to look up the number first
        if ($lookup) {
            if(is_null($id) OR ('1415550100' == $id)){
                $first = $this->getResponse('single');
            } else {
                $first = $this->getResponse('single-update');
            }

            $second = $this->getResponse('post');
            $third = $this->getResponse('single');
        } else {
            $first = $this->getResponse('post');
            $second = $this->getResponse('single');
            $third = null;
        }

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($expectedId, $lookup) {
            if ($request->getUri()->getPath() == '/account/numbers') {
                //just getting the number first / last
                return true;
            }

            $this->assertEquals('/number/update', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            
            $this->assertRequestFormBodyContains('country', 'US', $request);
            $this->assertRequestFormBodyContains('msisdn', $expectedId, $request);


            $this->assertRequestFormBodyContains('moHttpUrl', 'https://example.com/new_message', $request);
            $this->assertRequestFormBodyContains('voiceCallbackType', 'vxml', $request);
            $this->assertRequestFormBodyContains('voiceCallbackValue', 'https://example.com/new_voice', $request);
            $this->assertRequestFormBodyContains('voiceStatusCallbackUrl' , 'https://example.com/new_status' , $request);

            return true;
        }))->willReturn($first, $second, $third);

        if (isset($id)) {
            $number = @$this->numberClient->update($payload, $id);
        } else {
            $number = @$this->numberClient->update($payload);
        }

        $this->assertInstanceOf('Vonage\Numbers\Number', $number);
        if ($payload instanceof Number) {
            $this->assertSame($payload, $number);
        }
    }

    public function updateNumber()
    {

        $raw = $rawId = [
            'moHttpUrl' => 'https://example.com/new_message',
            'voiceCallbackType' => 'vxml',
            'voiceCallbackValue' => 'https://example.com/new_voice',
            'voiceStatusCallbackUrl' => 'https://example.com/new_status'
        ];

        $rawId['country'] = 'US';
        $rawId['msisdn'] = '1415550100';

        $number = new Number('1415550100');
        $number->setWebhook(Number::WEBHOOK_MESSAGE, 'https://example.com/new_message');
        $number->setWebhook(Number::WEBHOOK_VOICE_STATUS, 'https://example.com/new_status');
        $number->setVoiceDestination('https://example.com/new_voice');

        $noLookup = new Number('1415550100', 'US');
        $noLookup->setWebhook(Number::WEBHOOK_MESSAGE, 'https://example.com/new_message');
        $noLookup->setWebhook(Number::WEBHOOK_VOICE_STATUS, 'https://example.com/new_status');
        $noLookup->setVoiceDestination('https://example.com/new_voice');

        $fresh = new Number('1415550100', 'US');
        $fresh->setWebhook(Number::WEBHOOK_MESSAGE, 'https://example.com/new_message');
        $fresh->setWebhook(Number::WEBHOOK_VOICE_STATUS, 'https://example.com/new_status');
        $fresh->setVoiceDestination('https://example.com/new_voice');
        
        return [
            [$raw, '1415550100', '1415550100', true],
            [$rawId, null, '1415550100', false],
            [clone $number, null, '1415550100', true],
            [clone $number, '1415550100', '1415550100', true],
            [clone $noLookup, null, '1415550100', false],
            [clone $fresh, '1415550100', '1415550100', true],
        ];
    }

    /**
     * @dataProvider numbers
     */
    public function testGetNumber($payload, $id)
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
            $this->assertEquals('/account/numbers', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            $this->assertRequestQueryContains('pattern', $id, $request);
            return true;
        }))->willReturn($this->getResponse('single'));

        $number = @$this->numberClient->get($payload);

        $this->assertInstanceOf('Vonage\Numbers\Number', $number);
        if ($payload instanceof Number) {
            $this->assertSame($payload, $number);
        }

        $this->assertSame($id, $number->getId());
    }

    public function numbers()
    {
        return [
            ['1415550100', '1415550100'],
            [new Number('1415550100'), '1415550100'],
        ];
    }

    public function testListNumbers()
    {
        $this->vonageClient->send(Argument::that(function(RequestInterface $request){
            $this->assertEquals('/account/numbers', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse('list'));

        $numbers = $this->numberClient->search();

        $this->assertInternalType('array', $numbers);
        $this->assertInstanceOf('Vonage\Numbers\Number', $numbers[0]);
        $this->assertInstanceOf('Vonage\Numbers\Number', $numbers[1]);

        $this->assertSame('14155550100', $numbers[0]->getId());
        $this->assertSame('14155550101', $numbers[1]->getId());
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

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($options) {
            $this->assertEquals('/number/search', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());

            // Things that are whitelisted should be shown
            foreach ($options as $name => $value) {
                $this->assertRequestQueryContains($name, $value, $request);
            }

            return true;
        }))->willReturn($this->getResponse('available-numbers'));

        @$this->numberClient->searchAvailable('US', $options);
    }

    public function testSearchAvailableAcceptsFilterInterfaceOptions()
    {
        $options = new AvailableNumbers([
            'pattern' => '1',
            'search_pattern' => 2,
            'features' => 'SMS,VOICE',
            'size' => 100,
            'index' => 19
        ]);

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/number/search', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse('available-numbers'));

        @$this->numberClient->searchAvailable('US', $options);
    }

    /**
     * Make sure that unknown parameters fail validation
     */
    public function testUnknownParameterValueForSearchThrowsException()
    {
        $this->expectException(Request::class);
        $this->expectExceptionMessage("Unknown option: 'foo'");

        @$this->numberClient->searchAvailable('US', ['foo' => 'bar']);
    }

    public function testSearchAvailableReturnsNumberList()
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/number/search', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse('available-numbers'));

        $numbers = $this->numberClient->searchAvailable('US');

        $this->assertInternalType('array', $numbers);
        $this->assertInstanceOf('Vonage\Numbers\Number', $numbers[0]);
        $this->assertInstanceOf('Vonage\Numbers\Number', $numbers[1]);

        $this->assertSame('14155550100', $numbers[0]->getId());
        $this->assertSame('14155550101', $numbers[1]->getId());
    }

    /**
     * A search can return an empty set `[]` result when no numbers are found
     */
    public function testSearchAvailableReturnsEmptyNumberList()
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
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
        
        @$this->numberClient->searchOwned('1415550100', [
            'foo' => 'bar',
        ]);
    }

    public function testSearchOwnedPassesInAllowedAdditionalParameters()
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
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

        @$this->numberClient->searchOwned('1415550100', [
            'index' => 1,
            'size' => '100',
            'search_pattern' => 0,
            'has_application' => false,
            'country' => 'GB',
        ]);
    }

    public function testSearchOwnedReturnsSingleNumber()
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/numbers', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse('single'));

        $numbers = $this->numberClient->searchOwned('1415550100');

        $this->assertInternalType('array', $numbers);
        $this->assertInstanceOf('Vonage\Numbers\Number', $numbers[0]);

        $this->assertSame('1415550100', $numbers[0]->getId());
    }

    public function testPurchaseNumberWithNumberObject()
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/number/buy', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse('post'));

        $number = new Number('1415550100', 'US');
        $this->numberClient->purchase($number);

        // There's nothing to assert here as we don't do anything with the response.
        // If there's no exception thrown, everything is fine!
    }

    public function testPurchaseNumberWithNumberAndCountry()
    {
        // When providing a number string, the first thing that happens is a GET request to fetch number details
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            return $request->getUri()->getPath() === '/account/numbers';
        }))->willReturn($this->getResponse('single'));

        // Then we purchase the number
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            if ($request->getUri()->getPath() === '/number/buy') {
                $this->assertEquals('/number/buy', $request->getUri()->getPath());
                $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
                $this->assertEquals('POST', $request->getMethod());
                return true;
            }
            return false;
        }))->willReturn($this->getResponse('post'));
        @$this->numberClient->purchase('1415550100', 'US');

        // There's nothing to assert here as we don't do anything with the response.
        // If there's no exception thrown, everything is fine!
    }

    /**
     * @dataProvider purchaseNumberErrorProvider
     */
    public function testPurchaseNumberErrors($number, $country, $responseFile, $expectedHttpCode, $expectedException, $expectedExceptionMessage)
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/number/buy', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse($responseFile, $expectedHttpCode));

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $num = new Number($number, $country);
        @$this->numberClient->purchase($num);
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
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/number/cancel', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse('cancel'));

        $number = new Number('1415550100', 'US');
        @$this->numberClient->cancel($number);

        // There's nothing to assert here as we don't do anything with the response.
        // If there's no exception thrown, everything is fine!
    }

    public function testCancelNumberWithNumberString()
    {
        // When providing a number string, the first thing that happens is a GET request to fetch number details
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            return $request->getUri()->getPath() === '/account/numbers';
        }))->willReturn($this->getResponse('single'));


        // Then we get a POST request to cancel
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            if ($request->getUri()->getPath() === '/number/cancel') {
                $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
                $this->assertEquals('POST', $request->getMethod());
                return true;
            }
            return false;
        }))->willReturn($this->getResponse('cancel'));

        @$this->numberClient->cancel('1415550100');
    }

    public function testCancelNumberWithNumberAndCountryString()
    {
        // When providing a number string, the first thing that happens is a GET request to fetch number details
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            return $request->getUri()->getPath() === '/account/numbers';
        }))->willReturn($this->getResponse('single'));


        // Then we get a POST request to cancel
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            if ($request->getUri()->getPath() === '/number/cancel') {
                $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
                $this->assertEquals('POST', $request->getMethod());
                return true;
            }
            return false;
        }))->willReturn($this->getResponse('cancel'));

        @$this->numberClient->cancel('1415550100', 'US');
    }

    public function testCancelNumberError()
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/number/cancel', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse('method-failed', 420));

        $this->expectException(Exception\Request::class);
        $this->expectExceptionMessage('method failed');

        $num = new Number('1415550100', 'US');
        @$this->numberClient->cancel($num);
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
