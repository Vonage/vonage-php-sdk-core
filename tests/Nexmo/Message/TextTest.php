<?php
/**
 * @author Tim Lytle <tim.lytle@nexmo.com>
 */

class TextTest extends PHPUnit_Framework_TestCase
{
    protected $to   = '14845551212';
    protected $from = '16105551212';
    protected $text = 'this is test text';

    protected $required = array('to', 'from', 'text', 'type');

    /**
     * @var \Nexmo\Message\Text
     */
    protected $message;

    public function setUp()
    {
        $this->message = new \Nexmo\Message\Text($this->to, $this->from, $this->text);
    }

    public function tearDown()
    {
        $this->message = null;
    }

    /**
     * Creating a new text message, should result in the correct (matching) parameters.
     */
    public function testRequiredParams()
    {
        $params = $this->message->getParams();

        $this->assertEquals($this->to,   $params['to']);
        $this->assertEquals($this->from, $params['from']);
        $this->assertEquals($this->text, $params['text']);
        $this->assertEquals('text',      $params['type']);
    }

    /**
     * Optional params shouldn't be in the response, unless set.
     */
    public function testNoDefaultParams()
    {
        $params = array_keys($this->message->getParams());
        $diff = array_diff($params, $this->required); // should be no difference
        $this->assertEmpty($diff, 'message params contain unset values (could change default behaviour)');
    }

    /**
     * DLR can be optionally set.
     * @dataProvider optionalParams
     */
    public function testOptionalParams($setter, $param, $values)
    {
        //check no default value
        $params = $this->message->getParams();
        $this->assertArrayNotHasKey($param, $params);

        //test values
        foreach($values as $value => $expected){
            $this->message->$setter($value);
            $params = $this->message->getParams();
            $this->assertArrayHasKey($param, $params);
            $this->assertEquals($expected, $params[$param]);
        }
    }

    public function optionalParams()
    {
        return array(
          array('requestDLR',   'status-report-req', array(true => 1, false => 0)),
          array('setClientRef', 'client-ref',        array('test' => 'test')),
          array('setNetwork',   'network-code',      array('test' => 'test')),
          array('setTTL',       'ttl',               array('1' => 1)),
          array('setClass',     'message-class',     array(\Nexmo\Message\Text::CLASS_FLASH => \Nexmo\Message\Text::CLASS_FLASH)),
        );
    }
}
