<?php

declare(strict_types=1);

namespace VonageTest\Numbers;

use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Basic;
use Vonage\Client\Credentials\Container;
use Vonage\Client\Exception as ClientException;
use Vonage\Client\Exception\Request as RequestException;
use Vonage\Numbers\Client as NumbersClient;
use Vonage\Numbers\Filter\AvailableNumbers;
use Vonage\Numbers\Filter\OwnedNumbers;
use Vonage\Numbers\Number;
use VonageTest\Traits\HTTPTestTrait;
use VonageTest\Traits\Psr7AssertionTrait;
use VonageTest\VonageTestCase;

use function is_null;

class ClientTest extends VonageTestCase
{
    use Psr7AssertionTrait;
    use HTTPTestTrait;

    /**
     * @var APIResource
     */
    protected APIResource $apiClient;

    protected \Vonage\Client|\Prophecy\Prophecy\ObjectProphecy $vonageClient;

    protected APIResource $api;

    protected NumbersClient $numberClient;

    public function setUp(): void
    {
        $this->responsesDirectory = __DIR__ . '/responses';

        $this->vonageClient = $this->prophesize(\Vonage\Client::class);
        $this->vonageClient->getRestUrl()->willReturn('https://rest.nexmo.com');
        $this->vonageClient->getCredentials()->willReturn(
            new Container(new Basic('abc', 'def'))
        );

        $this->api = new APIResource();
        $this->api->setBaseUrl('https://rest.nexmo.com')
            ->setIsHAL(false);

        $this->api->setClient($this->vonageClient->reveal());

        /** @noinspection PhpParamsInspection */
        $this->numberClient = (new NumbersClient($this->api));
    }

    /**
     * @dataProvider updateNumber
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

            $this->assertRequestFormBodyContains(
                'moHttpUrl',
                'https://example.com/new_message',
                $request
            );
            $this->assertRequestFormBodyContains('voiceCallbackType', 'vxml', $request);
            $this->assertRequestFormBodyContains(
                'voiceCallbackValue',
                'https://example.com/new_voice',
                $request
            );
            $this->assertRequestFormBodyContains(
                'voiceStatusCallback',
                'https://example.com/new_status',
                $request
            );

            return true;
        }))->willReturn($first, $second, $third);

        if (isset($id)) {
            $number = $this->numberClient->update($payload, $id);
        } else {
            $number = $this->numberClient->update($payload);
        }

        $this->assertInstanceOf(Number::class, $number);
        if ($payload instanceof Number) {
            $this->assertSame($payload->getId(), $number->getId());
        }
    }

    public function updateNumber(): array
    {
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
            [clone $number, '1415550100', '1415550100', true],
        ];
    }

    /**
     * @dataProvider numbers
     */
    public function testGetNumber($payload, $id): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
            $this->assertEquals('/account/numbers', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertRequestMethod('GET', $request);
            $this->assertRequestQueryContains('pattern', $id, $request);
            return true;
        }))->willReturn($this->getResponse('single'));

        $number = $this->numberClient->get($payload->getId());

        if ($payload instanceof Number) {
            $this->assertSame($payload->getId(), $number->getId());
        }

        $this->assertSame($id, $number->getId());
    }

    public function numbers(): array
    {
        return [
            [new Number('1415550100'), '1415550100'],
        ];
    }

    public function testListNumbers(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/numbers', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertRequestMethod('GET', $request);
            return true;
        }))->willReturn($this->getResponse('list'));

        $numbers = $this->numberClient->searchOwned();

        $this->assertIsArray($numbers);
        $this->assertInstanceOf(Number::class, $numbers[0]);
        $this->assertInstanceOf(Number::class, $numbers[1]);
        $this->assertSame('14155550100', $numbers[0]->getId());
        $this->assertSame('14155550101', $numbers[1]->getId());
    }

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
            $this->assertRequestMethod('GET', $request);

            // Things that are whitelisted should be shown
            foreach ($options as $name => $value) {
                $this->assertRequestQueryContains($name, $value, $request);
            }

            return true;
        }))->willReturn($this->getResponse('available-numbers'));

        $this->numberClient->searchAvailable('US', new AvailableNumbers($options));
    }

    public function testSearchAvailableAcceptsFilterInterfaceOptions(): void
    {
        $options = new AvailableNumbers([
            'pattern' => '1',
            'search_pattern' => 2,
            'type' => 'landline',
            'features' => 'SMS,VOICE',
            'size' => '100',
            'index' => '19'
        ]);

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://rest.nexmo.com/number/search?size=100&index=19&country=US&' .
                        'search_pattern=2&pattern=1&type=landline&features=SMS%2CVOICE&page_index=1',
                $uriString
            );
            $this->assertRequestMethod('GET', $request);

            return true;
        }))->willReturn($this->getResponse('available-numbers'));

        $this->numberClient->searchAvailable('US', $options);
    }

    public function testUnknownParameterValueForSearchThrowsException(): void
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Unknown option: 'foo'");

        $this->numberClient->searchAvailable('US', new AvailableNumbers(['foo' => 'bar']));
    }

    public function testSearchAvailableReturnsNumberList(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/number/search', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertRequestMethod('GET', $request);

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
     */
    public function testSearchAvailableReturnsEmptyNumberList(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/number/search', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertRequestMethod('GET', $request);

            return true;
        }))->willReturn($this->getResponse('empty'));

        $numbers = $this->numberClient->searchAvailable('US');

        $this->assertIsArray($numbers);
        $this->assertEmpty($numbers);
    }

    public function testSearchOwnedErrorsOnUnknownSearchParameters(): void
    {
        $this->expectException(ClientException\Request::class);
        $this->expectExceptionMessage("Unknown option: 'foo'");

        $this->numberClient->searchOwned(new OwnedNumbers(['foo' => 'bar']), '1415550100');
    }

    public function testSearchOwnedPassesInAllowedAdditionalParameters(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/numbers', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertRequestMethod('GET', $request);
            $this->assertRequestQueryContains('index', '1', $request);
            $this->assertRequestQueryContains('size', '100', $request);
            $this->assertRequestQueryContains('search_pattern', '0', $request);
            $this->assertRequestQueryContains('has_application', 'false', $request);
            $this->assertRequestQueryContains('pattern', '1415550100', $request);

            return true;
        }))->willReturn($this->getResponse('single'));

        $this->numberClient->searchOwned(
            '1415550100',
            new OwnedNumbers([
                'index' => 1,
                'size' => '100',
                'search_pattern' => 0,
                'has_application' => false,
                'country' => 'GB',
            ])
        );
    }

    public function testSearchOwnedReturnsSingleNumber(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/account/numbers', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertRequestMethod('GET', $request);

            return true;
        }))->willReturn($this->getResponse('single'));

        $numbers = $this->numberClient->searchOwned('1415550100');

        $this->assertIsArray($numbers);
        $this->assertInstanceOf(Number::class, $numbers[0]);
        $this->assertSame('1415550100', $numbers[0]->getId());
    }

    public function testPurchaseNumberWithNumberObject(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/number/buy', $request->getUri()->getPath());
            $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
            $this->assertRequestFormBodyContains('country', 'US', $request);
            $this->assertRequestFormBodyContains('msisdn', '1415550100', $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('post'));

        $this->numberClient->purchase('1415550100', 'US');
        // There's nothing to assert here as we don't do anything with the response.
        // If there's no exception thrown, everything is fine!
    }

    public function testSearchOwnedNumbersWithFilter(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://rest.nexmo.com/account/numbers?size=10&index=1&application_id=66c04cea-68b2-45e4-9061-3fd847d627b8&page_index=1',
                $uriString
            );

            $this->assertEquals('GET', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('owned-numbers'));

        $filter = new \Vonage\Numbers\Filter\OwnedNumbers();
        $filter->setApplicationId("66c04cea-68b2-45e4-9061-3fd847d627b8");

        $response = $this->numberClient->searchOwned(null, $filter);
    }

    public function testPurchaseNumberWithNumberAndCountry(): void
    {
        // When providing a number string, the first thing that happens is a GET request to fetch number details
        $this->vonageClient->send(
            Argument::that(fn (RequestInterface $request) => $request->getUri()->getPath() === '/account/numbers')
        )
            ->willReturn($this->getResponse('single'));

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

        $this->numberClient->purchase('1415550100', 'US');

        // There's nothing to assert here as we don't do anything with the response.
        // If there's no exception thrown, everything is fine!
    }

    /**
     * @dataProvider purchaseNumberErrorProvider
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

        $this->numberClient->purchase($number, $country);
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

    public function testCancelNumberWithNumberString(): void
    {
        // When providing a number string, the first thing that happens is a GET request to fetch number details
        $this->vonageClient->send(
            Argument::that(fn (RequestInterface $request) => $request->getUri()->getPath() === '/account/numbers')
        )
            ->willReturn($this->getResponse('single'));

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

    public function testCancelNumberWithNumberAndCountryString(): void
    {
        // When providing a number string, the first thing that happens is a GET request to fetch number details
        $this->vonageClient->send(
            Argument::that(fn (RequestInterface $request) => $request->getUri()->getPath() === '/account/numbers')
        )
            ->willReturn($this->getResponse('single'));

        // Then we get a POST request to cancel
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            if ($request->getUri()->getPath() === '/number/cancel') {
                $this->assertEquals('rest.nexmo.com', $request->getUri()->getHost());
                $this->assertEquals('POST', $request->getMethod());

                return true;
            }
            return false;
        }))->willReturn($this->getResponse('cancel'));

        $this->numberClient->cancel('1415550100', 'US');
    }

    /**
     * Make sure that integer values that fail validation throw properly
     */
    public function testInvalidIntegerValueForSearchThrowsException(): void
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Invalid value: 'size' must be an integer");

        $this->numberClient->searchOwned(new OwnedNumbers(['size' => 'bob']), null);
    }

    /**
     * Make sure that boolean values that fail validation throw properly
     */
    public function testInvalidBooleanValueForSearchThrowsException(): void
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Invalid value: 'has_application' must be a boolean value");

        $this->numberClient->searchOwned(new OwnedNumbers(['has_application' => 'bob']));
    }
}
