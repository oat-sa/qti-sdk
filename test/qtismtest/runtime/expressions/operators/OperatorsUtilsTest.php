<?php

require_once(dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\runtime\expressions\operators\Utils as OperatorsUtils;

class OperatorsUtilsTest extends QtiSmTestCase
{
    /**
     * @dataProvider gcdProvider
     *
     * @param integer $a
     * @param integer $b
     * @param integer $expected
     */
    public function testGcd($a, $b, $expected)
    {
        $result = OperatorsUtils::gcd($a, $b);
        $this->assertInternalType('integer', $result);
        $this->assertSame($expected, $result);
    }

    /**
     * @dataProvider lcmProvider
     *
     * @param integer $a
     * @param integer $b
     * @param integer $expected
     */
    public function testLcm($a, $b, $expected)
    {
        $result = OperatorsUtils::lcm($a, $b);
        $this->assertInternalType('integer', $result);
        $this->assertSame($expected, $expected);
    }

    /**
     * @dataProvider meanProvider
     *
     * @param array $sample
     * @param number $expected
     */
    public function testMean(array $sample, $expected)
    {
        $result = OperatorsUtils::mean($sample);
        $this->assertSame($expected, $result);
    }

    /**
     * @dataProvider varianceProvider
     *
     * @param array $sample
     * @param boolean Apply Bessel's correction?
     * @param number $expected
     */
    public function testVariance(array $sample, $correction, $expected)
    {
        $result = OperatorsUtils::variance($sample, $correction);
        $this->assertSame($expected, $result);
    }

    /**
     * @dataProvider standardDeviationProvider
     *
     * @param array $sample
     * @param boolean Apply Bessel's standard correction?
     * @param number $expected
     */
    public function testStandardDeviation(array $sample, $correction, $expected)
    {
        $result = OperatorsUtils::standardDeviation($sample, $correction);

        if (is_bool($expected)) {
            $this->assertSame($expected, $result);
        } else {
            $this->assertSame($expected, round($result, 2));
        }
    }

    /**
     * @dataProvider getPrecedingBackslashesCountProvider
     *
     * @param string $string
     * @param integer $offset
     * @param integer $expected Expected preceding backslashes count.
     */
    public function testGetPrecedingBackslashesCount($string, $offset, $expected)
    {
        $this->assertSame($expected, OperatorsUtils::getPrecedingBackslashesCount($string, $offset));
    }

    /**
     * @dataProvider pregAddDelimiterProvider
     *
     * @param string $string
     * @param string $expected
     */
    public function testPregAddDelimiter($string, $expected)
    {
        $this->assertSame($expected, OperatorsUtils::pregAddDelimiter($string));
    }

    /**
     * @dataProvider escapeSymbolsProvider
     *
     * @param string $string
     * @param array|string $symbols
     * @param string $expected
     */
    public function testEscapeSymbols($string, $symbols, $expected)
    {
        $this->assertSame($expected, OperatorsUtils::escapeSymbols($string, $symbols));
    }

    /**
     * @dataProvider validCustomOperatorClassToPhpClassProvider
     *
     * @param string $customClass
     * @param string $expected
     */
    public function testValidCustomOperatorClassToPhpClass($customClass, $expected)
    {
        $this->assertEquals($expected, OperatorsUtils::customOperatorClassToPhpClass($customClass));
    }

    /**
     * @dataProvider invalidCustomOperatorClassToPhpClassProvider
     *
     * @param string $customClass
     */
    public function testInvalidCustomOperatorClassToPhpClass($customClass)
    {
        $this->assertFalse(OperatorsUtils::customOperatorClassToPhpClass($customClass));
    }

    public function pregAddDelimiterProvider()
    {
        return [
            ['', '//'],
            ['test', '/test/'],
            ['te/st', '/te\\/st/'],
            ['/', '/\\//'],
            ['/test', '/\\/test/'],
            ['test/', '/test\\//'],
            ['te/st is /test/', '/te\\/st is \\/test\\//'],
            ['te\\/st', '/te\\/st/'],
            ['te\\\\/st', '/te\\\\\\/st/'],
            ['te\\\\\\\\/st', '/te\\\\\\\\\\/st/'],
            ['\d{1,2}', '/\d{1,2}/'],
        ];
    }

    public function escapeSymbolsProvider()
    {
        return [
            ['10$ are 10$', ['$', '^'], '10\\$ are 10\\$'],
            ['$$$Jackpot$$$', '$', '\\$\\$\\$Jackpot\\$\\$\\$'],
            ['^exp$', ['$', '^'], '\\^exp\\$'],
        ];
    }

    public function getPrecedingBackslashesCountProvider()
    {
        return [
            ['', 0, 0],
            ['string!', 0, 0],
            ['string!', 10, 0],
            ['string!', 6, 0],
            ['string!', -20, 0],
            ['\\a', 1, 1],
            ['\\\\a', 2, 2],
            ['\\abc\\\\\\d', 7, 3],
        ];
    }

    public function gcdProvider()
    {
        return [
            [60, 330, 30],
            [256, 1024, 256],
            [456, 3698, 2],
            [25, 0, 25],
            [0, 25, 25],
            [0, 0, 0],
        ];
    }

    public function lcmProvider()
    {
        return [
            [4, 3, 12],
            [0, 3, 0],
            [3, 0, 0],
            [0, 0, 0],
            [330, -65, 4290],
        ];
    }

    public function meanProvider()
    {
        return [
            [[], false],
            [['string!'], false],
            [[10, 11, 'string'], false],
            [[10, null, 11], false],
            [[10, 11, new stdClass()], false],
            [[10], 10],
            [[10, 10], 10],
            [[10, 20, 30], 20],
            [[10.0, 20.0, 30.0], 20.0],
            [[0], 0],
            [[0, 0], 0],
        ];
    }

    public function varianceProvider()
    {
        return [
            // [0] = sample; [1] = Bessel's correction, [2] = expected result
            [[600, 470, 170, 430, 300], false, 21704], // on population (no correction)
            [[10], false, 0],
            [[600, 470, 170, 430, 300], true, 27130], // on sample (correction)
            [['String!'], false, false],
            [[null, 10], false, false],

            // fails because when using Bessel's correction,
            // the contain size must be > 1
            [[10], true, false],
        ];
    }

    public function standardDeviationProvider()
    {
        // The equality test will be done with 2 significant figures.
        return [
            [[600, 470, 170, 430, 300], false, 147.32], // on population (no correction)
            [[600, 470, 170, 430, 300], true, 164.71], // on sample (correction)
            [[10, 'String!'], false, false],
            [[0, 0, 0], false, 0.00],

            // fails because when using the Bessel's correction,
            // the container size must be > 1
            [[10], true, false],
        ];
    }

    public function validCustomOperatorClassToPhpClassProvider()
    {
        return [
            ['com.taotesting.operators.custom.explode', "com\\taotesting\\operators\\custom\\Explode"],
            ['org.imsglobal.rStats', "org\\imsglobal\\RStats"],
            ['taotesting.Custom', "taotesting\\Custom"],
        ];
    }

    public function invalidCustomOperatorClassToPhpClassProvider()
    {
        return [
            ['taotesting'],
            ['com#taotesting'],
            [''],
            ['com|taotesting.custom'],
        ];
    }
}
