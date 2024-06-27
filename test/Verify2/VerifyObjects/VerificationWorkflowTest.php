<?php

declare(strict_types=1);

namespace VonageTest\Verify2\VerifyObjects;

use InvalidArgumentException;
use Vonage\Verify2\VerifyObjects\VerificationWorkflow;
use VonageTest\VonageTestCase;

class VerificationWorkflowTest extends VonageTestCase
{
    public function testWillTakeValidNumericFromValue(): void
    {
        $workflow = new VerificationWorkflow(
            VerificationWorkflow::WORKFLOW_SMS,
            'xxx',
            '44773666552'
        );

        $this->assertInstanceOf(VerificationWorkflow::class, $workflow);
    }

    public function testWillTakeValidAlphaFromValue(): void
    {
        $workflow = new VerificationWorkflow(
            VerificationWorkflow::WORKFLOW_SMS,
            'xxx',
            'ACMECOMPANY'
        );

        $this->assertInstanceOf(VerificationWorkflow::class, $workflow);
    }

    public function testWillThrowErrorOnInvalidNumericFromValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $workflow = new VerificationWorkflow(
            VerificationWorkflow::WORKFLOW_SMS,
            'xxx',
            '3459568445'
        );
    }

    public function testWillThrowErrorOnInvalidAlphaFromValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $workflow = new VerificationWorkflow(
            VerificationWorkflow::WORKFLOW_SMS,
            'xxx',
            'MYAWESOMECOMPANYISTOOBIG'
        );
    }
}
