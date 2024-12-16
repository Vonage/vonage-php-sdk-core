<?php

declare(strict_types=1);

namespace Vonage\Client;

use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

class VonageConfig
{
    protected ?LoggerInterface $logger = null;
    protected bool $showDeprecations = false;
    protected bool $debugMode = false;
    protected ?string $baseUrl = null;

    /**
     * Used to inject a custom HTTP Client that is PSR-7 compatible
     * @var ClientInterface|null
     */
    protected ?ClientInterface $httpClient = null;

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    public function setLogger(?LoggerInterface $logger): VonageConfig
    {
        $this->logger = $logger;
        return $this;
    }

    public function getShowDeprecations(): bool
    {
        return $this->showDeprecations;
    }

    public function setShowDeprecations(bool $showDeprecations): VonageConfig
    {
        $this->showDeprecations = $showDeprecations;
        return $this;
    }

    public function isDebugMode(): bool
    {
        return $this->debugMode;
    }

    public function setDebugMode(bool $debugMode): VonageConfig
    {
        $this->debugMode = $debugMode;
        return $this;
    }

    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(?string $baseUrl): VonageConfig
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }
    public function getHttpClient(): ?ClientInterface
    {
        return $this->httpClient;
    }

    public function setHttpClient(?ClientInterface $httpClient): VonageConfig
    {
        $this->httpClient = $httpClient;

        return $this;
    }
}
