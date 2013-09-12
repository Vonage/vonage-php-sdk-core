<?php
namespace Nexmo\Message;
use Nexmo\MessageAbstract;
use Nexmo\MessageInterface;

/**
 * SMS Text Message
 * @author Tim Lytle <tim.lytle@nexmo.com>
 */
class Vcal extends MessageAbstract implements MessageInterface
{
    const TYPE = 'vcal';
    
    /**
     * Message Body
     * @var string
     */
    protected $vcal;
    
    /**
     * Create a new SMS text message.
     * 
     * @param string $to
     * @param string $from
     * @param string $vcal
     */
    public function __construct($to, $from, $vcal)
    {
        parent::__construct($to, $from);
        $this->text = (string) $vcal;
    }
    
    /**
     * Get an array of params to use in an API request.
     */
    public function getParams()
    {
        return array_merge(parent::getParams(), array(
            'vcal' => $this->vcal
        ));        
    }
}