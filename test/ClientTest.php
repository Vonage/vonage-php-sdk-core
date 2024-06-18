<?php

declare(strict_types=1);

namespace VonageTest;

use RuntimeException;
use VonageTest\VonageTestCase;
use Vonage\Client;
use Vonage\Client\Credentials\Basic;

class ClientTest extends VonageTestCase
{
    /**
     * Make sure that when calling the video module it errors if the class isn't found
     */
    public function testCallingVideoWithoutPackageGeneratesRuntimeError(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Please install @vonage/video to use the Video API');

        $client = new Client(new Basic('abcd', '1234'));
        $video = $client->video();
    }
}
