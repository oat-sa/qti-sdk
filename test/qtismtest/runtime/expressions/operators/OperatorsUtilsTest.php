<?php

namespace qtismtest\runtime\expressions\operators;

use qtism\runtime\expressions\operators\Utils as OperatorsUtils;
use qtismtest\QtiSmTestCase;
use stdClass;

/**
 * Class OperatorsUtilsTest
 */
class OperatorsUtilsTest extends QtiSmTestCase
{
    /**
     * @dataProvider gcdProvider
     *
     * @param int $a
     * @param int $b
     * @param int $expected
     */
    public function testGcd($a, $b, $expected): void
    {
        $result = OperatorsUtils::gcd($a, $b);
        $this::assertIsInt($result);
        $this::assertSame($expected, $result);
    }

    /**
     * @dataProvider lcmProvider
     *
     * @param int $a
     * @param int $b
     * @param int $expected
     */
    public function testLcm($a, $b, $expected): void
    {
        $result = OperatorsUtils::lcm($a, $b);
        $this::assertIsInt($result);
        $this::assertSame($expected, $expected);
    }

    /**
     * @dataProvider meanProvider
     *
     * @param array $sample
     * @param number $expected
     */
    public function testMean(array $sample, $expected): void
    {
        $result = OperatorsUtils::mean($sample);
        $this::assertSame($expected, $result);
    }

    /**
     * @dataProvider varianceProvider
     *
     * @param array $sample
     * @param bool Apply Bessel's correction?
     * @param number $expected
     */
    public function testVariance(array $sample, $correction, $expected): void
    {
        $result = OperatorsUtils::variance($sample, $correction);
        $this::assertSame($expected, $result);
    }

    /**
     * @dataProvider standardDeviationProvider
     *
     * @param array $sample
     * @param bool Apply Bessel's standard correction?
     * @param number $expected
     */
    public function testStandardDeviation(array $sample, $correction, $expected): void
    {
        $result = OperatorsUtils::standardDeviation($sample, $correction);

        if (is_bool($expected)) {
            $this::assertSame($expected, $result);
        } else {
            $this::assertSame($expected, round($result, 2));
        }
    }

    /**
     * @dataProvider getPrecedingBackslashesCountProvider
     *
     * @param string $string
     * @param int $offset
     * @param int $expected Expected preceding backslashes count.
     */
    public function testGetPrecedingBackslashesCount($string, $offset, $expected): void
    {
        $this::assertSame($expected, OperatorsUtils::getPrecedingBackslashesCount($string, $offset));
    }

    /**
     * @dataProvider pregAddDelimiterProvider
     *
     * @param string $string
     * @param string $expected
     */
    public function testPregAddDelimiter($string, $expected): void
    {
        $this::assertSame($expected, OperatorsUtils::pregAddDelimiter($string));
    }

    /**
     * @dataProvider escapeSymbolsProvider
     *
     * @param string $string
     * @param array|string $symbols
     * @param string $expected
     */
    public function testEscapeSymbols($string, $symbols, $expected): void
    {
        $this::assertSame($expected, OperatorsUtils::escapeSymbols($string, $symbols));
    }

    /**
     * @dataProvider validCustomOperatorClassToPhpClassProvider
     *
     * @param string $customClass
     * @param string $expected
     */
    public function testValidCustomOperatorClassToPhpClass($customClass, $expected): void
    {
        $this::assertEquals($expected, OperatorsUtils::customOperatorClassToPhpClass($customClass));
    }

    /**
     * @dataProvider invalidCustomOperatorClassToPhpClassProvider
     *
     * @param string $customClass
     */
    public function testInvalidCustomOperatorClassToPhpClass($customClass): void
    {
        $this::assertFalse(OperatorsUtils::customOperatorClassToPhpClass($customClass));
    }

    /**
     * @dataProvider lastPregErrorMessageProvider
     *
     * @param string $pcre
     * @param string $subject
     * @param string $message
     * @param int $recursionLimit
     */
    public function testLastPregErrorMessage($pcre, $subject, $message, $recursionLimit = 0): void
    {
        if ($recursionLimit > 0) {
            $prevRecursionLimit = ini_get('pcre.recursion_limit');
            ini_set('pcre.recursion_limit', $recursionLimit);
        }

        @preg_match($pcre, $subject);

        if ($recursionLimit > 0) {
            ini_set('pcre.recursion_limit', $prevRecursionLimit);
        }

        $this::assertEquals($message, OperatorsUtils::lastPregErrorMessage());
    }

    /**
     * @dataProvider patternForPcreProvider
     */
    public function testPrepareXsdPatternForPcre($pattern, $expected): void
    {
        $this->assertEquals($expected, OperatorsUtils::prepareXsdPatternForPcre($pattern));
    }

    /**
     * @return array
     */
    public function pregAddDelimiterProvider(): array
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

    /**
     * @return array
     */
    public function escapeSymbolsProvider(): array
    {
        return [
            ['10$ are 10$', ['$', '^'], '10\\$ are 10\\$'],
            ['$$$Jackpot$$$', '$', '\\$\\$\\$Jackpot\\$\\$\\$'],
            ['^exp$', ['$', '^'], '\\^exp\\$'],
            ['(?:[^\s]+)$', ['$', '^'], '(?:[^\s]+)\\$'],
            ['(?:[\s^]+)$', ['$', '^'], '(?:[\s\\^]+)\\$'],
        ];
    }

    /**
     * @return array
     */
    public function getPrecedingBackslashesCountProvider(): array
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

    /**
     * @return array
     */
    public function gcdProvider(): array
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

    /**
     * @return array
     */
    public function lcmProvider(): array
    {
        return [
            [4, 3, 12],
            [0, 3, 0],
            [3, 0, 0],
            [0, 0, 0],
            [330, -65, 4290],
        ];
    }

    /**
     * @return array
     */
    public function meanProvider(): array
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

    /**
     * @return array
     */
    public function varianceProvider(): array
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

    /**
     * @return array
     */
    public function standardDeviationProvider(): array
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

    /**
     * @return array
     */
    public function validCustomOperatorClassToPhpClassProvider(): array
    {
        return [
            ['com.taotesting.operators.custom.explode', "com\\taotesting\\operators\\custom\\Explode"],
            ['org.imsglobal.rStats', "org\\imsglobal\\RStats"],
            ['taotesting.Custom', "taotesting\\Custom"],
        ];
    }

    /**
     * @return array
     */
    public function invalidCustomOperatorClassToPhpClassProvider(): array
    {
        return [
            ['taotesting'],
            ['com#taotesting'],
            [''],
            ['com|taotesting.custom'],
            [false],
        ];
    }

    /**
     * @return array
     */
    public function lastPregErrorMessageProvider(): array
    {
        return [
            ['', 'foobar', 'PCRE Engine internal error'],
            ['/***', 'foobar', 'PCRE Engine internal error'],
            [
                '/(?:\D+|<\d+>)*[!?]/',
                'foobar foobar foobar foobar foobar foobar foobar foobar foobar foobar foobar foobar',
                'PCRE Engine backtrack limit exceeded',
            ],
            ['/abc/u', "\xa0\xa1", 'PCRE Engine malformed UTF-8 error'],
        ];
    }

    public function patternForPcreProvider(): array
    {
        return [
            'max chars string pattern without anchors' => ['[\s\S]{0,5}', '/^[\s\S]{0,5}$/su'],
            'max chars string pattern with anchors' => ['^[\s\S]{0,5}$', '/^[\s\S]{0,5}$/su'],
            'digits only pattern' => ['[0,1]+', '/^[0,1]+$/su'],
            'digits only pattern with anchors' => ['^[0,1]+$', '/^[0,1]+$/su'],
            'string prefix without anchors' => ['test(.*)', '/^test(.*)$/su'],
            'string prefix with anchors' => ['^test(.*)$', '/^test(.*)$/su'],
            'max words pattern without anchors' => [
                '(?:(?:[^\s\:\!\?\;\…\€]+)[\s\:\!\?\;\…\€]*){0,5}',
                '/^(?:(?:[^\s\:\!\?\;\…\€]+)[\s\:\!\?\;\…\€]*){0,5}$/su',
            ],
            'max words pattern with anchors' => [
                '^(?:(?:[^\s\:\!\?\;\…\€]+)[\s\:\!\?\;\…\€]*){0,5}$',
                '/^(?:(?:[^\s\:\!\?\;\…\€]+)[\s\:\!\?\;\…\€]*){0,5}$/su',
            ],
        ];
    }
}
