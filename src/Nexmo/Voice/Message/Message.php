<?php
/**
 * @author Tim Lytle <tim@timlytle.net>
 */

namespace Nexmo\Voice\Message;
use Nexmo\Client\Request\AbstractRequest;
use Nexmo\Client\Request\RequestInterface;

class Message extends AbstractRequest implements RequestInterface
{
    protected $params = array();

    public function __construct($text, $to, $from = null)
    {
        $this->params['text'] = $text;
        $this->params['to'] = $to;
        $this->params['from'] = $from;
    }

    public function setLanguage($lang)
    {
        $this->params['lg'] = $lang;
    }

    public function setVoice($voice)
    {
        $this->params['voice'] = $voice;
    }

    public function setRepeat($count)
    {
        $this->params['repeat'] = (int) $count;
    }

    /**
     * @return string
     */
    public function getURI()
    {
        return '/tts/json';
    }

} 