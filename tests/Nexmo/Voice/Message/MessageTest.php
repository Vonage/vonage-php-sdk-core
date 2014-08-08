<?php
/**
 * @author Tim Lytle <tim@timlytle.net>
 */

namespace Nexmo\Voice\Message;

class MessageTest extends PHPUnit_Framework_TestCase
{
    protected $message;

    protected $text = 'TTS Text';
    protected $to   = '15553331212';
    protected $from = '15554441212';

    public function setUp()
    {
        $this->message = new Message($this->text, $this->to, $this->from);
    }

}
 