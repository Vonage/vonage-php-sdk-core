<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Message;

use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Message\Message;
use Vonage\Message\Text;

use function array_diff;
use function array_keys;
use function json_encode;

class MessageCreationTest extends TestCase
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
     * Creating a new message, should result in the correct (matching) parameters.
     *
     * @throws ClientException
     */
    public function testRequiredParams(): void
    {
        $params = @$this->message->getRequestData();

        $this->assertEquals($this->to, $params['to']);
        $this->assertEquals($this->from, $params['from']);
    }

    /**
     * Optional params shouldn't be in the response, unless set.
     *
     * @throws ClientException
     */
    public function testNoDefaultParams(): void
    {
        $params = array_keys(@$this->message->getRequestData());
        $diff = array_diff($params, $this->set); // should be no difference

        $this->assertEmpty($diff, 'message params contain unset values (could change default behaviour)');
    }

    /**
     * Common optional params can be set
     *
     * @dataProvider optionalParams
     *
     * @param $setter
     * @param $param
     * @param $values
     *
     * @throws ClientException
     */
    public function testOptionalParams($setter, $param, $values): void
    {
        //check no default value
        $params = @$this->message->getRequestData();

        $this->assertArrayNotHasKey($param, $params);

        //test values
        foreach ($values as $value => $expected) {
            $this->message->$setter($value);
            $params = @$this->message->getRequestData();

            $this->assertArrayHasKey($param, $params);
            $this->assertEquals($expected, $params[$param]);
        }
    }

    /**
     * @return array[]
     */
    public function optionalParams(): array
    {
        return [
            ['requestDLR', 'status-report-req', [true => 1, false => 0]],
            ['setClientRef', 'client-ref', ['test' => 'test']],
            ['setCallback', 'callback', ['http://example.com/test-callback' => 'http://example.com/test-callback']],
            ['setNetwork', 'network-code', ['test' => 'test']],
            ['setTTL', 'ttl', ['1' => 1]],
            ['setClass', 'message-class', [Text::CLASS_FLASH => Text::CLASS_FLASH]],
        ];
    }

    /**
     * Returns a series of methods/args to test on a Message object
     */
    public static function responseMethodChangeList(): array
    {
        return [
            ['requestDLR', true],
            ['setCallback', 'https://example.com/changed'],
            ['setClientRef', 'my-personal-message'],
            ['setNetwork', '1234'],
            ['setTTL', 3600],
            ['setClass', 0],
        ];
    }

    /**
     * Throw an exception when we make a call on a method that cannot change after request
     *
     * @dataProvider responseMethodChangeList
     *
     * @param $method
     * @param $argument
     */
    public function testCanNotChangeCreationAfterResponse($method, $argument): void
    {
        $this->expectException('RuntimeException');

        $data = ['test' => 'test'];
        $response = new Response();
        $response->getBody()->write(json_encode($data));

        @$this->message->setResponse($response);
        $this->message->$method($argument);
    }
}
