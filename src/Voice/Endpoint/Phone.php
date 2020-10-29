<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Voice\Endpoint;

class Phone implements EndpointInterface
{
    /**
     * @var string
     */
    protected $dtmfAnswer;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var ?string
     */
    protected $ringbackTone;

    /**
     * @var ?string
     */
    protected $url;

    /**
     * Phone constructor.
     *
     * @param string $number
     * @param string|null $dtmfAnswer
     */
    public function __construct(string $number, string $dtmfAnswer = null)
    {
        $this->id = $number;
        $this->dtmfAnswer = $dtmfAnswer;
    }

    /**
     * @param string $number
     * @param array $data
     * @return Phone
     */
    public static function factory(string $number, array $data): Phone
    {
        $endpoint = new Phone($number);

        if (array_key_exists('dtmfAnswer', $data)) {
            $endpoint->setDtmfAnswer($data['dtmfAnswer']);
        }

        if (array_key_exists('onAnswer', $data)) {
            $endpoint->setUrl($data['onAnswer']['url']);

            if (array_key_exists('ringbackTone', $data['onAnswer'])) {
                $endpoint->setRingbackTone($data['onAnswer']['ringbackTone']);
            }

            // Legacy name for ringbackTone
            if (array_key_exists('ringback', $data['onAnswer'])) {
                $endpoint->setRingbackTone($data['onAnswer']['ringback']);
            }
        }

        return $endpoint;
    }

    /**
     * @return string|null
     */
    public function getDtmfAnswer(): ?string
    {
        return $this->dtmfAnswer;
    }

    /**
     * @return array{type: string, number: string, dtmfAnswer?: string}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param string $dtmf
     * @return $this
     */
    public function setDtmfAnswer(string $dtmf): self
    {
        $this->dtmfAnswer = $dtmf;

        return $this;
    }

    /**
     * @return array{type: string, number: string, dtmfAnswer?: string}
     */
    public function toArray(): array
    {
        $data = [
            'type' => 'phone',
            'number' => $this->id,
        ];

        if (null !== $this->getDtmfAnswer()) {
            $data['dtmfAnswer'] = $this->getDtmfAnswer();
        }

        if (null !== $this->getUrl()) {
            $data['onAnswer']['url'] = $this->getUrl();

            if (null !== $this->getRingbackTone()) {
                $data['onAnswer']['ringbackTone'] = $this->getRingbackTone();
            }
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getRingbackTone(): ?string
    {
        return $this->ringbackTone;
    }

    /**
     * @param string $ringbackTone
     * @return $this
     */
    public function setRingbackTone(string $ringbackTone): self
    {
        $this->ringbackTone = $ringbackTone;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }
}
