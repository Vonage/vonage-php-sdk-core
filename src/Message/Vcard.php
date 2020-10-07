<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Message;

use Vonage\Client\Exception\Exception;

/**
 * SMS Text Message
 */
class Vcard extends Message
{
    public const TYPE = 'vcard';

    /**
     * Message Body
     * @var string
     */
    protected $vcard;

    /**
     * Create a new SMS text message.
     *
     * @param $to
     * @param $from
     * @param $vcard
     */
    public function __construct($to, $from, $vcard)
    {
        parent::__construct($to, $from);

        $this->vcard = (string)$vcard;
    }

    /**
     * Get an array of params to use in an API request.
     *
     * @param bool $sent
     * @return array
     * @throws Exception
     */
    public function getRequestData($sent = true): array
    {
        return array_merge(parent::getRequestData($sent), ['vcard' => $this->vcard]);
    }
}
