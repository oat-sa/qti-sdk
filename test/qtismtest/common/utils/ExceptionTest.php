<?php

namespace qtismtest\common\utils;

use Exception;
use qtism\common\utils\Exception as ExceptionUtils;
use qtismtest\QtiSmTestCase;

/**
 * Class ExceptionTest
 */
class ExceptionTest extends QtiSmTestCase
{
    public function testNoChaining()
    {
        $e = new Exception('This is an error message!');
        $this->assertEquals(
            '[Exception] This is an error message!',
            ExceptionUtils::formatMessage($e)
        );

        $this->assertEquals(
            'This is an error message!',
            ExceptionUtils::formatMessage($e, false)
        );
    }

    public function testChaining()
    {
        $e1 = new Exception('This is an error message!');
        $e2 = new Exception('This is a 2nd error message!', 0, $e1);

        // With class name.
        $expected = "[Exception] This is a 2nd error message!\n";
        $expected .= "Caused by:\n";
        $expected .= "[Exception] This is an error message!";

        $this->assertEquals($expected, ExceptionUtils::formatMessage($e2));

        // No class name.
        $expected = "This is a 2nd error message!\n";
        $expected .= "Caused by:\n";
        $expected .= "This is an error message!";

        $this->assertEquals($expected, ExceptionUtils::formatMessage($e2, false));
    }
}
