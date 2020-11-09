<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Client\Credentials;

use PHPUnit\Framework\TestCase;
use Vonage\Client\Credentials\OAuth;

class OAuthTest extends TestCase
{
    protected $appToken = 'appToken';
    protected $appSecret = 'appSecret';
    protected $clientToken = 'clientToken';
    protected $clientSecret = 'clientSecret';

    public function testAsArray(): void
    {
        $credentials = new OAuth($this->appToken, $this->appSecret, $this->clientToken, $this->clientSecret);
        $array = $credentials->asArray();

        $this->assertEquals($this->clientToken, $array['token']);
        $this->assertEquals($this->clientSecret, $array['token_secret']);
        $this->assertEquals($this->appToken, $array['consumer_key']);
        $this->assertEquals($this->appSecret, $array['consumer_secret']);
    }

    public function testArrayAccess(): void
    {
        $credentials = new OAuth($this->appToken, $this->appSecret, $this->clientToken, $this->clientSecret);

        $this->assertEquals($this->clientToken, $credentials['token']);
        $this->assertEquals($this->clientSecret, $credentials['token_secret']);
        $this->assertEquals($this->appToken, $credentials['consumer_key']);
        $this->assertEquals($this->appSecret, $credentials['consumer_secret']);
    }

    public function testProperties(): void
    {
        $credentials = new OAuth($this->appToken, $this->appSecret, $this->clientToken, $this->clientSecret);

        $this->assertEquals($this->clientToken, $credentials->token);
        $this->assertEquals($this->clientSecret, $credentials->token_secret);
        $this->assertEquals($this->appToken, $credentials->consumer_key);
        $this->assertEquals($this->appSecret, $credentials->consumer_secret);
    }
}
