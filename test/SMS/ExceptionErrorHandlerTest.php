<?php
declare(strict_types=1);

namespace VonageTest\SMS;

use Vonage\Client\Exception\Request as ExceptionRequest;
use Vonage\Client\Exception\ThrottleException;
use Vonage\SMS\ExceptionErrorHandler;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ResponseFactory;

class ExceptionErrorHandlerTest extends TestCase
{
    public function test429ThrowsThrottleException()
    {
        $this->expectException(ThrottleException::class);
        $this->expectExceptionMessage('Too many concurrent requests');

        $respFactory = new ResponseFactory();
        $response = $respFactory->createResponse(429);

        $handler = new ExceptionErrorHandler();
        $handler($response, new Request());
    }

    public function testGenericErrorThrowsRequestException()
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
