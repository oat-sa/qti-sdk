<?php

namespace qtismtest\common\utils;

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
    public function testValidIdentifierFormat(string $string): void
    {
        self::assertTrue(Format::isIdentifier($string));
    }

    /**
     * @dataProvider invalidIdentifierFormatProvider
     * @param string $string
     */
    public function testInvalidIdentifierFormat(string $string): void
    {
        self::assertFalse(Format::isIdentifier($string));
    }

    /**
     * @dataProvider validVariableRefFormatProvider
     * @param string $string
     */
    public function testValidVariableRefFormat(string $string): void
    {
        self::assertTrue(Format::isVariableRef($string));
    }

    /**
     * @dataProvider invalidVariableRefFormatProvider
     * @param string $string
     */
    public function testInvalidVariableRefFormat(string $string): void
    {
        self::assertFalse(Format::isVariableRef($string));
    }

    /**
     * @dataProvider validCoordinatesFormatProvider
     * @param string $string
     */
    public function testValidCoordinatesFormat(string $string): void
    {
        self::assertTrue(Format::isCoords($string));
    }

    /**
     * @dataProvider invalidCoordinatesFormatProvider
     * @param string $string
     */
    public function testInvalidCoordinatesFormat(string $string): void
    {
        self::assertFalse(Format::isCoords($string));
    }

    /**
     * @dataProvider validUriFormatProvider
     * @param mixed $string
     */
    public function testValidUriFormat($string): void
    {
        self::assertTrue(Format::isUri($string));
    }

    /**
     * @dataProvider invalidUriFormatProvider
     * @param string $string
     */
    public function testInvalidUriFormat(string $string): void
    {
        // TODO: fix the isUri method because a relative path can be
        // accepted as a valid URI but not an empty string.
        self::assertFalse(Format::isUri($string));
    }

    /**
     * @dataProvider validBCP47LanguagesProvider
     * @param string $string
     */
    public function testValidBCP47LanguageFormat(string $string): void
    {
        $this::assertTrue(Format::isBCP47Lang($string));
    }

    /**
     * @dataProvider invalidBCP47LanguagesProvider
     * @param mixed $string
     */
    public function testInvalidBCP47LanguageFormat($string): void
    {
        $this::assertFalse(Format::isBCP47Lang($string));
    }

    /**
     * @dataProvider validClassFormatProvider
     * @param string $string
     */
    public function testValidClassFormatProvider(string $string): void
    {
        self::assertTrue(Format::isClass($string));
    }

    /**
     * @dataProvider invalidClassFormatProvider
     * @param mixed $string
     */
    public function testInvalidClassFormatProvider($string): void
    {
        self::assertFalse(Format::isClass($string));
    }

    /**
     * @dataProvider validString256FormatProvider
     * @param string $string
     */
    public function testValidString256Provider(string $string): void
    {
        self::assertTrue(Format::isString256($string));
    }

    /**
     * @dataProvider invalidString256FormatProvider
     * @param string $string
     */
    public function testInvalidString256Provider(string $string): void
    {
        self::assertFalse(Format::isString256($string));
    }

    /**
     * @dataProvider validFileFormatProvider
     * @param string $string
     */
    public function testValidFile(string $string): void
    {
        self::assertTrue(Format::isFile($string));
    }

    /**
     * @dataProvider scale10Provider
     * @param float $float
     * @param string $expected
     * @param string $x
     * @param int|bool $precision
     */
    public function testScale10(float $float, string $expected, string $x = 'x', $precision = false): void
    {
        self::assertEquals($expected, Format::scale10($float, $x, $precision));
    }

    /**
     * @dataProvider isPrintfIsoFormatProvider
     * @param string $input
     * @param bool $expected
     */
    public function testIsPrintfIsoFormat(string $input, bool $expected): void
    {
        self::assertEquals($expected, Format::isPrintfIsoFormat($input));
    }

    /**
     * @dataProvider printfFormatIsoToPhpProvider
     * @param string $input
     * @param string $expected
     */
    public function testPrintfFormatIsoToPhp(string $input, string $expected): void
    {
        self::assertEquals($expected, Format::printfFormatIsoToPhp($input));
    }

    /**
     * @dataProvider isXhtmlLengthProvider
     * @param mixed $input
     * @param bool $expected
     */
    public function testIsXhtmlLength($input, bool $expected): void
    {
        self::assertSame($expected, Format::isXhtmlLength($input));
    }

    /**
     * @dataProvider sanitizeProvider
     * @param string $dirty
     * @param string $clean
     */
    public function testSanitizeIdentifier(string $dirty, string $clean): void
    {
        self::assertEquals($clean, Format::sanitizeIdentifier($dirty));
    }

    /**
     * @dataProvider sanitizeProvider2
     * @param mixed $dirty
     */
    public function testSanitizeIdentifier2($dirty): void
    {
        self::assertTrue(Format::isIdentifier(Format::sanitizeIdentifier($dirty), false));
    }

    /**
     * @dataProvider isAriaLevelProvider
     * @param mixed $input
     * @param bool $expected
     */
    public function testIsAriaLevel($input, bool $expected): void
    {
        self::assertSame($expected, Format::isAriaLevel($input));
    }

    public function scale10Provider(): array
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

    public function validIdentifierFormatProvider(): array
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

    public function invalidIdentifierFormatProvider(): array
    {
        return [
            ['3bad'],
            ['.bad'],
            ['好壞好'],
            ['ba[d'],
            [''],
        ];
    }

    public function validVariableRefFormatProvider(): array
    {
        return [
            ['{_good}'],
            ['{g0od}'],
            ['{_-goOd3}'],
            ['{g.0.o.d...}'],
            ['{_壞壞}'],
            ['{myWeight1}'],
            ['{myIdentifier1}'],
            ['myIdentifier1'],
        ];
    }

    public function invalidVariableRefFormatProvider(): array
    {
        return [
            ['3bad'],
            ['{.bad'],
            ['好壞好}'],
            ['{}'],
            ['{{}}'],
        ];
    }

    public function validCoordinatesFormatProvider(): array
    {
        return [
            ['30,20,60,20'],
            ['20'],
            ['200 , 100, 40'],
        ];
    }

    public function invalidCoordinatesFormatProvider(): array
    {
        return [
            ['30,20,x,20'],
            ['x'],
            ['invalid'],
        ];
    }

    public function validUriFormatProvider(): array
    {
        return [
            ['http://www.taotesting.com'],
            ['../../index.html'],
            // TODO: fix the isUri method because now it's only testing whether
            // parameter is a non-empty string.
            [12],
            [true],
            [
                new class() {
                    public function __toString(): string
                    {
                        return 'any-random-string';
                    }
                },
            ],
        ];
    }

    public function invalidUriFormatProvider(): array
    {
        return [
            // ['^'],
            [''],
        ];
    }

    public function validBCP47LanguagesProvider(): array
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

    public function invalidBCP47LanguagesProvider(): array
    {
        return [
            [12],
            [true],
            [''],
            ['^'],
            ['"\'_-(/\\"'],
        ];
    }

    public function validClassFormatProvider(): array
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

    public function invalidClassFormatProvider(): array
    {
        return [
            ["a\tb"],
            [' '],
            [''],
            [false],
        ];
    }

    public function validString256FormatProvider(): array
    {
        return [
            [''],
            ["\t\n\r"],
            ['Hello World!'],
            ['世界，你好！'] // Hello World!
        ];
    }

    public function invalidString256FormatProvider(): array
    {
        return [
            ['Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla non pellentesque nunc. Interdum et malesuada fames ac ante ipsum primis in faucibus. Nunc adipiscing nisl ut risus facilisis faucibus. Morbi fermentum aliquet est et euismod. Praesent vitae adipiscing felis, ut lacinia velit. Aenean id suscipit nisi, eget feugiat tortor. Mauris eget nisi vitae mi commodo iaculis. Quisque sagittis massa in lectus semper ullamcorper. Morbi id sagittis massa. Aliquam massa dolor, pharetra nec sapien at, dignissim ultricies augue.'],
        ];
    }

    public function validFileFormatProvider(): array
    {
        return [
            ['data'],
        ];
    }

    public function isPrintfIsoFormatProvider(): array
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

    public function printfFormatIsoToPhpProvider(): array
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

    public function isXhtmlLengthProvider(): array
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

    public function sanitizeProvider(): array
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

    public function sanitizeProvider2(): array
    {
        return [
            [''],
            ['"'],
            ['123@'],
            [123],
            [12.3],
            [false],
            [true],
        ];
    }

    public function isAriaLevelProvider(): array
    {
        // input, expected
        return [
            [false, false],
            [true, true],
            ['-1', false],
            ['0', false],
            ['-20.4532', false],
            ['abc', false],
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
     * @dataProvider isMimeTypeProvider
     * @param bool $expected
     * @param mixed $string
     */
    public function testIsMimeType(bool $expected, $string): void
    {
        $this::assertEquals($expected, Format::isMimeType($string));
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
        ];
    }

    /**
     * @dataProvider isNormalizedStringProvider
     * @param bool $expected
     * @param mixed $string
     */
    public function testIsNormalizedString(bool $expected, $string): void
    {
        $this::assertEquals($expected, Format::isNormalizedString($string));
    }

    public function isNormalizedStringProvider(): array
    {
        return [
            [true, 'plain text'],
            [true, 'text with weird characters like: éàçùè'],
            [true, '\/:?;324_èé'],
            [true, ''],
            [true, 1012],
            [true, false],
            [false, "some \t text with   weird    spaces "],
            [false, "some \t text with \r  line    breaks \n"],
        ];
    }
}
