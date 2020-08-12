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
class AutoDetect extends Message
{
    const TYPE = 'text';
    
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
     * @param array  $additional
     */
    public function __construct($to, $from, $text, $additional = [])
    {
        parent::__construct($to, $from, $additional);
        $this->enableEncodingDetection();
        $this->requestData['text'] = (string) $text;
    }
}
