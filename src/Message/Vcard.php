<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Message;

/**
 * SMS Text Message
 */
class Vcard extends Message
{
    const TYPE = 'vcard';

    /**
     * Message Body
     * @var string
     */
    protected $vcard;

    /**
     * Create a new SMS text message.
     *
     * @param string $to
     * @param string $from
     * @param string $vcard
     */
    public function __construct($to, $from, $vcard)
    {
        parent::__construct($to, $from);
        $this->vcard = (string) $vcard;
    }

    /**
     * Get an array of params to use in an API request.
     */
    public function getRequestData($sent = true)
    {
        return array_merge(parent::getRequestData($sent), array(
            'vcard' => $this->vcard
        ));
    }
}
