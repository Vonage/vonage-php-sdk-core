<?php
namespace Nexmo\Message;
use Nexmo\Message\MessageAbstract;

/**
 * SMS Binary Message
 * @author Tim Lytle <tim.lytle@nexmo.com>
 */
class Wap extends MessageAbstract
{
    const TYPE = 'wappush';
    
    /**
     * Message Title
     * @var string
     */
    protected $title;
    
    /**
     * Message URL
     * @var string
     */
    protected $url;
    
    /**
     * Message Timeoupt
     * @var int
     */
    protected $validity;

    /**
     * Create a new SMS text message.
     *
     * @param string $to
     * @param string $from
     * @param string $title
     * @param string $url
     * @param int $validity
     */
    public function __construct($to, $from, $title, $url, $validity)
    {
        parent::__construct($to, $from);
        $this->title    = (string) $title;
        $this->url      =  (string) $url;
        $this->validity = (int) $validity;
    }
    
    /**
     * Get an array of params to use in an API request.
     */
    public function getParams()
    {
        return array_merge(parent::getParams(), array(
            'title'      => $this->title,
            'url'        => $this->url,
            'validity'   => $this->validity,
        ));
    }
}