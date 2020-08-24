<?php
namespace VonageTest;

use GuzzleHttp\Psr7\Response;
use Vonage\Client\Exception\Exception;
use Vonage\Client\Exception\Request;
use Vonage\Client\Exception\Server;
use Vonage\Client\Exception\Validation;
use PHPUnit\Framework\TestCase;
use Vonage\ApiErrorHandler;

class ApiErrorHandlerTest extends TestCase
{
    /**
     * Valid HTTP responses do not throw an error
     * There is not a good way to test for an exception _not_ being thrown,
     * but this method has the side effect of returning NULL when everything
     * is OK.
     */
    public function testDoesNotThrowOnSuccess()
    {
        $response = ApiErrorHandler::check(['success' => true], 200);

        $this->assertNull($response);
    }

    public function testThrowsOn4xx()
    {
       $this->expectException(Request::class);
       $this->expectExceptionMessage('Maximum number of flibbets met. See http://example.com/error for more information');
       ApiErrorHandler::check(['type' => 'http://example.com/error', 'title' => 'Maximum number of flibbets met'], 403);
    }

    public function testThrowsOn4xxWithDetail()
    {
       $this->expectException(Request::class);
       $this->expectExceptionMessage('Maximum number of flibbets met: Only allowed 3. See http://example.com/error for more information');
       ApiErrorHandler::check(['type' => 'http://example.com/error', 'title' => 'Maximum number of flibbets met', 'detail' => 'Only allowed 3'], 403);
    }

    public function testThrowsOn400WithValidationErrors()
    {
       try {
           ApiErrorHandler::check([
               'type' => 'http://example.com/error',
               'title' => 'Bad Request',
               'detail' => 'The request failed due to validation errors',
               'invalid_parameters' => [
                   [
                       "name" => "primary_colour",
                       "reason" => "Must be one of: blue, red, yellow"
                   ]
               ]
           ], 400);
       } catch (Validation $e) {
           $this->assertInstanceOf(Validation::class, $e);
           $this->assertEquals('Bad Request: The request failed due to validation errors. See http://example.com/error for more information', $e->getMessage());
           $this->assertEquals([
               [
                   "name" => "primary_colour",
                   "reason" => "Must be one of: blue, red, yellow"
               ]
           ], $e->getValidationErrors());
       }
    }

    public function testThrowsOn5xx()
    {
       $this->expectException(Server::class);
       $this->expectExceptionMessage('Server Error. See http://example.com/error for more information');
       ApiErrorHandler::check(['type' => 'http://example.com/error', 'title' => 'Server Error'], 500);
    }

    public function testThrowsOn5xxWithDetail()
    {
       $this->expectException(Server::class);
       $this->expectExceptionMessage('Server Error: More Information. See http://example.com/error for more information');
       ApiErrorHandler::check(['type' => 'http://example.com/error', 'title' => 'Server Error', 'detail' => 'More Information'], 500);
    }
}
