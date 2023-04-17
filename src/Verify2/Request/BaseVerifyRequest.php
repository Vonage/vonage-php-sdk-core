<?php

namespace Vonage\Verify2\Request;

use Vonage\Verify2\VerifyObjects\VerificationLocale;
use Vonage\Verify2\VerifyObjects\VerificationWorkflow;

abstract class BaseVerifyRequest implements RequestInterface
{
    private const TIMEOUT_MIN = 60;
    private const TIMEOUT_MAX = 900;
    private const LENGTH_MIN = 4;
    private const LENGTH_MAX = 10;

    protected ?VerificationLocale $locale = null;

    protected int $timeout = 300;

    protected ?string $clientRef = null;

    protected int $length = 4;

    protected string $brand;

    protected array $workflows = [];

    protected ?string $code = null;

    public function getLocale(): ?VerificationLocale
    {
        return $this->locale;
    }

    public function setLocale(?VerificationLocale $verificationLocale): static
    {
        $this->locale = $verificationLocale;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function setTimeout(int $timeout): static
    {
        $range = [
            'options' => [
                'min_range' => self::TIMEOUT_MIN,
                'max_range' => self::TIMEOUT_MAX
                ]
        ];

        if (!filter_var($timeout, FILTER_VALIDATE_INT, $range)) {
            throw new \OutOfBoundsException('Timeout ' . $timeout . ' is not valid');
        }

        $this->timeout = $timeout;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getClientRef(): ?string
    {
        return $this->clientRef;
    }

    public function setClientRef(?string $clientRef): static
    {
        $this->clientRef = $clientRef;

        return $this;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function setLength(int $length): static
    {
        $range = [
            'options' => [
                'min_range' => self::LENGTH_MIN,
                'max_range' => self::LENGTH_MAX
            ]
        ];

        if (!filter_var($length, FILTER_VALIDATE_INT, $range)) {
            throw new \OutOfBoundsException('PIN Length ' . $length . ' is not valid');
        }

        $this->length = $length;

        return $this;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getWorkflows(): array
    {
        return array_map(static function ($workflow) {
            return $workflow->toArray();
        }, $this->workflows);
    }

    public function addWorkflow(VerificationWorkflow $verificationWorkflow): static
    {
        $this->workflows[] = $verificationWorkflow;

        return $this;
    }

    public function getBaseVerifyUniversalOutputArray(): array
    {
        $returnArray = [
            'locale' => $this->getLocale()->getCode(),
            'channel_timeout' => $this->getTimeout(),
            'code_length' => $this->getLength(),
            'brand' => $this->getBrand(),
            'workflow' => $this->getWorkflows()
        ];

        if ($this->getClientRef()) {
            $returnArray['client_ref'] = $this->getClientRef();
        }

        if ($this->getCode()) {
            $returnArray['code'] = $this->getCode();
        }

        return $returnArray;
    }
}
