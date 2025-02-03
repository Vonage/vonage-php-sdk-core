<?php

declare(strict_types=1);

namespace VonageTest\Client\Exception;

use PHPUnit\Framework\TestCase;
use Vonage\Client\Exception\Exception;
use Vonage\Client\Exception\Validation;

class ValidationTest extends TestCase
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
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertEquals($errors, $exception->getValidationErrors());
    }
}
