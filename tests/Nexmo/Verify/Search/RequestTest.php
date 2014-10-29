<?php
/**
 * Created by PhpStorm.
 * User: tjlytle
 * Date: 10/28/14
 * Time: 10:59 PM
 */

namespace Nexmo\Verify\Search;


class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function testSingleRequest()
    {
        $request = new Request('1234abcd');
        $params = $request->getParams();

        $this->assertArrayHasKey('request_id', $params);
        $this->assertEquals('1234abcd', $params['request_id']);
    }
}
