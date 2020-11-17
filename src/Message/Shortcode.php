<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Message;

use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Message\Shortcode\Alert;
use Vonage\Message\Shortcode\Marketing;
use Vonage\Message\Shortcode\TwoFactor;

use function strtolower;

abstract class Shortcode
{
    /**
     * @var string
     */
    protected $to;

    /**
     * @var array
     */
    protected $custom;

    /**
     * @var array
     */
    protected $options;

    public function __construct(string $to, array $custom = [], array $options = [])
    {
        $this->to = $to;
        $this->custom = $custom;
        $this->options = $options;
    }

    public function setCustom(array $custom): void
    {
        $this->custom = $custom;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getRequestData(): array
    {
        // Options, then custom, then to. This is the priority
        // we want so that people can't overwrite to with a custom param
        return $this->options + $this->custom + ['to' => $this->to];
    }

    /**
     * @throws ClientException
     *
     * @return Alert|Marketing|TwoFactor|null
     */
    public static function createMessageFromArray(array $data)
    {
        if (!isset($data['type'])) {
            throw new ClientException('No type provided when creating a shortcode message');
        }

        if (!isset($data['to'])) {
            throw new ClientException('No to provided when creating a shortcode message');
        }

        $data['type'] = strtolower($data['type']);

        if ($data['type'] === '2fa') {
            $m = new TwoFactor($data['to']);
        } elseif ($data['type'] === 'marketing') {
            $m = new Marketing($data['to']);
        } elseif ($data['type'] === 'alert') {
            $m = new Alert($data['to']);
        }

        if (isset($m)) {
            if (isset($data['custom'])) {
                $m->setCustom($data['custom']);
            }

            if (isset($data['options'])) {
                $m->setOptions($data['options']);
            }

            return $m;
        }

        return null;
    }
}
