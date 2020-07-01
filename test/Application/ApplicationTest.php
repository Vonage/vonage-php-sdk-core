<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Application;

use Zend\Diactoros\Response;
use Nexmo\Application\Webhook;
use PHPUnit\Framework\TestCase;
use Nexmo\Application\RtcConfig;
use Nexmo\Application\Application;
use Nexmo\Application\VoiceConfig;
use Nexmo\Application\MessagesConfig;

class ApplicationTest extends TestCase
{
    /**
     * @var Application
     */
    protected $app;
    
    public function setUp()
    {
        $this->app = new Application();
        $this->app->fromArray(json_decode(file_get_contents(__DIR__ . '/responses/success.json'), true));
    }

    public function testConstructWithId()
    {
        $app = new Application('1a20a124-1775-412b-b623-e6985f4aace0');
        $this->assertEquals('1a20a124-1775-412b-b623-e6985f4aace0', $app->getId());
    }

    public function testNameIsSet()
    {
        $this->assertEquals('My Application', $this->app->getName());
    }

    public function testVoiceWebhookParams()
    {
        @$this->app->getVoiceConfig()->setWebhook(VoiceConfig::EVENT, new Webhook('http://example.com/event'));
        @$this->app->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, new Webhook('http://example.com/answer'));

        $this->assertEquals('http://example.com/event', $this->app->getVoiceConfig()->getWebhook(VoiceConfig::EVENT));
        $this->assertEquals('http://example.com/answer', $this->app->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER));
    }

    public function testResponseSetsProperties()
    {
        $this->assertEquals(
            "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCA\nKOxjsU4pf/sMFi9N0jqcSLcjxu33G\nd/vynKnlw9SENi+UZR44GdjGdmfm1\ntL1eA7IBh2HNnkYXnAwYzKJoa4eO3\n0kYWekeIZawIwe/g9faFgkev+1xsO\nOUNhPx2LhuLmgwWSRS4L5W851Xe3f\nUQIDAQAB\n-----END PUBLIC KEY-----\n",
            $this->app->getPublicKey()
        );
        $this->assertEquals('private_key', $this->app->getPrivateKey());
    }

    public function testResponseSetsVoiceConfigs()
    {
        $webhook = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER);
        $method  = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER)->getMethod();
        $this->assertEquals('https://example.com/webhooks/answer', $webhook);
        $this->assertEquals('GET', $method);

        $webhook = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::EVENT);
        $method  = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::EVENT)->getMethod();
        $this->assertEquals('https://example.com/webhooks/event', $webhook);
        $this->assertEquals('POST', $method);
    }

    public function testResponseSetsMessagesConfigs()
    {
        $webhook = $this->app->getMessagesConfig()->getWebhook(MessagesConfig::INBOUND);
        $method  = $this->app->getMessagesConfig()->getWebhook(MessagesConfig::INBOUND)->getMethod();
        $this->assertEquals('https://example.com/webhooks/inbound', $webhook);
        $this->assertEquals('POST', $method);

        $webhook = $this->app->getMessagesConfig()->getWebhook(MessagesConfig::STATUS);
        $method  = $this->app->getMessagesConfig()->getWebhook(MessagesConfig::STATUS)->getMethod();
        $this->assertEquals('https://example.com/webhooks/status', $webhook);
        $this->assertEquals('POST', $method);
    }

    public function testResponseSetsRtcConfigs()
    {
        $webhook = $this->app->getRtcConfig()->getWebhook(RtcConfig::EVENT);
        $method  = $this->app->getRtcConfig()->getWebhook(RtcConfig::EVENT)->getMethod();
        $this->assertEquals('https://example.com/webhooks/event', $webhook);
        $this->assertEquals('POST', $method);
    }

    public function testResponseSetsVbcConfigs()
    {
        $this->assertEquals(true, $this->app->getVbcConfig()->isEnabled());
    }

    public function testCanGetDirtyValues()
    {
        $this->assertEquals('My Application', $this->app->getName());

        $this->app->setName('new');
        $this->assertEquals('new', $this->app->getName());

        $webhook = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER);
        $this->assertEquals('https://example.com/webhooks/answer', $webhook);

        @$this->app->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, new Webhook('http://example.com'));
        $webhook = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER);
        $this->assertEquals('http://example.com', (string) $webhook);
    }

    public function testConfigCanBeCopied()
    {
        $otherapp = new Application();
        $otherapp->setName('new app');

        $otherapp->setVoiceConfig($this->app->getVoiceConfig());

        $webhook = $otherapp->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER);
        $this->assertEquals('https://example.com/webhooks/answer', $webhook);
    }

    /**
     * Get the API response we'd expect for a call to the API.
     *
     * @param string $type
     * @return Response
     */
    protected function getResponse($type = 'success')
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'r'));
    }
}