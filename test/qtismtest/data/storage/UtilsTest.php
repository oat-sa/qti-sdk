<?php

namespace qtismtest\data\storage;

use InvalidArgumentException;
use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiShape;
use qtism\common\enums\BaseType;
use qtism\data\storage\Utils;
use qtismtest\QtiSmTestCase;
use RuntimeException;
use stdClass;
use UnexpectedValueException;

/**
 * Class UtilsTest
 */
class UtilsTest extends QtiSmTestCase
{
    /**
     * @dataProvider validIntegerProvider
     * @param string $string
     * @param int $expected
     */
    public function testStringToInteger(string $string, int $expected): void
    {
        $value = Utils::stringToDatatype($string, BaseType::INTEGER);
        self::assertSame($expected, $value);
    }

    /**
     * @dataProvider invalidIntegerProvider
     * @param string $string
     */
    public function testStringToIntegerInvalid($string): void
    {
        $this->expectException(UnexpectedValueException::class);
        Utils::stringToDatatype($string, BaseType::INTEGER);
    }

    /**
     * @dataProvider validFloatProvider
     * @param string $string
     * @param float $expected
     */
    public function testStringToFloatValid(string $string, float $expected): void
    {
        $value = Utils::stringToDatatype($string, BaseType::FLOAT);
        self::assertSame($expected, $value);
    }

    /**
     * @dataProvider invalidFloatProvider
     * @param string $string
     */
    public function testStringToFloatInvalid(string $string): void
    {
        $this->expectException(UnexpectedValueException::class);
        Utils::stringToDatatype($string, BaseType::FLOAT);
    }

    /**
     * @dataProvider validBooleanProvider
     * @param string $string
     * @param bool $expected
     */
    public function testStringToBooleanValid(string $string, bool $expected): void
    {
        $value = Utils::stringToDatatype($string, BaseType::BOOLEAN);
        self::assertSame($expected, $value);
    }

    /**
     * @dataProvider invalidBooleanProvider
     * @param mixed $string
     */
    public function testStringToBooleanInvalid($string): void
    {
        $this->expectException(UnexpectedValueException::class);
        Utils::stringToDatatype($string, BaseType::BOOLEAN);
    }

    /**
     * @dataProvider validIntOrIdentifierProvider
     * @param string $string
     * @param mixed $expected
     */
    public function testIntOrIdentifierValid(string $string, $expected): void
    {
        $value = Utils::stringToDatatype($string, BaseType::INT_OR_IDENTIFIER);
        self::assertSame($expected, $value);
    }

    /**
     * @dataProvider invalidIntOrIdentifierProvider
     * @param mixed $string
     */
    public function testIntOrIdentifierInvalid($string): void
    {
        $this->expectException(UnexpectedValueException::class);
        Utils::stringToDatatype($string, BaseType::INT_OR_IDENTIFIER);
    }

    /**
     * @dataProvider validPointProvider
     * @param string $string
     * @param QtiPoint $expected
     */
    public function testStringToPointValid(string $string, QtiPoint $expected): void
    {
        $value = Utils::stringToDatatype($string, BaseType::POINT);
        self::assertEquals($expected, $value);
    }

    /**
     * @dataProvider invalidPointProvider
     * @param string $string
     */
    public function testStringToPointInvalid(string $string): void
    {
        $this->expectException(UnexpectedValueException::class);
        Utils::stringToDatatype($string, BaseType::POINT);
    }

    /**
     * @dataProvider validDurationProvider
     * @param string $string
     * @param QtiDuration $expected
     */
    public function testStringToDurationValid(string $string, QtiDuration $expected): void
    {
        $value = Utils::stringToDatatype($string, BaseType::DURATION);
        self::assertEquals($expected, $value);
    }

    /**
     * @dataProvider invalidDurationProvider
     * @param string $string
     */
    public function testStringToDurationInvalid(string $string): void
    {
        $this->expectException(UnexpectedValueException::class);
        Utils::stringToDatatype($string, BaseType::DURATION);
    }

    /**
     * @dataProvider validPairProvider
     * @param string $string
     * @param QtiPair $expected
     */
    public function testStringToPairValid(string $string, QtiPair $expected): void
    {
        $value = Utils::stringToDatatype($string, BaseType::PAIR);
        self::assertEquals($expected, $value);
    }

    /**
     * @dataProvider invalidPairProvider
     * @param string $string
     */
    public function testStringToPairInvalid(string $string): void
    {
        $this->expectException(UnexpectedValueException::class);
        Utils::stringToDatatype($string, BaseType::PAIR);
    }

    /**
     * @dataProvider validDirectedPairProvider
     * @param string $string
     * @param QtiDirectedPair $expected
     */
    public function testStringToDirectedPairValid(string $string, QtiDirectedPair $expected): void
    {
        $value = Utils::stringToDatatype($string, BaseType::DIRECTED_PAIR);
        self::assertEquals($expected, $value);
    }

    /**
     * @dataProvider invalidPairProvider
     * @param string $string
     */
    public function testStringToDirectedPairInvalid(string $string): void
    {
        $this->expectException(UnexpectedValueException::class);
        Utils::stringToDatatype($string, BaseType::DIRECTED_PAIR);
    }

    /**
     * @dataProvider validCoordsProvider
     * @param string $string
     * @param int $shape
     */
    public function testStringToCoords(string $string, int $shape): void
    {
        $coords = Utils::stringToCoords($string, $shape);
        self::assertInstanceOf(QtiCoords::class, $coords);

        $intCoords = explode(',', $string);
        self::assertEquals(count($intCoords), count($coords));

        for ($i = 0; $i < count($intCoords); $i++) {
            self::assertEquals((int)$intCoords[$i], $coords[$i]);
        }
    }

    /**
     * @dataProvider invalidCoordsProvider
     * @param string $string
     * @param QtiShape $shape
     */
    public function testStringToCoordsInvalid(string $string, int $shape): void
    {
        $this->expectException(UnexpectedValueException::class);
        Utils::stringToCoords($string, $shape);
    }

    /**
     * @dataProvider invalidShapeProvider
     * @param string $string
     * @param mixed $shape
     */
    public function testStringToCoordsInvalidShapes(string $string, int $shape): void
    {
        $this->expectException(InvalidArgumentException::class);
        Utils::stringToCoords($string, $shape);
    }

    /**
     * @dataProvider validUriToSanitizeProvider
     * @param string $uri
     * @param string $expected
     */
    public function testValidUriToSanitize(string $uri, string $expected): void
    {
        self::assertEquals($expected, Utils::sanitizeUri($uri));
    }

    /**
     * @dataProvider invalidUriToSanitizeProvider
     * @param mixed $uri
     */
    public function testInvalidUriToSanitize($uri): void
    {
        $this->expectException(InvalidArgumentException::class);
        Utils::sanitizeUri($uri);
    }

    public function testUnsupportedFile(): void
    {
        $this->expectException(RuntimeException::class);
        Utils::stringToDatatype('not supported', BaseType::FILE);
    }

    public function testUnknownType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Utils::stringToDatatype('test', -1);
    }

    public function validCoordsProvider(): array
    {
        return [
            ['30, 30, 60, 30', QtiShape::RECT],
            ['10, 10, 10', QtiShape::CIRCLE],
            ['10,10,10', QtiShape::CIRCLE],
            ['0,8,7,4,2,2,8,-4,-2,1', QtiShape::POLY],
            ['30.1, 30, 50, 30.1', QtiShape::RECT],
            ['184,237,18.38', QtiShape::CIRCLE],
            ['-184 ,237, -18.38', QtiShape::CIRCLE],
        ];
    }

    public function invalidCoordsProvider(): array
    {
        return [
            ['invalid', QtiShape::RECT],
            ['20;40;30', QtiShape::CIRCLE],
            ['184.456,237.,18', QtiShape::CIRCLE],
        ];
    }

    public function invalidShapeProvider(): array
    {
        return [
            ['10, 10, 10', QtiShape::DEF],
            ['10', 25],
        ];
    }

    public function validIntegerProvider(): array
    {
        return [
            ['25', 25],
            [' 25', 25],
            ['25 ', 25],
            ['0', 0],
            ['-0', 0],
            ['-150', -150],
            [' -150', -150],
            ['-150 ', -150],
        ];
    }

    public function invalidIntegerProvider(): array
    {
        return [
            ['25.234'],
            ['A B'],
            ['-'],
            ['+'],
            ['abcd'],
            ['-bd'],
        ];
    }

    public function validFloatProvider(): array
    {
        return [
            ['25.234', 25.234],
            ['25', (float)25],
            ['-25', -(float)25],
            ['-25.234', -25.234],
            ['25.0', 25.0],
        ];
    }

    public function invalidFloatProvider(): array
    {
        return [
            ['2a'],
            ['A B'],
            ['-'],
            ['+'],
            ['abcd'],
            ['-bd'],
        ];
    }

    public function validBooleanProvider(): array
    {
        return [
            ['true', true],
            ['false', false],
            ['  true', true],
            ['false ', false],
        ];
    }

    public function invalidBooleanProvider(): array
    {
        return [
            ['tru'],
            [''],
            ['f'],
            [24],
        ];
    }

    public function validPointProvider(): array
    {
        return [
            ['20 30', new QtiPoint(20, 30)],
            ['240 30', new QtiPoint(240, 30)],
            ['-10 3', new QtiPoint(-10, 3)],
        ];
    }

    public function invalidPointProvider(): array
    {
        return [
            ['20 x'],
            ['x  y'],
            ['xy'],
            ['x y'],
            ['20px 20em'],
            ['20'],
            [''],
        ];
    }

    public function validDurationProvider(): array
    {
        return [
            ['P1D', new QtiDuration('P1D')], // 1 day
            ['P2W', new QtiDuration('P2W')], // 2 weeks
            ['P3M', new QtiDuration('P3M')], // 3 months
            ['P4Y', new QtiDuration('P4Y')], // 4 years
            ['P1Y1D', new QtiDuration('P1Y1D')], // 1 year + 1 day
            ['P1DT12H', new QtiDuration('P1DT12H')], // 1 day + 12 hours
            ['PT3600S', new QtiDuration('PT3600S')] // 3600 seconds
        ];
    }

    public function invalidDurationProvider(): array
    {
        return [
            ['D1P'],
            ['3600'],
            [''],
            ['abcdef'],
        ];
    }

    public function validPairProvider(): array
    {
        return [
            ['Bidule Trucmuche', new QtiPair('Bidule', 'Trucmuche')],
            ['C D', new QtiPair('C', 'D')],
        ];
    }

    public function validDirectedPairProvider(): array
    {
        return [
            ['Bidule Trucmuche', new QtiDirectedPair('Bidule', 'Trucmuche')],
            ['C D', new QtiDirectedPair('C', 'D')],
        ];
    }

    public function invalidPairProvider(): array
    {
        return [
            ['Machinbrol'],
            ['bidule 0'],
            [''],
        ];
    }

    /**
     * @return array
     */
    public function validUriToSanitizeProvider(): array
    {
        return [
            ['http://www.taotesting.com/', 'http://www.taotesting.com'],
            ['', ''],
            ['http://taotesting.com', 'http://taotesting.com'],
            ['./', '.'],
            ['../', '..'],
            ['/../../q01.xml', '/../../q01.xml'],
            ['./../../q01.xml/', './../../q01.xml'],
            ['/', ''],
        ];
    }

    public function invalidUriToSanitizeProvider(): array
    {
        return [
            [new stdClass()],
            [14],
            [true],
        ];
    }

    public function validIntOrIdentifierProvider(): array
    {
        return [
            ['identifier', 'identifier', 'string'],
            ['1337', 1337, 'integer'],
        ];
    }

    public function invalidIntOrIdentifierProvider(): array
    {
        return [
            [3.3],
            ['9_xxx'],
        ];
    }
}
