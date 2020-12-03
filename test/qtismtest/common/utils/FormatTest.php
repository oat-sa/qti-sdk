<?php

namespace qtismtest\common\utils;

use InvalidArgumentException;
use qtism\common\utils\Format;
use qtismtest\QtiSmTestCase;
use stdClass;

/**
 * Class FormatTest
 */
class FormatTest extends QtiSmTestCase
{
    /**
     * @dataProvider validIdentifierFormatProvider
     * @param string $string
     */
    public function testValidIdentifierFormat($string)
    {
        $this->assertTrue(Format::isIdentifier($string));
    }

    /**
     * @dataProvider invalidIdentifierFormatProvider
     * @param string $string
     */
    public function testInvalidIdentifierFormat($string)
    {
        $this->assertFalse(Format::isIdentifier($string));
    }

    /**
     * @dataProvider validVariableRefFormatProvider
     * @param string $string
     */
    public function testValidVariableRefFormat($string)
    {
        $this->assertTrue(Format::isVariableRef($string));
    }

    /**
     * @dataProvider invalidVariableRefFormatProvider
     * @param string $string
     */
    public function testInvalidVariableRefFormat($string)
    {
        $this->assertFalse(Format::isVariableRef($string));
    }

    /**
     * @dataProvider validCoordinatesFormatProvider
     * @param string $string
     */
    public function testValidCoordinatesFormat($string)
    {
        $this->assertTrue(Format::isCoords($string));
    }

    /**
     * @dataProvider invalidCoordinatesFormatProvider
     * @param string $string
     */
    public function testInvalidCoordinatesFormat($string)
    {
        $this->assertFalse(Format::isCoords($string));
    }

    /**
     * @dataProvider validUriFormatProvider
     * @param string $string
     */
    public function testValidUriFormat($string)
    {
        $this->assertTrue(Format::isUri($string));
    }

    /**
     * @dataProvider invalidUriFormatProvider
     * @param string $string
     */
    public function testInvalidUriFormat($string)
    {
        $this->assertFalse(Format::isUri($string));
    }

    /**
     * @dataProvider validBCP47LanguagesProvider
     * @param string $string
     */
    public function testValidBCP47LanguageFormat($string)
    {
        $this->assertTrue(Format::isBCP47Lang($string));
    }

    /**
     * @dataProvider invalidBCP47LanguagesProvider
     * @param string $string
     */
    public function testInvalidBCP47LanguageFormat($string)
    {
        $this->assertFalse(Format::isBCP47Lang($string));
    }

    /**
     * @dataProvider validClassFormatProvider
     * @param string $string
     */
    public function testValidClassFormatProvider($string)
    {
        $this->assertTrue(Format::isClass($string));
    }

    /**
     * @dataProvider invalidClassFormatProvider
     * @param string $string
     */
    public function testInvalidClassFormatProvider($string)
    {
        $this->assertFalse(Format::isClass($string));
    }

    /**
     * @dataProvider validString256FormatProvider
     * @param string $string
     */
    public function testValidString256Provider($string)
    {
        $this->assertTrue(Format::isString256($string));
    }

    /**
     * @dataProvider invalidString256FormatProvider
     * @param string $string
     */
    public function testInvalidString256Provider($string)
    {
        $this->assertFalse(Format::isString256($string));
    }

    /**
     * @dataProvider validFileFormatProvider
     * @param string $string
     */
    public function testValidFile($string)
    {
        $this->assertTrue(Format::isFile($string));
    }

    /**
     * @dataProvider scale10Provider
     * @param float $float
     * @param string $expected
     * @param string $x
     * @param int|bool $precision
     */
    public function testScale10($float, $expected, $x = 'x', $precision = false)
    {
        $this->assertEquals($expected, Format::scale10($float, $x, $precision));
    }

    /**
     * @dataProvider isPrintfIsoFormatProvider
     *
     * @param string $input
     * @param bool $expected
     */
    public function testIsPrintfIsoFormat($input, $expected)
    {
        $this->assertEquals($expected, Format::isPrintfIsoFormat($input));
    }

    /**
     * @dataProvider printfFormatIsoToPhpProvider
     *
     * @param string $input
     * @param bool $expected
     */
    public function testPrintfFormatIsoToPhp($input, $expected)
    {
        $this->assertEquals($expected, Format::printfFormatIsoToPhp($input));
    }

    /**
     * @dataProvider isXhtmlLengthProvider
     *
     * @param mixed $input
     * @param bool $expected
     */
    public function testIsXhtmlLength($input, $expected)
    {
        $this->assertSame($expected, Format::isXhtmlLength($input));
    }

    /**
     * @dataProvider sanitizeProvider
     * @param string $dirty
     * @param mixed $clean
     */
    public function testSanitizeIdentifier($dirty, $clean)
    {
        $this->assertEquals($dirty == $clean, Format::isIdentifier($dirty), false);
        $this->assertTrue(Format::isIdentifier(Format::sanitizeIdentifier($dirty), false));
        $this->assertEquals($clean, Format::sanitizeIdentifier($dirty), false);
    }

    /**
     * @dataProvider sanitizeProvider2
     * @param mixed $dirty
     */
    public function testSanitizeIdentifier2($dirty)
    {
        $this->assertTrue(Format::isIdentifier(Format::sanitizeIdentifier($dirty), false));
    }

    /**
     * @param $input
     * @param $expected
     * @dataProvider isAriaLevelProvider
     */
    public function testIsAriaLevel($input, $expected)
    {
        $this->assertSame($expected, Format::isAriaLevel($input));
    }

    /**
     * @return array
     */
    public function scale10Provider()
    {
        return [
            // No precision, no X
            [2, '2.000000 x 10⁰'],
            [25, '2.500000 x 10¹'],
            [250, '2.500000 x 10²'],
            [2500, '2.500000 x 10³'],
            [250000, '2.500000 x 10⁵'],
            [2500000, '2.500000 x 10⁶'],
            [25000000, '2.500000 x 10⁷'],
            [250000000, '2.500000 x 10⁸'],
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
    public function invalidIdentifierFormatProvider()
    {
        return [
            ['3bad'],
            ['.bad'],
            ['好壞好'],
            ['ba[d'],
            [''],
        ];
    }

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
    public function validCoordinatesFormatProvider()
    {
        return [
            ['30,20,60,20'],
            ['20'],
            ['200 , 100, 40'],
        ];
    }

    /**
     * @return array
     */
    public function invalidCoordinatesFormatProvider()
    {
        return [
            ['30,20,x,20'],
            ['x'],
            ['invalid'],
        ];
    }

    /**
     * @return array
     */
    public function validUriFormatProvider()
    {
        return [
            ['http://www.taotesting.com'],
            ['../../index.html'],
        ];
    }

    /**
     * @return array
     */
    public function invalidUriFormatProvider()
    {
        return [
            // TODO: fix the isUri method because a relative path can be
            // accepted as a valid URI but not an empty string.
            // ['^'],
            [''],
            [12],
            [true],
            [['key' => 'value']],
        ];
    }

    /**
     * @return array
     */
    public function validBCP47LanguagesProvider()
    {
        return [
            ['en'],
            ['en-US'],
            ['es-419'],
            ['rm-sursilv'],
            ['gsw-u-sd-chzh'],
            ['nan-Hant-TW'],
        ];
    }

    /**
     * @return array
     */
    public function invalidBCP47LanguagesProvider()
    {
        return [
            [12],
            [['key' => 'value']],
            [true],
            [''],
            ['^'],
            ['"\'_-(/\\"'],
        ];
    }

    /**
     * @return array
     */
    public function validClassFormatProvider()
    {
        return [
            ['a'],
            ['my-class'],
            ['my-class my-other-class'],
            ['my-class    my-other-class'],
            ['theclass'],
            ['MYCLASS'],
            ['MY_CLASS'],
            ['my_class'],
            ['My_Class'],
        ];
    }

    /**
     * @return array
     */
    public function invalidClassFormatProvider()
    {
        return [
            ["a\tb"],
            [' '],
            [''],
            [false],
        ];
    }

    /**
     * @return array
     */
    public function validString256FormatProvider()
    {
        return [
            [''],
            ["\t\n\r"],
            ['Hello World!'],
            ['世界，你好！'] // Hello World!
        ];
    }

    /**
     * @return array
     */
    public function invalidString256FormatProvider()
    {
        return [
            ['Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla non pellentesque nunc. Interdum et malesuada fames ac ante ipsum primis in faucibus. Nunc adipiscing nisl ut risus facilisis faucibus. Morbi fermentum aliquet est et euismod. Praesent vitae adipiscing felis, ut lacinia velit. Aenean id suscipit nisi, eget feugiat tortor. Mauris eget nisi vitae mi commodo iaculis. Quisque sagittis massa in lectus semper ullamcorper. Morbi id sagittis massa. Aliquam massa dolor, pharetra nec sapien at, dignissim ultricies augue.'],
        ];
    }

    /**
     * @return array
     */
    public function validFileFormatProvider()
    {
        return [
            ['data'],
        ];
    }

    /**
     * @return array
     */
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
            ['Escape or not? %%s', false],
            ['Escape or not? %%%s', true],
            ['Escape or not? %%%%s', false],
            ['Escape or not? %%%%%s', true],
            ['%s bla %s and %%%s is %s and %%%%s', true],
            ['%%s bla %s and %%%s is %s and %%%%s', true],
            ['%%s bla %%s and %%%s is %s and %%%%s', true],
            ['%%s bla %%s and %%s is %s and %%%%s', true],
            ['%%s bla %%s and %%s is %%%%s and %%%%s', false],
            ['%s', true],
            ['%S', false],
            ['bla %S bli %s', true],
            ['blabla', false],
        ];
    }

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
    public function sanitizeProvider()
    {
        return [
            ['GoodIdentifier', 'GoodIdentifier'],
            ['abc 123', 'abc123'],
            ['@bc', 'bc'],
            ['-bc', 'bc'],
            ['---bc', 'bc'],
            ['-bc-', 'bc-'],
            ['2017id', 'id'],
            ['abc@@@', 'abc'],
            ['20i17d', 'i17d'],
            ['20id@@', 'id'],
            ['9bc', 'bc'],
            ['bc@', 'bc'],
        ];
    }

    /**
     * @return array
     */
    public function sanitizeProvider2()
    {
        return [
            [''],
            ['"'],
            ['123@'],
            [123],
            [12.3],
            [null],
            [false],
            [true],
            [[]],
            [new stdClass()],
        ];
    }

    /**
     * @return array
     */
    public function isAriaLevelProvider()
    {
        // input, expected
        return [
            [false, false],
            [true, false],
            ['-1', false],
            ['0', false],
            ['-20.4532', false],
            ['abc', false],
            [null, false],
            [new stdClass(), false],
            [-1, false],
            [0, false],
            [-20.5432, false],
            [1, true],
            ['1', true],
            [1000, true],
            ['1000', true],
            [1.453, true],
            [2.453, true],
            ['1.453', true],
            ['2.453', true],
        ];
    }

    /**
     * @dataProvider stringToBooleanProvider
     * @param bool $expected
     * @param string $string
     */
    public function testStringToBooleanWithValidValues(bool $expected, string $string)
    {
        $this->assertEquals($expected, Format::stringToBoolean($string));
    }

    public function stringToBooleanProvider(): array
    {
        return [
            [true, "  TrUe"],
            [false, '  FALSE '],
            [true, 'true'],
        ];
    }

    /**
     * @dataProvider  stringToBooleanInvalidProvider
     * @param mixed $string
     * @param mixed $given
     */
    public function testStringToBooleanWithInvalidValues($string, $given = null)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('String value "true" or "false" expected, "' . ($given ?? $string) . '" given.');
        Format::stringToBoolean($string);
    }

    public function stringToBooleanInvalidProvider(): array
    {
        return [
            [false, 'boolean'],
            [''],
            [78, 'integer'],
            ['robert'],
            [[], 'array'],
        ];
    }

    /**
     * @dataProvider isMimeTypeProvider
     * @param bool $expected
     * @param mixed $string
     */
    public function testIsMimeType(bool $expected, $string)
    {
        $this->assertEquals($expected, Format::isMimeType($string));
    }

    public function isMimeTypeProvider(): array
    {
        return [
            [true, 'video/webm'],
            [true, 'text/plain'],
            [true, 'application/octet-stream'],
            [true, 'audio/ogg'],
            [true, 'x-conference/x-cooltalk'],
            [true, 'application/vnd.adobe.air-application-installer-package+zip'],
            [true, 'image/jpeg'],
            [true, 'application/rdf+xml'],
            [false, false],
            [false, ''],
            [false, 78],
            [false, 'robert'],
            [false, []],
        ];
    }

    /**
     * @dataProvider isNormalizedStringProvider
     * @param bool $expected
     * @param mixed $string
     */
    public function testIsNormalizedString(bool $expected, $string)
    {
        $this->assertEquals($expected, Format::isNormalizedString($string));
    }

    public function isNormalizedStringProvider(): array
    {
        return [
            [true, 'plain text'],
            [true, 'text with weird characters like: éàçùè'],
            [true, '\/:?;324_èé'],
            [true, ''],
            [false, "some \t text with   weird    spaces "],
            [false, "some \t text with \r  line    breaks \n"],
            [false, false],
            [false, 1012],
            [false, []],
        ];
    }
}
