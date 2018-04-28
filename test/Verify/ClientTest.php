<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Verify;


use Nexmo\Verify\Client;
use Nexmo\Verify\Verification;
use NexmoTest\Psr7AssertionTrait;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Response;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

    /**
     * @var Client
     */
    protected $client;

    protected $nexmoClient;

    /**
     * Create the Message API Client, and mock the Nexmo Client
     */
    public function setUp()
    {
        $this->nexmoClient = $this->prophesize('Nexmo\Client');
        $this->nexmoClient->getApiUrl()->willReturn('https://api.nexmo.com');
        $this->client = new Client();
        $this->client->setClient($this->nexmoClient->reveal());
    }

    /**
     * @dataProvider getApiMethods
     */
    public function testClientSetsSelf($method, $response, $construct, $args = [])
    {
        $client = $this->prophesize('Nexmo\Client');
        $client->send(Argument::cetera())->willReturn($this->getResponse($response));
        $client->getApiUrl()->willReturn('http://api.nexmo.com');

        $this->client->setClient($client->reveal());

        $mock = $this->getMockBuilder('Nexmo\Verify\Verification')
                     ->setConstructorArgs($construct)
                     ->setMethods(['setClient'])
                     ->getMock();

        $mock->expects($this->once())->method('setClient')->with($this->client);

        array_unshift($args, $mock);
        call_user_func_array([$this->client, $method], $args);
    }

    public function getApiMethods()
    {
        return [
            ['start',   'start',   ['14845551212', 'Test Verify']],
            ['cancel',  'cancel',  ['44a5279b27dd4a638d614d265ad57a77']],
            ['trigger', 'trigger', ['44a5279b27dd4a638d614d265ad57a77']],
            ['search',  'search',  ['44a5279b27dd4a638d614d265ad57a77']],
            ['check',   'check',   ['44a5279b27dd4a638d614d265ad57a77'], ['1234']],
        ];
    }

    public function testUnserializeAcceptsObject()
    {
        $mock = $this->getMockBuilder('Nexmo\Verify\Verification')
            ->setConstructorArgs(['14845551212', 'Test Verify'])
            ->setMethods(['setClient'])
            ->getMock();

        $mock->expects($this->once())->method('setClient')->with($this->client);

        $this->client->unserialize($mock);
    }

    public function testUnserializeSetsClient()
    {
        $verification = new Verification('14845551212', 'Test Verify');
        $verification->setResponse($this->getResponse('start'));

        $string = serialize($verification);
        $object = $this->client->unserialize($string);

        $this->assertInstanceOf('Nexmo\Verify\Verification', $object);

        $search = $this->setupClientForSearch('search');
        $object->sync();
        $this->assertSame($search, $object->getResponse());
    }

    public function testSerializeMatchesEntity()
    {
        $verification = new Verification('14845551212', 'Test Verify');
        $verification->setResponse($this->getResponse('start'));

        $string = serialize($verification);
        $this->assertSame($string, $this->client->serialize($verification));
    }

    public function testCanStartVerification()
    {
        $success = $this->setupClientForStart('start');

        $verification = new Verification('14845551212', 'Test Verify');
        $this->client->start($verification);
        $this->assertSame($success, $verification->getResponse());
    }

    public function testCanStartArray()
    {
        $response = $this->setupClientForStart('start');

        $verification = $this->client->start([
            'number' => '14845551212',
            'brand'  => 'Test Verify'
        ]);

        $this->assertSame($response, $verification->getResponse());
    }

    public function testStartThrowsException()
    {
        $response = $this->setupClientForStart('start-error');

        try {
            $this->client->start([
                'number' => '14845551212',
                'brand'  => 'Test Verify'
            ]);
            $this->fail('did not throw exception');
        } catch (\Nexmo\Client\Exception\Request $e) {
            $this->assertEquals('2', $e->getCode());
            $this->assertEquals('Your request is incomplete and missing the mandatory parameter: brand', $e->getMessage());
            $this->assertSame($response, $e->getEntity()->getResponse());
        }
    }

    public function testStartThrowsServerException()
    {
        $response = $this->setupClientForStart('server-error');

        try {
            $this->client->start([
                'number' => '14845551212',
                'brand'  => 'Test Verify'
            ]);
            $this->fail('did not throw exception');
        } catch (\Nexmo\Client\Exception\Server $e) {
            $this->assertEquals('5', $e->getCode());
            $this->assertEquals('Server Error', $e->getMessage());
            $this->assertSame($response, $e->getEntity()->getResponse());
        }
    }

    protected function setupClientForStart($response)
    {
        $response = $this->getResponse($response);
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) {
            $this->assertRequestJsonBodyContains('number', '14845551212', $request);
            $this->assertRequestJsonBodyContains('brand', 'Test Verify', $request);
            $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/json', $request);
            return true;
        }))->willReturn($response)
           ->shouldBeCalledTimes(1);

        return $response;
    }

    public function testCanSearchVerification()
    {
        $response = $this->setupClientForSearch('search');

        $verification = new Verification('44a5279b27dd4a638d614d265ad57a77');
        $this->client->search($verification);

        $this->assertSame($response, $verification->getResponse());
    }

    public function testCanSearchId()
    {
        $response = $this->setupClientForSearch('search');

        $verification = $this->client->search('44a5279b27dd4a638d614d265ad57a77');

        $this->assertSame($response, $verification->getResponse());
    }

    public function testSearchThrowsException()
    {
        $response = $this->setupClientForSearch('search-error');

        try {
            $this->client->search('44a5279b27dd4a638d614d265ad57a77');
            $this->fail('did not throw exception');
        } catch (\Nexmo\Client\Exception\Request $e) {
            $this->assertEquals('101', $e->getCode());
            $this->assertEquals('No response found', $e->getMessage());
            $this->assertSame($response, $e->getEntity()->getResponse());
        }
    }

    public function testSearchThrowsServerException()
    {
        $response = $this->setupClientForSearch('server-error');

        try {
            $this->client->search('44a5279b27dd4a638d614d265ad57a77');
            $this->fail('did not throw exception');
        } catch (\Nexmo\Client\Exception\Server $e) {
            $this->assertEquals('5', $e->getCode());
            $this->assertEquals('Server Error', $e->getMessage());
            $this->assertSame($response, $e->getEntity()->getResponse());
        }
    }

    public function testSearchReplacesResponse()
    {
        $old = $this->getResponse('start');
        $verification = new Verification('14845551212', 'Test Verify');
        $verification->setResponse($old);

        $response = $this->setupClientForSearch('search');
        $this->client->search($verification);

        $this->assertSame($response, $verification->getResponse());
    }

    protected function setupClientForSearch($response)
    {
        $response = $this->getResponse($response);
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) {
            $this->assertRequestJsonBodyContains('request_id', '44a5279b27dd4a638d614d265ad57a77', $request);
            $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/search/json', $request);
            return true;
        }))->willReturn($response)
            ->shouldBeCalledTimes(1);

        return $response;
    }

    public function testCanCancelVerification()
    {
        $response = $this->setupClientForControl('cancel', 'cancel');

        $verification = new Verification('44a5279b27dd4a638d614d265ad57a77');
        $result = $this->client->cancel($verification);

        $this->assertSame($verification, $result);
        $this->assertSame($response, $verification->getResponse());
    }

    public function testCanCancelId()
    {
        $response = $this->setupClientForControl('cancel', 'cancel');

        $verification = $this->client->cancel('44a5279b27dd4a638d614d265ad57a77');

        $this->assertSame($response, $verification->getResponse());
    }

    public function testCancelThrowsClientException()
    {
        $response = $this->setupClientForControl('cancel-error', 'cancel');

        try {
            $this->client->cancel('44a5279b27dd4a638d614d265ad57a77');
            $this->fail('did not throw exception');
        } catch (\Nexmo\Client\Exception\Request $e) {
            $this->assertEquals('19', $e->getCode());
            $this->assertEquals('Verification request  [\'c1878c7451f94c1992d52797df57658e\'] can\'t be cancelled now. Too many attempts to re-deliver have already been made.', $e->getMessage());
            $this->assertSame($response, $e->getEntity()->getResponse());
        }
    }

    public function testCancelThrowsServerException()
    {
        $response = $this->setupClientForControl('server-error', 'cancel');

        try {
            $this->client->cancel('44a5279b27dd4a638d614d265ad57a77');
            $this->fail('did not throw exception');
        } catch (\Nexmo\Client\Exception\Server $e) {
            $this->assertEquals('5', $e->getCode());
            $this->assertEquals('Server Error', $e->getMessage());
            $this->assertSame($response, $e->getEntity()->getResponse());
        }
    }

    public function testCanTriggerId()
    {
        $response = $this->setupClientForControl('trigger', 'trigger_next_event');

        $verification = $this->client->trigger('44a5279b27dd4a638d614d265ad57a77');

        $this->assertSame($response, $verification->getResponse());
    }

    public function testCanTriggerVerification()
    {
        $response = $this->setupClientForControl('trigger', 'trigger_next_event');

        $verification = new Verification('44a5279b27dd4a638d614d265ad57a77');
        $result = $this->client->trigger($verification);

        $this->assertSame($verification, $result);
        $this->assertSame($response, $verification->getResponse());
    }

    public function testTriggerThrowsClientException()
    {
        $response = $this->setupClientForControl('trigger-error', 'trigger_next_event');

        try {
            $this->client->trigger('44a5279b27dd4a638d614d265ad57a77');
            $this->fail('did not throw exception');
        } catch (\Nexmo\Client\Exception\Request $e) {
            $this->assertEquals('6', $e->getCode());
            $this->assertEquals('The requestId \'44a5279b27dd4a638d614d265ad57a77\' does not exist or its no longer active.', $e->getMessage());
            $this->assertSame($response, $e->getEntity()->getResponse());
        }
    }

    public function testTriggerThrowsServerException()
    {
        $response = $this->setupClientForControl('server-error', 'trigger_next_event');

        try {
            $this->client->trigger('44a5279b27dd4a638d614d265ad57a77');
            $this->fail('did not throw exception');
        } catch (\Nexmo\Client\Exception\Server $e) {
            $this->assertEquals('5', $e->getCode());
            $this->assertEquals('Server Error', $e->getMessage());
            $this->assertSame($response, $e->getEntity()->getResponse());
        }
    }

    /**
     * @dataProvider getControlCommands
     */
    public function testControlNotReplaceResponse($method, $cmd)
    {
        $response = $this->getResponse('search');
        $verification = new Verification('44a5279b27dd4a638d614d265ad57a77');
        $verification->setResponse($response);

        $this->setupClientForControl($method, $cmd);
        $this->client->$method($verification);

        $this->assertSame($response, $verification->getResponse());
    }

    public function getControlCommands()
    {
        return [
            ['cancel', 'cancel'],
            ['trigger', 'trigger_next_event']
        ];
    }    
    
    protected function setupClientForControl($response, $cmd)
    {
        $response = $this->getResponse($response);
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($cmd) {
            $this->assertRequestJsonBodyContains('request_id', '44a5279b27dd4a638d614d265ad57a77', $request);
            $this->assertRequestJsonBodyContains('cmd', $cmd, $request);
            $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/control/json', $request);
            return true;
        }))->willReturn($response)
            ->shouldBeCalledTimes(1);

        return $response;
    }

    public function testCanCheckVerification()
    {
        $response = $this->setupClientForCheck('check', '1234');
        $verification = new Verification('44a5279b27dd4a638d614d265ad57a77');
        
        $this->client->check($verification, '1234');
        
        $this->assertSame($response, $verification->getResponse());
    }

    public function testCanCheckId()
    {
        $response = $this->setupClientForCheck('check', '1234');
        $verification = $this->client->check('44a5279b27dd4a638d614d265ad57a77', '1234');

        $this->assertSame($response, $verification->getResponse());
    }


    public function testCheckThrowsClientException()
    {
        $response = $this->setupClientForCheck('check-error', '1234');

        try {
            $this->client->check('44a5279b27dd4a638d614d265ad57a77', '1234');
            $this->fail('did not throw exception');
        } catch (\Nexmo\Client\Exception\Request $e) {
            $this->assertEquals('16', $e->getCode());
            $this->assertEquals('The code provided does not match the expected value', $e->getMessage());
            $this->assertSame($response, $e->getEntity()->getResponse());
        }
    }

    public function testCheckThrowsServerException()
    {
        $response = $this->setupClientForCheck('server-error', '1234');

        try {
            $this->client->check('44a5279b27dd4a638d614d265ad57a77', '1234');
            $this->fail('did not throw exception');
        } catch (\Nexmo\Client\Exception\Server $e) {
            $this->assertEquals('5', $e->getCode());
            $this->assertEquals('Server Error', $e->getMessage());
            $this->assertSame($response, $e->getEntity()->getResponse());
        }
    }

    public function testCheckNotReplaceResponse()
    {
        $old = $this->getResponse('search');
        $verification = new Verification('44a5279b27dd4a638d614d265ad57a77');
        $verification->setResponse($old);

        $this->setupClientForCheck('check', '1234');

        $this->client->check($verification, '1234');
        $this->assertSame($old, $verification->getResponse());
    }
    
    protected function setupClientForCheck($response, $code, $ip = null)
    {
        $response = $this->getResponse($response);
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($code, $ip) {
            $this->assertRequestJsonBodyContains('request_id', '44a5279b27dd4a638d614d265ad57a77', $request);
            $this->assertRequestJsonBodyContains('code', $code, $request);

            if($ip){
                $this->assertRequestJsonBodyContains('ip_address', $ip, $request);
            }

            $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/check/json', $request);
            return true;
        }))->willReturn($response)
            ->shouldBeCalledTimes(1);

        return $response;
    }

    /**
     * Get the API response we'd expect for a call to the API. Verify API currently returns 200 all the time, so only
     * change between success / fail is body of the message.
     *
     * @param string $type
     * @return Response
     */
    protected function getResponse($type = 'success')
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'r'));
    }
}
