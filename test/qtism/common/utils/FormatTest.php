<?php

require_once(dirname(__FILE__) . '/../../../QtiSmTestCase.php');

use qtism\common\utils\Format;

class FormatTest extends QtiSmTestCase
{
    /**
     * @dataProvider validIdentifierFormatProvider
     */
    public function testValidIdentifierFormat($string)
    {
        $this->assertTrue(Format::isIdentifier($string));
    }

    /**
     * @dataProvider invalidIdentifierFormatProvider
     */
    public function testInvalidIdentifierFormat($string)
    {
        $this->assertFalse(Format::isIdentifier($string));
    }

    /**
     * @dataProvider validVariableRefFormatProvider
     */
    public function testValidVariableRefFormat($string)
    {
        $this->assertTrue(Format::isVariableRef($string));
    }

    /**
     * @dataProvider invalidVariableRefFormatProvider
     */
    public function testInvalidVariableRefFormat($string)
    {
        $this->assertFalse(Format::isVariableRef($string));
    }

    /**
     * @dataProvider validCoordinatesFormatProvider
     */
    public function testValidCoordinatesFormat($string)
    {
        $this->assertTrue(Format::isCoords($string));
    }

    /**
     * @dataProvider invalidCoordinatesFormatProvider
     */
    public function testInvalidCoordinatesFormat($string)
    {
        $this->assertFalse(Format::isCoords($string));
    }

    /**
     * @dataProvider validUriFormatProvider
     */
    public function testValidUriFormat($string)
    {
        $this->assertTrue(Format::isUri($string));
    }

    /**
     * @dataProvider validClassFormatProvider
     */
    public function testValidClassFormatProvider($string)
    {
        $this->assertTrue(Format::isClass($string));
    }

    /**
     * @dataProvider invalidClassFormatProvider
     */
    public function testInvalidClassFormatProvider($string)
    {
        $this->assertFalse(Format::isClass($string));
    }

    /**
     * @dataProvider validString256FormatProvider
     */
    public function testValidString256Provider($string)
    {
        $this->assertTrue(Format::isString256($string));
    }

    /**
     * @dataProvider invalidString256FormatProvider
     */
    public function testInvalidString256Provider($string)
    {
        $this->assertFalse(Format::isString256($string));
    }

    /**
     * @dataProvider scale10Provider
     */
    public function testScale10($float, $expected, $x = 'x', $precision = false)
    {
        $this->assertEquals($expected, Format::scale10($float, $x, $precision));
    }

    /**
     * @dataProvider isPrintfIsoFormatProvider
     *
     * @param string $input
     * @param string $expected
     */
    public function testIsPrintfIsoFormat($input, $expected)
    {
        $this->assertEquals($expected, Format::isPrintfIsoFormat($input));
    }

    /**
     * @dataProvider printfFormatIsoToPhpProvider
     *
     * @param string $input
     * @param boolean $expected
     */
    public function testPrintfFormatIsoToPhp($input, $expected)
    {
        $this->assertEquals($expected, Format::printfFormatIsoToPhp($input));
    }

    /**
     * @dataProvider isXhtmlLengthProvider
     *
     * @param mixed $input
     * @param boolean $expected
     */
    public function testIsXhtmlLength($input, $expected)
    {
        $this->assertSame($expected, Format::isXhtmlLength($input));
    }

    /**
     * @dataProvider sanitizeProvider
     */
    public function testSanitizeIdentifier($dirty, $clean)
    {
        $this->assertEquals($dirty == $clean, Format::isIdentifier($dirty), false);
        $this->assertTrue(Format::isIdentifier(Format::sanitizeIdentifier($dirty), false));
        $this->assertEquals($clean, Format::sanitizeIdentifier($dirty), false);
    }

    /**
     * @dataProvider sanitizeProvider2
     */
    public function testSanitizeIdentifier2($dirty)
    {
        $this->assertTrue(Format::isIdentifier(Format::sanitizeIdentifier($dirty), false));
    }

    public function scale10Provider()
    {
        return [
            // No precision, no X
            [2, '2.000000 x 10⁰'],
            [25, '2.500000 x 10¹'],
            [-53000, '-5.300000 x 10⁴'],
            [6720000000, '6.720000 x 10⁹'],
            [672000000000, '6.720000 x 10¹¹'],
            [0.2, '2.000000 x 10⁻¹'],
            [0.00000000751, '7.510000 x 10⁻⁹'],

            // Precision + X
            [2, '2.000 X 10⁰', 'X', 3],
            [25, '2 X 10¹', 'X', 0],
            [-53000, '-5.3 e 10⁴', 'e', 1],
        ];
    }

    public function validIdentifierFormatProvider()
    {
        return [
            ['_good'],
            ['g0od'],
            ['_-goOd3'],
            ['g.0.o.d...'],
            ['_壞壞'],
            ['myWeight1'],
        ];
    }

    public function invalidIdentifierFormatProvider()
    {
        return [
            ['3bad'],
            ['.bad'],
            ['好壞好'],
            [''],
        ];
    }

    public function validVariableRefFormatProvider()
    {
        return [
            ['{_good}'],
            ['{g0od}'],
            ['{_-goOd3}'],
            ['{g.0.o.d...}'],
            ['{_壞壞}'],
            ['{myWeight1}'],
        ];
    }

    public function invalidVariableRefFormatProvider()
    {
        return [
            ['3bad'],
            ['{.bad'],
            ['好壞好}'],
            ['{}'],
            ['{{}}'],
        ];
    }

    public function validCoordinatesFormatProvider()
    {
        return [
            ['30,20,60,20'],
            ['20'],
            ['200 , 100, 40'],
        ];
    }

    public function invalidCoordinatesFormatProvider()
    {
        return [
            ['30,20,x,20'],
            ['x'],
            ['invalid'],
        ];
    }

    public function validUriFormatProvider()
    {
        return [
            ['http://www.taotesting.com'],
            ['../../index.html'],
        ];
    }

    public function validClassFormatProvider()
    {
        return [
            ['a'],
            ['my-class'],
            ['my-class my-other-class'],
            ['my-class   my-other-class'],
            ['theclass'],
        ];
    }

    public function invalidClassFormatProvider()
    {
        return [
            ["a\tb"],
            [" "],
        ];
    }

    public function validString256FormatProvider()
    {
        return [
            [""],
            ["\t\n\r"],
            ["Hello World!"],
            ["世界，你好！"] // Hello World!
        ];
    }

    public function invalidString256FormatProvider()
    {
        return [
            ["Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla non pellentesque nunc. Interdum et malesuada fames ac ante ipsum primis in faucibus. Nunc adipiscing nisl ut risus facilisis faucibus. Morbi fermentum aliquet est et euismod. Praesent vitae adipiscing felis, ut lacinia velit. Aenean id suscipit nisi, eget feugiat tortor. Mauris eget nisi vitae mi commodo iaculis. Quisque sagittis massa in lectus semper ullamcorper. Morbi id sagittis massa. Aliquam massa dolor, pharetra nec sapien at, dignissim ultricies augue."],
        ];
    }

    public function isPrintfIsoFormatProvider()
    {
        return [
            // input, expected
            ['%#x', true],
            ['%#llx', true],
            ['Octal %#x is Octal %#llx', true],
            ["%d\n", true],
            ["%3d\n", true],
            ["%03d\n", true],
            ["Characters: %c %c \n", true],
            ["Decimals: %d %ld\n", true],
            ["Preceding with blanks: %10d \n", true],
            ["Preceding with zeros: %010d \n", true],
            ["Some different radices: %d %x %o %#x %#o \n", true],
            ["floats: %4.2f %+.0e %E \n", true],
            ["Width trick: %*d \n", true],
            ["%s \n", true],
            ["%3d %06.3f\n", true],
            ["The color: %s\n", true],
            ["First number: %d\n", true],
            ["Second number: %04d\n", true],
            ["Third number: %i\n", true],
            ["Float number: %3.2f\n", true],
            ["Hexadecimal: %x\n", true],
            ["Octal: %o\n", true],
            ["Unsigned value: %u\n", true],
            ["Just print the percentage sign %%\n", false], // Do not contain valid specifier.
            [":%s:\n", true],
            [":%15s:\n", true],
            [":%.10s:\n", true],
            [":%-10s:\n", true],
            [":%-15s:\n", true],
            [":%.15s:\n", true],
            [":%15.10s:\n", true],
            [":%-15.10s:\n", true],
            ["This is an integer with padding %03d\n", true],
            ['This is an integer with padding...', false],
            ["Escape or not? %%s", false],
            ["Escape or not? %%%s", true],
            ["Escape or not? %%%%s", false],
            ["Escape or not? %%%%%s", true],
            ["%s bla %s and %%%s is %s and %%%%s", true],
            ["%%s bla %s and %%%s is %s and %%%%s", true],
            ["%%s bla %%s and %%%s is %s and %%%%s", true],
            ["%%s bla %%s and %%s is %s and %%%%s", true],
            ["%%s bla %%s and %%s is %%%%s and %%%%s", false],
            ["%s", true],
            ["%S", false],
            ["bla %S bli %s", true],
            ["blabla", false],
        ];
    }

    public function printfFormatIsoToPhpProvider()
    {
        return [
            // input, expected
            ['%#x', '%x'],
            ['%#llx', '%x'],
            ['Octal %#x is Octal %#llx', 'Octal %x is Octal %x'],
            ['%i', '%d'],
            ['%+i', '%+d'],
            ['Really good job Mister %s, you deserve a %#+i!', 'Really good job Mister %s, you deserve a %+d!'],
            ['%a', '%x'],
            ['%A', '%X'],
            ['blablabla', 'blablabla'],
        ];
    }

    public function isXhtmlLengthProvider()
    {
        return [
            // input, expected
            [0, true],
            [1, true],
            [100, true],
            ['100%', true],
            ['1%', true],
            ['0%', true],
            [new stdClass(), false],
            [-10, false],
            ['-10', false],
            ['10', false],
            [true, false],
            [10.0, false],
        ];
    }

    public function sanitizeProvider()
    {
        return [
            ["GoodIdentifier", "GoodIdentifier"],
            ["abc 123", "abc123"],
            ["@bc", "bc"],
            ["-bc", "bc"],
            ["---bc", "bc"],
            ["-bc-", "bc-"],
            ["2017id", "id"],
            ["abc@@@", "abc"],
            ["20i17d", "i17d"],
            ["20id@@", "id"],
            ["9bc", "bc"],
            ["bc@", "bc"],
        ];
    }

    public function sanitizeProvider2()
    {
        return [
            [""],
            ["\""],
            ["123@"],
            [123],
            [12.3],
            [null],
            [false],
            [true],
            [[]],
            [new stdClass()],
        ];
    }
}
