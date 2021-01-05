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
    public function testStringToInteger($string, $expected)
    {
        $value = Utils::stringToDatatype($string, BaseType::INTEGER);
        $this->assertIsInt($value);
        $this->assertTrue($value === $expected);
    }

    /**
     * @dataProvider invalidIntegerProvider
     * @param string $string
     */
    public function testStringToIntegerInvalid($string)
    {
        $this->expectException(UnexpectedValueException::class);
        $value = Utils::stringToDatatype($string, BaseType::INTEGER);
    }

    /**
     * @dataProvider validFloatProvider
     * @param string $string
     * @param float $expected
     */
    public function testStringToFloatValid($string, $expected)
    {
        $value = Utils::stringToDatatype($string, BaseType::FLOAT);
        $this->assertIsFloat($value);
        $this->assertTrue($value === $expected);
    }

    /**
     * @dataProvider invalidFloatProvider
     * @param string $string
     */
    public function testStringToFloatInvalid($string)
    {
        $this->expectException(UnexpectedValueException::class);
        $value = Utils::stringToDatatype($string, BaseType::FLOAT);
    }

    /**
     * @dataProvider validBooleanProvider
     * @param string $string
     * @param bool $expected
     */
    public function testStringToBooleanValid($string, $expected)
    {
        $value = Utils::stringToDatatype($string, BaseType::BOOLEAN);
        $this->assertIsBool($value);
        $this->assertTrue($expected === $value);
    }

    /**
     * @dataProvider validIntOrIdentifierProvider
     *
     * @param $string
     * @param $expected
     * @param $type
     */
    public function testIntOrIdentifierValid($string, $expected, $type)
    {
        $value = Utils::stringToDatatype($string, BaseType::INT_OR_IDENTIFIER);
        $this->assertIsString($type);
        $this->assertTrue($expected === $value);
    }

    /**
     * @dataProvider invalidIntOrIdentifierProvider
     * @param $string
     */
    public function testIntOrIdentifierInvalid($string)
    {
        $this->expectException(UnexpectedValueException::class);
        Utils::stringToDatatype($string, BaseType::INT_OR_IDENTIFIER);
    }

    /**
     * @dataProvider invalidBooleanProvider
     * @param string $string
     */
    public function testStringToBooleanInvalid($string)
    {
        $this->expectException(UnexpectedValueException::class);
        $value = Utils::stringToDatatype($string, BaseType::BOOLEAN);
    }

    /**
     * @dataProvider validPointProvider
     * @param string $string
     * @param QtiPoint $expected
     */
    public function testStringToPointValid($string, $expected)
    {
        $value = Utils::stringToDatatype($string, BaseType::POINT);
        $this->assertIsInt($value->getX());
        $this->assertIsInt($value->getY());
        $this->assertEquals($expected->getX(), $value->getX());
        $this->assertEquals($expected->getY(), $value->getY());
    }

    /**
     * @dataProvider invalidPointProvider
     * @param string $string
     */
    public function testStringToPointInvalid($string)
    {
        $this->expectException(UnexpectedValueException::class);
        $value = Utils::stringToDatatype($string, BaseType::POINT);
    }

    /**
     * @dataProvider validDurationProvider
     * @param string $string
     * @param QtiDuration $expected
     */
    public function testStringToDurationValid($string, $expected)
    {
        $value = Utils::stringToDatatype($string, BaseType::DURATION);
        $this->assertInstanceOf(QtiDuration::class, $value);
        $this->assertEquals($value->getDays(), $expected->getDays());
        $this->assertEquals($value->getYears(), $expected->getYears());
        $this->assertEquals($value->getHours(), $expected->getHours());
        $this->assertEquals($value->getMinutes(), $expected->getMinutes());
        $this->assertEquals($value->getMonths(), $expected->getMonths());
        $this->assertEquals($value->getSeconds(), $expected->getSeconds());
    }

    /**
     * @dataProvider invalidDurationProvider
     * @param string $string
     */
    public function testStringToDurationInvalid($string)
    {
        $this->expectException(UnexpectedValueException::class);
        $value = Utils::stringToDatatype($string, BaseType::DURATION);
    }

    /**
     * @dataProvider validPairProvider
     * @param string $string
     * @param QtiPair $expected
     */
    public function testStringToPairValid($string, $expected)
    {
        $value = Utils::stringToDatatype($string, BaseType::PAIR);
        $this->assertInstanceOf(QtiPair::class, $value);
        $this->assertEquals($expected->getFirst(), $value->getFirst());
        $this->assertEquals($expected->getSecond(), $value->getSecond());
    }

    /**
     * @dataProvider invalidPairProvider
     * @param string $string
     */
    public function testStringToPairInvalid($string)
    {
        $this->expectException(UnexpectedValueException::class);
        $value = Utils::stringToDatatype($string, BaseType::PAIR);
    }

    /**
     * @dataProvider validPairProvider
     * @param string $string
     * @param QtiDirectedPair $expected
     */
    public function testStringToDirectedPairValid($string, $expected)
    {
        $value = Utils::stringToDatatype($string, BaseType::DIRECTED_PAIR);
        $this->assertInstanceOf(QtiDirectedPair::class, $value);
        $this->assertEquals($expected->getFirst(), $value->getFirst());
        $this->assertEquals($expected->getSecond(), $value->getSecond());
    }

    /**
     * @dataProvider invalidPairProvider
     * @param string $string
     */
    public function testStringToDirectedPairInvalid($string)
    {
        $this->expectException(UnexpectedValueException::class);
        $value = Utils::stringToDatatype($string, BaseType::DIRECTED_PAIR);
    }

    /**
     * @dataProvider validCoordsProvider
     * @param string $string
     * @param QtiShape $shape
     */
    public function testStringToCoords($string, $shape)
    {
        $coords = Utils::stringToCoords($string, $shape);
        $this->assertInstanceOf(QtiCoords::class, $coords);

        $intCoords = explode(',', $string);
        $this->assertEquals(count($intCoords), count($coords));

        for ($i = 0; $i < count($intCoords); $i++) {
            $this->assertEquals((int)$intCoords[$i], $coords[$i]);
        }
    }

    /**
     * @dataProvider invalidCoordsProvider
     * @param string $string
     * @param QtiShape $shape
     */
    public function testStringToCoordsInvalid($string, $shape)
    {
        $this->expectException(UnexpectedValueException::class);
        $coords = Utils::stringToCoords($string, $shape);
    }

    /**
     * @dataProvider invalidShapeProvider
     * @param string $string
     * @param mixed $shape
     */
    public function testStringToCoordsInvalidShapes($string, $shape)
    {
        $this->expectException(InvalidArgumentException::class);
        $coords = Utils::stringToCoords($string, $shape);
    }

    /**
     * @dataProvider validUriToSanitizeProvider
     * @param string $uri
     * @param string $expected
     */
    public function testValidUriToSanitize($uri, $expected)
    {
        $this->assertEquals($expected, Utils::sanitizeUri($uri));
    }

    /**
     * @dataProvider invalidUriToSanitizeProvider
     * @param string $uri
     */
    public function testInvalidUriToSanitize($uri)
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = Utils::sanitizeUri($uri);
    }

    public function testUnsupportedFile()
    {
        $this->expectException(RuntimeException::class);
        Utils::stringToDatatype('not supported', BaseType::FILE);
    }

    public function testUnknownType()
    {
        $this->expectException(InvalidArgumentException::class);
        Utils::stringToDatatype('test', 'test');
    }

    /**
     * @return array
     */
    public function validCoordsProvider()
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

    /**
     * @return array
     */
    public function invalidCoordsProvider()
    {
        return [
            ['invalid', QtiShape::RECT],
            ['20;40;30', QtiShape::CIRCLE],
            ['184.456,237.,18', QtiShape::CIRCLE],
        ];
    }

    /**
     * @return array
     */
    public function invalidShapeProvider()
    {
        return [
            ['10, 10, 10', QtiShape::DEF],
            ['10', 25],
        ];
    }

    /**
     * @return array
     */
    public function validIntegerProvider()
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

    /**
     * @return array
     */
    public function invalidIntegerProvider()
    {
        return [
            ['25.234'],
            ['A B'],
            ['-'],
            ['+'],
            ['abcd'],
            ['-bd'],
            [null],
        ];
    }

    /**
     * @return array
     */
    public function validFloatProvider()
    {
        return [
            ['25.234', 25.234],
            ['25', (float)25],
            ['-25', -(float)25],
            ['-25.234', -25.234],
            ['25.0', 25.0],
        ];
    }

    /**
     * @return array
     */
    public function invalidFloatProvider()
    {
        return [
            ['2a'],
            ['A B'],
            ['-'],
            ['+'],
            ['abcd'],
            ['-bd'],
            [null],
        ];
    }

    /**
     * @return array
     */
    public function validBooleanProvider()
    {
        return [
            ['true', true],
            ['false', false],
            ['  true', true],
            ['false ', false],
        ];
    }

    /**
     * @return array
     */
    public function invalidBooleanProvider()
    {
        return [
            ['tru'],
            [''],
            ['f'],
            [null],
            [24],
        ];
    }

    /**
     * @return array
     */
    public function validPointProvider()
    {
        return [
            ['20 30', new QtiPoint(20, 30)],
            ['240 30', new QtiPoint(240, 30)],
            ['-10 3', new QtiPoint(-10, 3)],
        ];
    }

    /**
     * @return array
     */
    public function invalidPointProvider()
    {
        return [
            ['20 x'],
            ['x  y'],
            ['xy'],
            ['x y'],
            ['20px 20em'],
            ['20'],
            [''],
            [null],
        ];
    }

    /**
     * @return array
     */
    public function validDurationProvider()
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

    /**
     * @return array
     */
    public function invalidDurationProvider()
    {
        return [
            ['D1P'],
            ['3600'],
            [''],
            ['abcdef'],
            [null],
        ];
    }

    /**
     * @return array
     */
    public function validPairProvider()
    {
        return [
            ['Bidule Trucmuche', new QtiPair('Bidule', 'Trucmuche')],
            ['C D', new QtiPair('C', 'D')],
        ];
    }

    /**
     * @return array
     */
    public function invalidPairProvider()
    {
        return [
            ['Machinbrol'],
            ['bidule 0'],
            [''],
            [null],
        ];
    }

    /**
     * @return array
     */
    public function validUriToSanitizeProvider()
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

    /**
     * @return array
     */
    public function invalidUriToSanitizeProvider()
    {
        return [
            [new stdClass()],
            [14],
            [true],
        ];
    }

    /**
     * @return array
     */
    public function validIntOrIdentifierProvider()
    {
        return [
            ['identifier', 'identifier', 'string'],
            ['1337', 1337, 'integer'],
        ];
    }

    /**
     * @return array
     */
    public function invalidIntOrIdentifierProvider()
    {
        return [
            [3.3],
            ['9_xxx'],
        ];
    }
}
