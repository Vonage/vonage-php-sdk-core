<?php
namespace Nexmo\Message;
use Nexmo\MessageAbstract;
use Nexmo\MessageInterface;

/**
 * SMS Binary Message
 * @author Tim Lytle <tim.lytle@nexmo.com>
 */
class Binary extends MessageAbstract implements MessageInterface 
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
    public function getParams()
    {
        return array_merge(parent::getParams(), array(
            'body' => $this->body,
            'udh'  => $this->udh,
        ));
    }
}