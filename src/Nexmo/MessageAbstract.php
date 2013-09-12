<?php
namespace Nexmo;
use Nexmo\MessageInterface;

/**
 * SMS Text Message
 * @author Tim Lytle <tim.lytle@nexmo.com>
 * 
 * @todo Should implement from some interface / extend an abstract. Will know
 * better what that should look like once all the message types are built.
 */
class MessageAbstract implements MessageInterface
{
    const TYPE = 'abstract';

    const CLASS_FLASH = 0;
    
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
     * Request DLR
     * @var string
     */
    protected $dlr;
    
    /**
     * Client Ref
     * @var string
     */
    protected $ref;
    
    /**
     * Network
     * @var string
     */
    protected $network;

    /**
     * TTL
     * @var int
     */
    protected $ttl;

    /**
     * Message Class
     * @var string
     */
    protected $class;

    /**
     * Create a new SMS text message.
     *
     * @param string $to
     * @param string $from
     * @param mixed $dlr
     * @param string $ref
     * @param string $network
     * @param int $ttl
     */
    public function __construct($to, $from)
    {
        $this->to      = (string) $to;
        $this->from    = (string) $from;
    }
    
    /**
     * Get an array of params to use in an API request.
     */
    public function getParams()
    {
        $params =  array(
            'type'              => static::TYPE,
        	'from'              => $this->from,
            'to'                => $this->to,
            'status-report-req' => $this->dlr,
            'client-ref'        => $this->ref,
            'network-code'      => $this->network,
            'ttl'               => $this->ttl,
            'message-class'     => $this->class
        );

        foreach($params as $param => $value){
            if(is_null($value)){
                unset($params[$param]);
            }
        }

        return $params;
    }

    public function requestDLR($dlr)
    {
        $this->dlr = $dlr ? 1 : 0;
    }

    public function setClientRef($ref)
    {
        $this->ref = (string) $ref;
    }

    public function setNetwork($network)
    {
        $this->network = (string) $network;
    }

    public function setTTL($ttl)
    {
        $this->ttl = (int) $ttl;
    }

    public function setClass($class)
    {
        $this->class = $class;
    }
}