<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\Test;

use Exception;
use PHPUnit\Framework\TestCase;
use Vonage\ApiErrorHandler;
use Vonage\Client\Exception\Request;
use Vonage\Client\Exception\Server;
use Vonage\Client\Exception\Validation;

class ApiErrorHandlerTest extends TestCase
{
    /**
     * Valid HTTP responses do not throw an error
     * There is not a good way to test for an exception _not_ being thrown,
     * but this method has the side effect of returning NULL when everything
     * is OK.
     */
    public function testDoesNotThrowOnSuccess(): void
    {
        /** @noinspection UnnecessaryAssertionInspection */
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        /** @noinspection PhpUnhandledExceptionInspection */
        self::assertNull(ApiErrorHandler::check(['success' => true], 200));
    }

    /**
     * @throws Request
     * @throws Server
     * @throws Validation
     */
    public function testThrowsOn4xx(): void
    {
        $this->expectException(Request::class);
        $this->expectExceptionMessage(
            'Maximum number of flibbets met. See http://example.com/error for more information'
        );

        ApiErrorHandler::check(
            [
                'type' => 'http://example.com/error',
                'title' => 'Maximum number of flibbets met'
            ],
            403
        );
    }

    /**
     * @throws Request
     * @throws Server
     * @throws Validation
     */
    public function testThrowsOn4xxWithDetail(): void
    {
        $this->expectException(Request::class);
        $this->expectExceptionMessage(
            'Maximum number of flibbets met: Only allowed 3. See http://example.com/error for more information'
        );

        ApiErrorHandler::check(
            [
                'type' => 'http://example.com/error',
                'title' => 'Maximum number of flibbets met',
                'detail' => 'Only allowed 3'
            ],
            403
        );
    }

    public function testThrowsOn400WithValidationErrors(): void
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
            self::assertInstanceOf(Validation::class, $e);
            self::assertEquals(
                'Bad Request: The request failed due to validation errors. ' .
                'See http://example.com/error for more information',
                $e->getMessage()
            );

            self::assertEquals([
                [
                    "name" => "primary_colour",
                    "reason" => "Must be one of: blue, red, yellow"
                ]
            ], $e->getValidationErrors());
        } catch (Exception $e) {
            self::fail('Did not throw a Validation exception');
        }
    }

    /**
     * @throws Request
     * @throws Server
     * @throws Validation
     */
    public function testThrowsOn5xx(): void
    {
        $this->expectException(Server::class);
        $this->expectExceptionMessage('Server Error. See http://example.com/error for more information');

        ApiErrorHandler::check(['type' => 'http://example.com/error', 'title' => 'Server Error'], 500);
    }

    /**
     * @throws Request
     * @throws Server
     * @throws Validation
     */
    public function testThrowsOn5xxWithDetail(): void
    {
        $this->expectException(Server::class);
        $this->expectExceptionMessage(
            'Server Error: More Information. See http://example.com/error for more information'
        );

        ApiErrorHandler::check(
            [
                'type' => 'http://example.com/error',
                'title' => 'Server Error',
                'detail' => 'More Information'
            ],
            500
        );
    }
}
