<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Account\Application;


use Nexmo\Account\Application\Application;
use Nexmo\Account\Application\VoiceConfig;
use Zend\Diactoros\Response;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Application
     */
    protected $app;
    
    public function setUp()
    {
        $this->app = new Application('test');
    }

    public function testNameIsSet()
    {
        $this->assertEquals('test', $this->app->getRequestData()['name']);
    }

    public function testAllAppsAreVoice()
    {
        $this->assertEquals('voice', $this->app->getRequestData()['type']);
    }

    public function testVoiceWebhookParams()
    {
        $this->app->getVoiceConfig()->setWebhook(VoiceConfig::EVENT, 'http://example.com/event');
        $this->app->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, 'http://example.com/answer');

        $params = $this->app->getRequestData();
        $this->assertArrayHasKey('event_url', $params);
        $this->assertArrayHasKey('answer_url', $params);

        $this->assertEquals('http://example.com/event', $params['event_url']);
        $this->assertEquals('http://example.com/answer', $params['answer_url']);
    }

    public function testResponseSetsProperties()
    {
        $this->app->setResponse($this->getResponse());
        $this->assertEquals('client_test', $this->app->getName());
        $this->assertEquals('public_key', $this->app->getPublicKey());
        $this->assertEquals('private_key', $this->app->getPrivateKey());
    }

    public function testResponseSetsConfigs()
    {
        $this->app->setResponse($this->getResponse());

        $webhook = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER);
        $method  = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER)->getMethod();
        $this->assertEquals('http://test.runscope.net/answer', $webhook);
        $this->assertEquals('GET', $method);

        $webhook = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::EVENT);
        $method  = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::EVENT)->getMethod();
        $this->assertEquals('http://test.runscope.net/event', $webhook);
        $this->assertEquals('POST', $method);
    }

    public function testCanGetDirtyValues()
    {
        $this->app->setResponse($this->getResponse());
        $this->assertEquals('client_test', $this->app->getName());
        $this->app->setName('new');
        $this->assertEquals('new', $this->app->getName());
        //$this->assertTrue($this->app->isDirty());
        //$this->assertEquals('client_test', $this->app->getName(true));

        $webhook = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER);
        $this->assertEquals('http://test.runscope.net/answer', (string) $webhook);

        $this->app->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, 'http://example.com');
        $webhook = $this->app->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER);
        $this->assertEquals('http://example.com', (string) $webhook);

        //$webhook = $this->app->getVoiceConfig(true)->getWebhook(VoiceConfig::ANSWER);
        //$this->assertEquals('http://test.runscope.net/answer', (string) $webhook);
    }

    public function testConfigCanBeCopied()
    {
        $this->app->setResponse($this->getResponse());

        $otherapp = new Application('new app');
        $otherapp->setVoiceConfig($this->app->getVoiceConfig());

        $webhook = $otherapp->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER);
        $this->assertEquals('http://test.runscope.net/answer', $webhook);
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