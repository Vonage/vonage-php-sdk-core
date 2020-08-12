<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Message;

use Vonage\Client\Exception\Exception;
use Vonage\Message\Shortcode\Alert;
use Vonage\Message\Shortcode\Marketing;
use Vonage\Message\Shortcode\TwoFactor;

abstract class Shortcode
{
    protected $to;
    protected $custom;
    protected $options;

    public function __construct($to, array $custom = [], array $options = [])
    {
        $this->to = $to;
        $this->custom = $custom;
        $this->options = $options;
    }

    public function setCustom($custom)
    {
        $this->custom = $custom;
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getRequestData()
    {
        // Options, then custom, then to. This is the priority
        // we want so that people can't overwrite to with a custom param
        return $this->options + $this->custom + [
            'to' => $this->to
        ];
    }

    public static function createMessageFromArray($data)
    {
        if (!isset($data['type'])) {
            throw new Exception('No type provided when creating a shortcode message');
        }

        if (!isset($data['to'])) {
            throw new Exception('No to provided when creating a shortcode message');
        }

        $data['type'] = strtolower($data['type']);

        if ($data['type'] === '2fa') {
            $m = new TwoFactor($data['to']);
        } elseif ($data['type'] === 'marketing') {
            $m = new Marketing($data['to']);
        } elseif ($data['type'] === 'alert') {
            $m = new Alert($data['to']);
        }

        if (isset($data['custom'])) {
            $m->setCustom($data['custom']);
        }

        if (isset($data['options'])) {
            $m->setOptions($data['options']);
        }

        return $m;
    }
}
