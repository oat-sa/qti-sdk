<?php

namespace qtismtest\common\utils;

use qtismtest\QtiSmTestCase;
use qtism\common\utils\Php as PhpUtils;

class PhpTest extends QtiSmTestCase
{
    
    /**
     * @dataProvider displayTypeProvider
     *
     * @param mixed $value
     * @param string $expected
     */
    public function testDisplayType($value, $expected)
    {
        $this->assertEquals($expected, PhpUtils::displayType($value));
    }
    
    public function displayTypeProvider()
    {
        return array(
            array(null, 'null'),
            array(12, 'php:integer'),
            array(15.2, 'php:double'),
            array('str', 'php:string'),
            array(true, 'php:boolean'),
            array(array(), 'php:array'),
            array(new \stdClass(), 'stdClass')
        );
    }
}
