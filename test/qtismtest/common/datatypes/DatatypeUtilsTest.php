<?php

namespace qtismtest\common\datatypes;

use qtism\common\datatypes\Utils;
use qtismtest\QtiSmTestCase;

/**
 * Class DatatypeUtilsTest
 */
class DatatypeUtilsTest extends QtiSmTestCase
{
    /**
     * @dataProvider isQtiIntegerValidProvider
     * @param int $value
     */
    public function testIsQtiIntegerValid($value): void
    {
        $this::assertTrue(Utils::isQtiInteger($value));
    }

    /**
     * @dataProvider isQtiIntegerInvalidProvider
     * @param int $value
     */
    public function testIsQtiIntegerInvalid($value): void
    {
        $this::assertFalse(Utils::isQtiInteger($value));
    }

    /**
     * @return array
     */
    public function isQtiIntegerValidProvider(): array
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

    /**
     * @return array
     */
    public function isQtiIntegerInvalidProvider(): array
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
