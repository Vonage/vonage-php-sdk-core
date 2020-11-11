<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Numbers;

use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Exception as ClientException;
use Vonage\Client\Exception\Request as RequestException;
use Vonage\Numbers\Client as NumbersClient;
use Vonage\Numbers\Filter\AvailableNumbers;
use Vonage\Numbers\Number;
use VonageTest\Psr7AssertionTrait;

use function fopen;
use function is_null;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

    /**
     * @var APIResource
     */
    protected $apiClient;

    protected $vonageClient;

    /**
     * @var NumbersClient
     */
    protected $numberClient;

    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize('Vonage\Client');
        $this->vonageClient->getRestUrl()->willReturn('https://rest.nexmo.com');

        /** @noinspection PhpParamsInspection */
        $this->numberClient = (new NumbersClient())->setClient($this->vonageClient->reveal());
    }

    /**
     * @dataProvider updateNumber
     *
     * @param $payload
     * @param $id
     * @param $expectedId
     * @param $lookup
     *
     * @throws ClientException\Exception
     * @throws RequestException
     * @throws ClientExceptionInterface
     */
    public function testUpdateNumber($payload, $id, $expectedId, $lookup): void
    {
        //based on the id provided, may need to look up the number first
        if ($lookup) {
            if (1415550100 === (int)$id || is_null($id)) {
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

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($expectedId) {
            if ($request->getUri()->getPath() === '/account/numbers') {
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
            $this->assertRequestFormBodyContains('voiceStatusCallbackUrl', 'https://example.com/new_status', $request);

            return true;
        }))->willReturn($first, $second, $third);

        if (isset($id)) {
            $number = @$this->numberClient->update($payload, $id);
        } else {
            $number = @$this->numberClient->update($payload);
        }

        $this->assertInstanceOf(Number::class, $number);
        if ($payload instanceof Number) {
            $this->assertSame($payload, $number);
        }
    }

    /**
     * @return array[]
     */
    public function updateNumber(): array
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
     *
     * @param $payload
     * @param $id
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Server
     * @throws RequestException
     */
    public function testGetNumber($payload, $id): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
            $this->assertEquals('/account/numbers', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            $this->assertRequestQueryContains('pattern', $id, $request);
            return true;
        }))->willReturn($this->getResponse('single'));

        $number = @$this->numberClient->get($payload);

        $this->assertInstanceOf(Number::class, $number);

        if ($payload instanceof Number) {
            $this->assertSame($payload, $number);
        }

        $this->assertSame($id, $number->getId());
    }

    public function numbers(): array
    {
        return [
            ['1415550100', '1415550100'],
            [new Number('1415550100'), '1415550100'],
        ];
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Server
     * @throws RequestException
     */
    public function testListNumbers(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/numbers', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse('list'));

        $numbers = $this->numberClient->search();

        $this->assertIsArray($numbers);
        $this->assertInstanceOf(Number::class, $numbers[0]);
        $this->assertInstanceOf(Number::class, $numbers[1]);
        $this->assertSame('14155550100', $numbers[0]->getId());
        $this->assertSame('14155550101', $numbers[1]->getId());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Server
     * @throws RequestException
     */
    public function testSearchAvailablePassesThroughWhitelistedOptions(): void
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

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Server
     * @throws RequestException
     */
    public function testSearchAvailableAcceptsFilterInterfaceOptions(): void
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
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Server
     * @throws RequestException
     */
    public function testUnknownParameterValueForSearchThrowsException(): void
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Unknown option: 'foo'");

        @$this->numberClient->searchAvailable('US', ['foo' => 'bar']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Server
     * @throws RequestException
     */
    public function testSearchAvailableReturnsNumberList(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/number/search', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('available-numbers'));

        $numbers = $this->numberClient->searchAvailable('US');

        $this->assertIsArray($numbers);
        $this->assertInstanceOf(Number::class, $numbers[0]);
        $this->assertInstanceOf(Number::class, $numbers[1]);
        $this->assertSame('14155550100', $numbers[0]->getId());
        $this->assertSame('14155550101', $numbers[1]->getId());
    }

    /**
     * A search can return an empty set `[]` result when no numbers are found
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Server
     * @throws RequestException
     */
    public function testSearchAvailableReturnsEmptyNumberList(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/number/search', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('empty'));

        $numbers = @$this->numberClient->searchAvailable('US');

        $this->assertIsArray($numbers);
        $this->assertEmpty($numbers);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Server
     * @throws RequestException
     */
    public function testSearchOwnedErrorsOnUnknownSearchParameters(): void
    {
        $this->expectException(ClientException\Request::class);
        $this->expectExceptionMessage("Unknown option: 'foo'");

        @$this->numberClient->searchOwned('1415550100', ['foo' => 'bar']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Server
     * @throws RequestException
     */
    public function testSearchOwnedPassesInAllowedAdditionalParameters(): void
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

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Server
     * @throws RequestException
     */
    public function testSearchOwnedReturnsSingleNumber(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/numbers', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('single'));

        $numbers = $this->numberClient->searchOwned('1415550100');

        $this->assertIsArray($numbers);
        $this->assertInstanceOf(Number::class, $numbers[0]);
        $this->assertSame('1415550100', $numbers[0]->getId());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     */
    public function testPurchaseNumberWithNumberObject(): void
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

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     */
    public function testPurchaseNumberWithNumberAndCountry(): void
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
     *
     * @param $number
     * @param $country
     * @param $responseFile
     * @param $expectedHttpCode
     * @param $expectedException
     * @param $expectedExceptionMessage
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     */
    public function testPurchaseNumberErrors(
        $number,
        $country,
        $responseFile,
        $expectedHttpCode,
        $expectedException,
        $expectedExceptionMessage
    ): void {
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

    public function purchaseNumberErrorProvider(): array
    {
        $r = [];

        $r['mismatched number/country'] = [
            '14155510100',
            'GB',
            'method-failed',
            420,
            ClientException\Request::class,
            'method failed'
        ];

        $r['user already owns number'] = [
            '14155510100',
            'GB',
            'method-failed',
            420,
            ClientException\Request::class,
            'method failed'
        ];

        $r['someone else owns the number'] = [
            '14155510100',
            'GB',
            'method-failed',
            420,
            ClientException\Request::class,
            'method failed'
        ];

        return $r;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Server
     * @throws RequestException
     */
    public function testCancelNumberWithNumberObject(): void
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

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Server
     * @throws RequestException
     */
    public function testCancelNumberWithNumberString(): void
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

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Server
     * @throws RequestException
     */
    public function testCancelNumberWithNumberAndCountryString(): void
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

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Server
     * @throws RequestException
     */
    public function testCancelNumberError(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/number/cancel', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('method-failed', 420));

        $this->expectException(ClientException\Request::class);
        $this->expectExceptionMessage('method failed');

        $num = new Number('1415550100', 'US');
        @$this->numberClient->cancel($num);
    }

    /**
     * Make sure that integer values that fail validation throw properly
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Server
     * @throws RequestException
     */
    public function testInvalidIntegerValueForSearchThrowsException(): void
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Invalid value: 'size' must be an integer");

        @$this->numberClient->searchOwned(null, ['size' => 'bob']);
    }

    /**
     * Make sure that boolean values that fail validation throw properly
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Server
     * @throws RequestException
     */
    public function testInvalidBooleanValueForSearchThrowsException(): void
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Invalid value: 'has_application' must be a boolean value");

        @$this->numberClient->searchOwned(null, ['has_application' => 'bob']);
    }

    /**
     * Get the API response we'd expect for a call to the API.
     */
    protected function getResponse(string $type = 'success', int $status = 200): Response
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'), $status);
    }
}
