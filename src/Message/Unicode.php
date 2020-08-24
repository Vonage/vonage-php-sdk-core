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
class Unicode extends Message
{
    const TYPE = 'unicode';
    
    /**
     * Message Body
     * @var string
     */
    protected $text;
    
    /**
     * Create a new SMS text message.
     *
     * @param string $to
     * @param string $from
     * @param string $text
     */
    public function __construct($to, $from, $text)
    {
        parent::__construct($to, $from);
        $this->text = (string) $text;
    }
    
    /**
     * Get an array of params to use in an API request.
     */
    public function getRequestData($sent = true)
    {
        return array_merge(parent::getRequestData($sent), array(
            'text' => $this->text
        ));
    }
}
