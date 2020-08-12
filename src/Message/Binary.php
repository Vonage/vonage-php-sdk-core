<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Message;

/**
 * SMS Binary Message
 */
class Binary extends Message
{
    const TYPE = 'binary';
    
    /**
     * Message Body
     * @var string
     */
    protected $body;
    
    /**
     * Message UDH
     * @var string
     */
    protected $udh;
    
    /**
     * Create a new SMS text message.
     *
     * @param string $to
     * @param string $from
     * @param string $body
     * @param string $udh
     */
    public function __construct($to, $from, $body, $udh)
    {
        parent::__construct($to, $from);
        $this->body = (string) $body;
        $this->udh =  (string) $udh;
    }

    /**
     * Get an array of params to use in an API request.
     */
    public function getRequestData($sent = true)
    {
        return array_merge(parent::getRequestData($sent), array(
            'body' => $this->body,
            'udh'  => $this->udh,
        ));
    }
}
