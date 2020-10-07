<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Test\Message;

use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use Vonage\Client\Exception\Exception;
use Vonage\Message\Message;
use Vonage\Message\Text;

class MessageTest extends TestCase
{
    protected $to = '14845551212';
    protected $from = '16105551212';
    protected $text = 'this is test text';
    protected $set = ['to', 'from', 'text'];

    /**
     * @var Message
     */
    protected $message;

    public function setUp(): void
    {
        $this->message = new Message($this->to, $this->from, [
            'text' => $this->text
        ]);
    }

    public function tearDown(): void
    {
        $this->message = null;
    }

    /**
     * @throws Exception
     */
    public function testRequestSetsData(): void
    {
        $data = ['test' => 'test'];
        $request = new Request('http://example.com?' . http_build_query($data));
        @$this->message->setRequest($request);

        self::assertSame($request, @$this->message->getRequest());

        $requestData = @$this->message->getRequestData();

        self::assertEquals($data, $requestData);
    }

    /**
     * @throws \Exception
     */
    public function testResponseSetsData(): void
    {
        $data = ['test' => 'test'];
        $response = new Response();
        $response->getBody()->write(json_encode($data));
        $response->getBody()->rewind();

        @$this->message->setResponse($response);

        self::assertSame($response, @$this->message->getResponse());
        self::assertEquals($data, @$this->message->getResponseData());
    }

    /**
     * For getting message data from API, can create a simple object with just an ID.
     *
     * @throws \Exception
     */
    public function testCanCreateWithId(): void
    {
        self::assertEquals('00000123', (new Message('00000123'))->getMessageId());
    }

    /**
     * When creating a message, it should not auto-detect encoding by default
     *
     * @dataProvider messageEncodingProvider
     * @param $msg
     * @throws Exception
     */
    public function testDoesNotAutodetectByDefault($msg): void
    {
        $message = new Text('to', 'from', $msg);

        self::assertFalse($message->isEncodingDetectionEnabled());

        $d = $message->getRequestData(false);

        self::assertEquals('text', $d['type']);
    }

    /**
     * When creating a message, it should not auto-detect encoding by default
     *
     * @dataProvider messageEncodingProvider
     * @param $msg
     * @param $encoding
     * @throws Exception
     */
    public function testDoesAutodetectWhenEnabled($msg, $encoding): void
    {
        $message = new Text('to', 'from', $msg);
        $message->enableEncodingDetection();

        self::assertTrue($message->isEncodingDetectionEnabled());

        $d = $message->getRequestData(false);

        self::assertEquals($d['type'], $encoding);
    }

    /**
     * @return array
     */
    public function messageEncodingProvider(): array
    {
        return [
            'text' => ['Hello World', 'text'],
            'emoji' => ['Testing 💪', 'unicode'],
            'kanji' => ['漢字', 'unicode']
        ];
    }

    /**
     * Get the API response we'd expect for a call to the API. Message API currently returns 200 all the time, so only
     * change between success / fail is body of the message.
     *
     * @param string $type
     * @return Response
     */
    protected function getResponse(string $type = 'success'): Response
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'));
    }
}
