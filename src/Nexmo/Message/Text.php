<?php
namespace Nexmo\Message;
use Nexmo\MessageInterface;

/**
 * SMS Text Message
 * @author Tim Lytle <tim.lytle@nexmo.com>
 * 
 * @todo Should implement from some interface / extend an abstract. Will know
 * better what that should look like once all the message types are built.
 */
class Text implements MessageInterface
{
    /**
     * Send Message To
     * @var string
     */
    protected $to;
    
    /**
     * Send Message From
     * @var string
     */
    protected $from;
    
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
        $this->to   = (string) $to;
        $this->from = (string) $from;
        $this->text = (string) $text;
    }
    
    /**
     * Get an array of params to use in an API request.
     * 
     * @todo This should be part of the interface / abstract class.
     */
    public function getParams()
    {
        return array(
            'from' => $this->from,
            'to'   => $this->to,
            'text' => $this->text
        );
    }
}