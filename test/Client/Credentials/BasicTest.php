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
use Vonage\Client\Credentials\Basic;

class BasicTest extends TestCase
{
    protected $key = 'key';
    protected $secret = 'secret';

    public function testAsArray(): void
    {
        $credentials = new Basic($this->key, $this->secret);
        $array = $credentials->asArray();

        self::assertEquals($this->key, $array['api_key']);
        self::assertEquals($this->secret, $array['api_secret']);
    }

    public function testArrayAccess(): void
    {
        $credentials = new Basic($this->key, $this->secret);

        self::assertEquals($this->key, $credentials['api_key']);
        self::assertEquals($this->secret, $credentials['api_secret']);
    }

    public function testProperties(): void
    {
        $credentials = new Basic($this->key, $this->secret);

        self::assertEquals($this->key, $credentials->api_key);
        self::assertEquals($this->secret, $credentials->api_secret);
    }
}
