<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Application;

use Exception;
use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use Vonage\Application\Application;
use Vonage\Application\MessagesConfig;
use Vonage\Application\RtcConfig;
use Vonage\Application\VoiceConfig;
use Vonage\Client\Exception\Exception as ClientException;

use function fopen;

class ApplicationTest extends TestCase
{
    /**
     * @var Application
     */
    protected $app;

    public function setUp(): void
    {
        $this->app = (new Application())->setName('test');
    }

    public function testConstructWithId(): void
    {
        $app = new Application('1a20a124-1775-412b-b623-e6985f4aace0');

        self::assertEquals('1a20a124-1775-412b-b623-e6985f4aace0', $app->getId());
    }

    /**
     * @throws ClientException
     */
    public function testNameIsSet(): void
    {
        self::assertEquals('test', @$this->app->getRequestData()['name']);
    }

    /**
     * @throws Exception
     */
    public function testVoiceWebhookParams(): void
    {
        @$this->app->getVoiceConfig()->setWebhook(VoiceConfig::EVENT, 'http://example.com/event');
        @$this->app->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, 'http://example.com/answer');

        $params = @$this->app->getRequestData();
        $capabilities = $params['capabilities'];

        self::assertArrayHasKey('event_url', $capabilities['voice']['webhooks']);
        self::assertArrayHasKey('answer_url', $capabilities['voice']['webhooks']);
        self::assertEquals('http://example.com/event', $capabilities['voice']['webhooks']['event_url']['address']);
        self::assertEquals('http://example.com/answer', $capabilities['voice']['webhooks']['answer_url']['address']);
    }

    public function testResponseSetsProperties(): void
    {
        @$this->app->setResponse($this->getResponse());

        self::assertEquals('My Application', $this->app->getName());
        self::assertEquals(
            "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCA\nKOxjsU4pf/sMFi9N0jqcSLcjxu33G\nd/vynKnlw9SENi" .
            "+UZR44GdjGdmfm1\ntL1eA7IBh2HNnkYXnAwYzKJoa4eO3\n0kYWekeIZawIwe/g9faFgkev+1xsO\nOUNhPx2LhuLmgwWSRS4L5W851" .
            "Xe3f\nUQIDAQAB\n-----END PUBLIC KEY-----\n",
            $this->app->getPublicKey()
        );
        self::assertEquals('private_key', $this->app->getPrivateKey());
    }

    /**
     * @throws Exception
     */
    public function testResponseSetsVoiceConfigs(): void
    {
        @$this->app->setResponse($this->getResponse());

        $webhook = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER);
        $method = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER)->getMethod();
        self::assertEquals('https://example.com/webhooks/answer', $webhook);
        self::assertEquals('GET', $method);

        $webhook = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::EVENT);
        $method = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::EVENT)->getMethod();
        self::assertEquals('https://example.com/webhooks/event', $webhook);
        self::assertEquals('POST', $method);
    }

    /**
     * @throws Exception
     */
    public function testResponseSetsMessagesConfigs(): void
    {
        @$this->app->setResponse($this->getResponse());

        $webhook = $this->app->getMessagesConfig()->getWebhook(MessagesConfig::INBOUND);
        $method = $this->app->getMessagesConfig()->getWebhook(MessagesConfig::INBOUND)->getMethod();
        self::assertEquals('https://example.com/webhooks/inbound', $webhook);
        self::assertEquals('POST', $method);

        $webhook = $this->app->getMessagesConfig()->getWebhook(MessagesConfig::STATUS);
        $method = $this->app->getMessagesConfig()->getWebhook(MessagesConfig::STATUS)->getMethod();
        self::assertEquals('https://example.com/webhooks/status', $webhook);
        self::assertEquals('POST', $method);
    }

    /**
     * @throws Exception
     */
    public function testResponseSetsRtcConfigs(): void
    {
        @$this->app->setResponse($this->getResponse());

        $webhook = $this->app->getRtcConfig()->getWebhook(RtcConfig::EVENT);
        $method = $this->app->getRtcConfig()->getWebhook(RtcConfig::EVENT)->getMethod();
        self::assertEquals('https://example.com/webhooks/event', $webhook);
        self::assertEquals('POST', $method);
    }

    public function testResponseSetsVbcConfigs(): void
    {
        @$this->app->setResponse($this->getResponse());
        self::assertEquals(true, $this->app->getVbcConfig()->isEnabled());
    }

    /**
     * @throws Exception
     */
    public function testCanGetDirtyValues(): void
    {
        @$this->app->setResponse($this->getResponse());
        self::assertEquals('My Application', $this->app->getName());

        $this->app->setName('new');
        self::assertEquals('new', $this->app->getName());

        $webhook = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER);
        self::assertEquals('https://example.com/webhooks/answer', $webhook);

        @$this->app->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, 'http://example.com');
        $webhook = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER);
        self::assertEquals('http://example.com', (string)$webhook);
    }

    /**
     * @throws Exception
     */
    public function testConfigCanBeCopied(): void
    {
        @$this->app->setResponse($this->getResponse());

        $otherapp = new Application();
        $otherapp->setName('new app');

        $otherapp->setVoiceConfig($this->app->getVoiceConfig());

        $webhook = $otherapp->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER);
        self::assertEquals('https://example.com/webhooks/answer', $webhook);
    }

    /**
     * Get the API response we'd expect for a call to the API.
     *
     * @param string $type
     *
     * @return Response
     */
    protected function getResponse(string $type = 'success'): Response
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'));
    }
}
