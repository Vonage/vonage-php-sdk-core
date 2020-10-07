<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Test\SMS;

use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ResponseFactory;
use PHPUnit\Framework\TestCase;
use Vonage\Client\Exception\Request as ExceptionRequest;
use Vonage\Client\Exception\Server;
use Vonage\Client\Exception\ThrottleException;
use Vonage\SMS\ExceptionErrorHandler;

class ExceptionErrorHandlerTest extends TestCase
{
    /**
     * @throws ExceptionRequest
     * @throws ThrottleException
     * @throws Server
     */
    public function test429ThrowsThrottleException(): void
    {
        $this->expectException(ThrottleException::class);
        $this->expectExceptionMessage('Too many concurrent requests');

        $response = (new ResponseFactory())
            ->createResponse(429);

        $handler = new ExceptionErrorHandler();
        $handler($response, new Request());
    }

    /**
     * @throws ExceptionRequest
     * @throws Server
     * @throws ThrottleException
     */
    public function testGenericErrorThrowsRequestException(): void
    {
        $this->expectException(ExceptionRequest::class);
        $this->expectExceptionMessage('This is a generic error being thrown');
        $this->expectExceptionCode(499);

        $response = new JsonResponse([
            'error-code' => 499,
            'error-code-label' => 'This is a generic error being thrown'
        ]);

        $handler = new ExceptionErrorHandler();
        $handler($response, new Request());
    }
}
