<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest;

use Exception;
use PHPUnit\Framework\TestCase;
use Vonage\ApiErrorHandler;
use Vonage\Client\Exception\Request as RequestException;
use Vonage\Client\Exception\Server as ServerException;
use Vonage\Client\Exception\Validation as ValidationException;

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
        $this->assertNull(ApiErrorHandler::check(['success' => true], 200));
    }

    /**
     * @throws RequestException
     * @throws ServerException
     * @throws ValidationException
     */
    public function testThrowsOn4xx(): void
    {
        $this->expectException(RequestException::class);
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
     * @throws RequestException
     * @throws ServerException
     * @throws ValidationException
     */
    public function testThrowsOn4xxWithDetail(): void
    {
        $this->expectException(RequestException::class);
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
        } catch (ValidationException $e) {
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertEquals(
                'Bad Request: The request failed due to validation errors. ' .
                'See http://example.com/error for more information',
                $e->getMessage()
            );

            $this->assertEquals([
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
     * @throws RequestException
     * @throws ServerException
     * @throws ValidationException
     */
    public function testThrowsOn5xx(): void
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('Server Error. See http://example.com/error for more information');

        ApiErrorHandler::check(['type' => 'http://example.com/error', 'title' => 'Server Error'], 500);
    }

    /**
     * @throws RequestException
     * @throws ServerException
     * @throws ValidationException
     */
    public function testThrowsOn5xxWithDetail(): void
    {
        $this->expectException(ServerException::class);
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
