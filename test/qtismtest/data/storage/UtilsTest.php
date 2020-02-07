<?php

use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiShape;
use qtism\common\enums\BaseType;
use qtism\data\storage\Utils;

require_once(dirname(__FILE__) . '/../../../QtiSmTestCase.php');

class UtilsTest extends QtiSmTestCase
{
    /**
     * @dataProvider validIntegerProvider
     */
    public function testStringToInteger($string, $expected)
    {
        $value = Utils::stringToDatatype($string, BaseType::INTEGER);
        $this->assertInternalType('integer', $value);
        $this->assertTrue($value === $expected);
    }

    /**
     * @dataProvider invalidIntegerProvider
     */
    public function testStringToIntegerInvalid($string)
    {
        $this->setExpectedException('\\UnexpectedValueException');
        $value = Utils::stringToDatatype($string, BaseType::INTEGER);
    }

    /**
     * @dataProvider validFloatProvider
     */
    public function testStringToFloatValid($string, $expected)
    {
        $value = Utils::stringToDatatype($string, BaseType::FLOAT);
        $this->assertInternalType('float', $value);
        $this->assertTrue($value === $expected);
    }

    /**
     * @dataProvider invalidFloatProvider
     */
    public function testStringToFloatInvalid($string)
    {
        $this->setExpectedException('\\UnexpectedValueException');
        $value = Utils::stringToDatatype($string, BaseType::FLOAT);
    }

    /**
     * @dataProvider validBooleanProvider
     */
    public function testStringToBooleanValid($string, $expected)
    {
        $value = Utils::stringToDatatype($string, BaseType::BOOLEAN);
        $this->assertInternalType('boolean', $value);
        $this->assertTrue($expected === $value);
    }

    /**
     * @dataProvider invalidBooleanProvider
     */
    public function testStringToBooleanInvalid($string)
    {
        $this->setExpectedException('\\UnexpectedValueException');
        $value = Utils::stringToDatatype($string, BaseType::BOOLEAN);
    }

    /**
     * @dataProvider validPointProvider
     */
    public function testStringToPointValid($string, $expected)
    {
        $value = Utils::stringToDatatype($string, BaseType::POINT);
        $this->assertInternalType('integer', $value->getX());
        $this->assertInternalType('integer', $value->getY());
        $this->assertEquals($expected->getX(), $value->getX());
        $this->assertEquals($expected->getY(), $value->getY());
    }

    /**
     * @dataProvider invalidPointProvider
     */
    public function testStringToPointInvalid($string)
    {
        $this->setExpectedException('\\UnexpectedValueException');
        $value = Utils::stringToDatatype($string, BaseType::POINT);
    }

    /**
     * @dataProvider validDurationProvider
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
     */
    public function testStringToDurationInvalid($string)
    {
        $this->setExpectedException('\\UnexpectedValueException');
        $value = Utils::stringToDatatype($string, BaseType::DURATION);
    }

    /**
     * @dataProvider validPairProvider
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
     */
    public function testStringToPairInvalid($string)
    {
        $this->setExpectedException('\\UnexpectedValueException');
        $value = Utils::stringToDatatype($string, BaseType::PAIR);
    }

    /**
     * @dataProvider validPairProvider
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
     */
    public function testStringToDirectedPairInvalid($string)
    {
        $this->setExpectedException('\\UnexpectedValueException');
        $value = Utils::stringToDatatype($string, BaseType::PAIR);
    }

    /**
     * @dataProvider validCoordsProvider
     */
    public function testStringToCoords($string, $shape)
    {
        $coords = Utils::stringToCoords($string, $shape);
        $this->assertInstanceOf(QtiCoords::class, $coords);

        $intCoords = explode(",", $string);
        $this->assertEquals(count($intCoords), count($coords));

        for ($i = 0; $i < count($intCoords); $i++) {
            $this->assertEquals(intval($intCoords[$i]), $coords[$i]);
        }
    }

    /**
     * @dataProvider invalidCoordsProvider
     */
    public function testStringToCoordsInvalid($string, $shape)
    {
        $this->setExpectedException('\\UnexpectedValueException');
        $coords = Utils::stringToCoords($string, $shape);
    }

    /**
     * @dataProvider invalidShapeProvider
     */
    public function testStringToCoordsInvalidShapes($string, $shape)
    {
        $this->setExpectedException('\\InvalidArgumentException');
        $coords = Utils::stringToCoords($string, $shape);
    }

    /**
     * @dataProvider validUriToSanitizeProvider
     */
    public function testValidUriToSanitize($uri, $expected)
    {
        $this->assertEquals($expected, Utils::sanitizeUri($uri));
    }

    /**
     * @dataProvider invalidUriToSanitizeProvider
     */
    public function testInvalidUriToSanitize($uri)
    {
        $this->setExpectedException('\\InvalidArgumentException');
        $uri = Utils::sanitizeUri($uri);
    }

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

    public function invalidCoordsProvider()
    {
        return [
            ['invalid', QtiShape::RECT],
            ['20;40;30', QtiShape::CIRCLE],
            ['184.456,237.,18', QtiShape::CIRCLE],
        ];
    }

    public function invalidShapeProvider()
    {
        return [
            ['10, 10, 10', QtiShape::DEF],
            ['10', 25],
        ];
    }

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

    public function validFloatProvider()
    {
        return [
            ['25.234', 25.234],
            ['25', floatval(25)],
            ['-25', -floatval(25)],
            ['-25.234', -25.234],
            ['25.0', 25.0],
        ];
    }

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

    public function validBooleanProvider()
    {
        return [
            ['true', true],
            ['false', false],
            ['  true', true],
            ['false ', false],
        ];
    }

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

    public function validPointProvider()
    {
        return [
            ['20 30', new QtiPoint(20, 30)],
            ['240 30', new QtiPoint(240, 30)],
            ['-10 3', new QtiPoint(-10, 3)],
        ];
    }

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

    public function validPairProvider()
    {
        return [
            ['Bidule Trucmuche', new QtiPair('Bidule', 'Trucmuche')],
            ['C D', new QtiPair('C', 'D')],
        ];
    }

    public function invalidPairProvider()
    {
        return [
            ['Machinbrol'],
            ['bidule 0'],
            [''],
            [null],
        ];
    }

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

    public function invalidUriToSanitizeProvider()
    {
        return [
            [new stdClass()],
            [14],
            [true],
        ];
    }
}
