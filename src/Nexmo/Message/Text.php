<?php
namespace Nexmo\Message;
use Nexmo\Message\MessageAbstract;

/**
 * SMS Text Message
 * @author Tim Lytle <tim.lytle@nexmo.com>
 */
class Text extends MessageAbstract
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
     */
    public function __construct($to, $from, $text)
    {
        parent::__construct($to, $from);
        $this->text = (string) $text;
    }
    
    /**
     * Get an array of params to use in an API request.
     */
    public function getParams()
    {
        return array_merge(parent::getParams(), array(
            'text' => $this->text
        ));
    }
}