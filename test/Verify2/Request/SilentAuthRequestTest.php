<?php

declare(strict_types=1);

namespace VonageTest\Verify2\Request;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Verify2\Request\BaseVerifyRequest;
use Vonage\Verify2\Request\SilentAuthRequest;
use Vonage\Verify2\Request\SMSRequest;
use Vonage\Verify2\VerifyObjects\VerificationLocale;
use Vonage\Verify2\VerifyObjects\VerificationWorkflow;

class SilentAuthRequestTest extends TestCase
{
    public function testIsValidSilentAuthRequest(): void
    {
        $silentAuthRequest = new SilentAuthRequest(
            '077377775555',
            'VONAGE',
            'https://silent-auth.example'
        );

        $extraWorkflow = new VerificationWorkflow(
            VerificationWorkflow::WORKFLOW_SMS,
            '077377775555'
        );

        $silentAuthRequest->addWorkflow($extraWorkflow);

        $client = new Client(new Client\Credentials\Basic('test', 'test2'));
        $this->assertTrue($client->verify2()::isSilentAuthRequest($silentAuthRequest));
        $this->assertTrue(SilentAuthRequest::isValidWorkflow($silentAuthRequest->getWorkflows()));
    }

    public function testIsInvalidSilentAuthRequest(): void
    {
        $request = new SMSRequest(
            '077377775555',
            'VONAGE',
        );

        $extraWorkflow = new VerificationWorkflow(
            VerificationWorkflow::WORKFLOW_SILENT_AUTH,
            '077377775555'
        );

        $request->addWorkflow($extraWorkflow);
        $client = new Client(new Client\Credentials\Basic('test', 'test2'));

        $this->assertTrue($client->verify2()::isSilentAuthRequest($request));
        $this->assertFalse(SilentAuthRequest::isValidWorkflow($request->getWorkflows()));
    }

    public function testIsNotSilentAuthRequest(): void
    {
        $request = new SMSRequest(
            '077377775555',
            'VONAGE',
        );

        $extraWorkflow = new VerificationWorkflow(
            VerificationWorkflow::WORKFLOW_EMAIL,
            'jim@jim.com'
        );

        $request->addWorkflow($extraWorkflow);
        $client = new Client(new Client\Credentials\Basic('test', 'test2'));

        $this->assertFalse($client->verify2()::isSilentAuthRequest($request));
        // No second test to see if the workflow is valid, why are you checking a workflow on a non SA request?
    }
}
