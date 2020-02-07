<?php

use qtism\common\datatypes\Utils;

require_once(dirname(__FILE__) . '/../../../QtiSmTestCase.php');

class DatatypeUtilsTest extends QtiSmTestCase
{
    /**
     * @dataProvider isQtiIntegerValidProvider
     * @param integer $value
     */
    public function testIsQtiIntegerValid($value)
    {
        $this->assertTrue(Utils::isQtiInteger($value));
    }

    /**
     * @dataProvider isQtiIntegerInvalidProvider
     * @param integer $value
     */
    public function testIsQtiIntegerInvalid($value)
    {
        $this->assertFalse(Utils::isQtiInteger($value));
    }

    public function isQtiIntegerValidProvider()
    {
        return [
            [0],
            [-0],
            [250],
            [-250],
            [2147483647],
            [-2147483647],
        ];
    }

    public function isQtiIntegerInvalidProvider()
    {
        return [
            [null],
            [''],
            ['bla'],
            [25.5],
            [true],
            [2147483648],
            [-2147483649],
        ];
    }
}
