<?php

declare(strict_types=1);

namespace VonageTest\Application;

use Vonage\Application\Application;
use Vonage\Application\MessagesConfig;
use Vonage\Application\RtcConfig;
use Vonage\Application\VoiceConfig;
use Vonage\Application\Webhook;
use VonageTest\VonageTestCase;

use function json_decode;
use function file_get_contents;

class ApplicationTest extends VonageTestCase
{
    protected Application $app;

    protected string $responsesDirectory;

    public function setUp(): void
    {
        $this->responsesDirectory = __DIR__ . '/responses';

        $this->app = (new Application())->setName('test');
    }

    protected function getResponseData(string $type = 'success'): array
    {
        return json_decode(
            file_get_contents($this->responsesDirectory . '/' . $type . '.json'),
            true
        );
    }

    public function testConstructWithId(): void
    {
        $app = new Application('1a20a124-1775-412b-b623-e6985f4aace0');

        $this->assertEquals('1a20a124-1775-412b-b623-e6985f4aace0', $app->getId());
    }

    public function testConstructWithIdAndData(): void
    {
        $data = $this->getResponseData();
        $app = new Application($data['id'], $data);

        $this->assertEquals('78d335fa323d01149c3dd6f0d48968cf', $app->getId());
        $this->assertEquals('My Application', $app->getName());
    }

    public function testNameIsSet(): void
    {
        $this->assertEquals('test', $this->app->getName());
    }

    public function testVoiceWebhookParams(): void
    {
        $this->app->getVoiceConfig()->setWebhook(VoiceConfig::EVENT, new Webhook('http://example.com/event', 'POST'));
        $this->app->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, new Webhook('http://example.com/answer', 'GET'));

        $webhook = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::EVENT);
        $this->assertEquals('http://example.com/event', $webhook->getUrl());

        $webhook = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER);
        $this->assertEquals('http://example.com/answer', $webhook->getUrl());
    }

    public function testFromArraySetsProperties(): void
    {
        $this->app->fromArray($this->getResponseData());

        $this->assertEquals('My Application', $this->app->getName());
        $this->assertEquals(
            "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCA\nKOxjsU4pf/sMFi9N0jqcSLcjxu33G\nd/vynKnlw9SENi" .
            "+UZR44GdjGdmfm1\ntL1eA7IBh2HNnkYXnAwYzKJoa4eO3\n0kYWekeIZawIwe/g9faFgkev+1xsO\nOUNhPx2LhuLmgwWSRS4L5W851" .
            "Xe3f\nUQIDAQAB\n-----END PUBLIC KEY-----\n",
            $this->app->getPublicKey()
        );
        $this->assertEquals('private_key', $this->app->getPrivateKey());
    }

    public function testFromArraySetsVoiceConfigs(): void
    {
        $this->app->fromArray($this->getResponseData());

        $webhook = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER);
        $method = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER)->getMethod();
        $this->assertEquals('https://example.com/webhooks/answer', $webhook);
        $this->assertEquals('GET', $method);

        $webhook = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::EVENT);
        $method = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::EVENT)->getMethod();
        $this->assertEquals('https://example.com/webhooks/event', $webhook);
        $this->assertEquals('POST', $method);
    }

    public function testFromArraySetsMessagesConfigs(): void
    {
        $this->app->fromArray($this->getResponseData());

        $webhook = $this->app->getMessagesConfig()->getWebhook(MessagesConfig::INBOUND);
        $method = $this->app->getMessagesConfig()->getWebhook(MessagesConfig::INBOUND)->getMethod();
        $this->assertEquals('https://example.com/webhooks/inbound', $webhook);
        $this->assertEquals('POST', $method);

        $webhook = $this->app->getMessagesConfig()->getWebhook(MessagesConfig::STATUS);
        $method = $this->app->getMessagesConfig()->getWebhook(MessagesConfig::STATUS)->getMethod();
        $this->assertEquals('https://example.com/webhooks/status', $webhook);
        $this->assertEquals('POST', $method);
    }

    public function testFromArraySetsRtcConfigs(): void
    {
        $this->app->fromArray($this->getResponseData());

        $webhook = $this->app->getRtcConfig()->getWebhook(RtcConfig::EVENT);
        $method = $this->app->getRtcConfig()->getWebhook(RtcConfig::EVENT)->getMethod();
        $this->assertEquals('https://example.com/webhooks/event', $webhook);
        $this->assertEquals('POST', $method);
    }

    public function testFromArraySetsVbcConfigs(): void
    {
        $this->app->fromArray($this->getResponseData());
        $this->assertEquals(true, $this->app->getVbcConfig()->isEnabled());
    }

    public function testCanGetDirtyValues(): void
    {
        $this->app->fromArray($this->getResponseData());
        $this->assertEquals('My Application', $this->app->getName());

        $this->app->setName('new');
        $this->assertEquals('new', $this->app->getName());

        $webhook = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER);
        $this->assertEquals('https://example.com/webhooks/answer', $webhook);

        $this->app->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, new Webhook('http://example.com', 'GET'));
        $webhook = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER);
        $this->assertEquals('http://example.com', (string)$webhook);
    }

    public function testConfigCanBeCopied(): void
    {
        $this->app->fromArray($this->getResponseData());

        $otherapp = new Application();
        $otherapp->setName('new app');

        $otherapp->setVoiceConfig($this->app->getVoiceConfig());

        $webhook = $otherapp->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER);
        $this->assertEquals('https://example.com/webhooks/answer', $webhook);
    }

    public function testCanSetFallbackUrlWebhook(): void
    {
        $application = new Application();
        $application->setName('my application');
        $application->getVoiceConfig()->setRegion('eu-west');

        $webhook = new Webhook('https://example.com/fallbackUrl', 'GET');
        $application->getVoiceConfig()->setWebhook(\Vonage\Application\VoiceConfig::FALLBACK_ANSWER_URL, $webhook);

        $output = $application->toArray();
        $this->assertEquals('https://example.com/fallbackUrl', $output['capabilities']['voice']['webhooks']['fallback_answer_url']['address']);
    }
}
