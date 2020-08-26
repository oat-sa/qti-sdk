<?php

namespace qtismtest\common\utils;

use qtism\common\utils\Php as PhpUtils;
use qtismtest\QtiSmTestCase;
use stdClass;

/**
 * Class PhpTest
 */
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

    /**
     * @return array
     */
    public function displayTypeProvider()
    {
        return [
            [null, 'null'],
            [12, 'php:integer'],
            [15.2, 'php:double'],
            ['str', 'php:string'],
            [true, 'php:boolean'],
            [[], 'php:array'],
            [new stdClass(), 'stdClass'],
        ];
    }
}
