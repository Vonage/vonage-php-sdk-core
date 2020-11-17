<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace VonageTest\Verify;

use Laminas\Diactoros\Request;
use PHPUnit\Framework\TestCase;
use Vonage\Entity\Psr7Trait;
use Laminas\Diactoros\Response;
use Vonage\Client\Exception\Request as ExceptionRequest;
use Vonage\Verify\ExceptionErrorHandler;

class ExceptionErrorHandlerTest extends TestCase
{
    use Psr7Trait;

    public function testServerExceptionThrowOnError()
    {
        $this->expectException(ExceptionRequest::class);

        $handler = new ExceptionErrorHandler();
        $handler->__invoke($this->getResponse('start-error'), new Request());
    }

    public function testNoExceptionThrowOnValidResponse()
    {
        $handler = new ExceptionErrorHandler();
        $this->assertNull($handler->__invoke($this->getResponse('start'), new Request()));
    }

    /**
     * Get the API response we'd expect for a call to the API. Verify API currently returns 200 all the time, so only
     * change between success / fail is body of the message.
     */
    protected function getResponse(string $type = 'success'): Response
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'));
    }
}