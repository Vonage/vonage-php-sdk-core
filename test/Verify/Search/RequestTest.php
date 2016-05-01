<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
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
