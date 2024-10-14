<?php

declare(strict_types=1);

namespace VonageTest\Client\Exception;

use PHPUnit\Framework\TestCase;
use Vonage\Client\Exception\Exception;

class Validation extends TestCase
{
    public function testValidationException()
    {
        $message = 'Validation failed';
        $code = 422;
        $previous = new Exception('Previous exception');
        $errors = [
            'field1' => 'Error message 1',
            'field2' => 'Error message 2'
        ];

        $exception = new Validation($message, $code, $previous, $errors);

        // Assert the exception message
        $this->assertEquals($message, $exception->getMessage());

        // Assert the exception code
        $this->assertEquals($code, $exception->getCode());

        // Assert the previous exception
        $this->assertSame($previous, $exception->getPrevious());

        // Assert the validation errors
        $this->assertEquals($errors, $exception->getValidationErrors());
    }
}
