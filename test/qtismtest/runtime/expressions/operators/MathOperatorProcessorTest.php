<?php

declare(strict_types=1);

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\enums\BaseType;
use qtism\data\expressions\operators\MathFunctions;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\expressions\operators\MathOperatorProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\operators\OperatorProcessingException;

/**
 * Class MathOperatorProcessorTest
 */
class MathOperatorProcessorTest extends QtiSmTestCase
{
    /**
     * @dataProvider sinProvider
     *
     * @param number $operand operand in radians
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testSin($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::SIN);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiInteger);
    }

    /**
     * @dataProvider cosProvider
     *
     * @param number $operand
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testCos($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::COS);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiInteger);
    }

    /**
     * @dataProvider tanProvider
     *
     * @param number $operand
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testTan($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::TAN);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiInteger);
    }

    /**
     * @dataProvider secProvider
     *
     * @param number $operand
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testSec($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::SEC);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiInteger);
    }

    /**
     * @dataProvider cscProvider
     *
     * @param number $operand
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testCsc($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::CSC);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiInteger);
    }

    /**
     * @dataProvider cotProvider
     *
     * @param number $operand
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testCot($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::COT);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiInteger);
    }

    /**
     * @dataProvider asinProvider
     *
     * @param number $operand
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testAsin($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::ASIN);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiInteger);
    }

    /**
     * @dataProvider atan2Provider
     *
     * @param number $operand1
     * @param number $operand2
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testAtan2($operand1, $operand2, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::ATAN2);
        $operands = new OperandsCollection([$operand1, $operand2]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiInteger);
    }

    /**
     * @dataProvider asecProvider
     *
     * @param number $operand
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testAsec($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::ASEC);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiInteger);
    }

    /**
     * @dataProvider acscProvider
     *
     * @param number $operand
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testAcsc($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::ACSC);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiInteger);
    }

    /**
     * @dataProvider acotProvider
     *
     * @param number $operand
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testAcot($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::ACOT);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiInteger);
    }

    /**
     * @dataProvider logProvider
     *
     * @param number $operand
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testLog($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::LOG);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiInteger);
    }

    /**
     * @dataProvider lnProvider
     *
     * @param number $operand
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testLn($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::LN);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiInteger);
    }

    /**
     * @dataProvider sinhProvider
     *
     * @param number $operand
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testSinh($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::SINH);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiInteger);
    }

    /**
     * @dataProvider coshProvider
     *
     * @param number $operand
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testCosh($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::COSH);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiInteger);
    }

    /**
     * @dataProvider tanhProvider
     *
     * @param number $operand
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testTanh($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::TANH);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiInteger);
    }

    /**
     * @dataProvider sechProvider
     *
     * @param number $operand
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testSech($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::SECH);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiInteger);
    }

    /**
     * @dataProvider cschProvider
     *
     * @param number $operand
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testCsch($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::CSCH);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiInteger);
    }

    /**
     * @dataProvider cothProvider
     *
     * @param number $operand
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testCoth($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::COTH);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiInteger);
    }

    /**
     * @dataProvider absProvider
     *
     * @param number $operand
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testAbs($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::ABS);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiInteger);
    }

    /**
     * @dataProvider expProvider
     *
     * @param number $operand
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testExp($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::EXP);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiInteger);
    }

    /**
     * @dataProvider signumProvider
     *
     * @param number $operand
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testSignum($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::SIGNUM);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiFloat);
    }

    /**
     * @dataProvider floorProvider
     *
     * @param number $operand
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testFloor($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::FLOOR);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
    }

    /**
     * @dataProvider ceilProvider
     *
     * @param number $operand
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testCeil($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::CEIL);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
    }

    /**
     * @dataProvider toDegreesProvider
     *
     * @param number $operand
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testToDegrees($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::TO_DEGREES);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiInteger);
    }

    /**
     * @dataProvider toRadiansProvider
     *
     * @param number $operand
     * @param number $expected
     * @throws MarshallerNotFoundException
     */
    public function testToRadians($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::TO_RADIANS);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiInteger);
    }

    /**
     * @dataProvider acosProvider
     *
     * @param $operand
     * @param $expected
     * @throws MarshallerNotFoundException
     */
    public function testAcos($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::ACOS);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiInteger);
    }

    /**
     * @dataProvider atanProvider
     *
     * @param $operand
     * @param $expected
     * @throws MarshallerNotFoundException
     */
    public function testAtan($operand, $expected): void
    {
        $expression = $this->createFakeExpression(MathFunctions::ATAN);
        $operands = new OperandsCollection([$operand]);
        $processor = new MathOperatorProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertEqualsRounded($expected, $result);
        $this::assertTrue(!$result instanceof QtiInteger);
    }

    /**
     * @param $expected
     * @param $value
     */
    protected function assertEqualsRounded($expected, $value): void
    {
        if ($expected === null) {
            $this::assertNull($value);
        } elseif (is_infinite($expected)) {
            if ($expected > 0) {
                $this::assertTrue(is_infinite($value->getValue()) && $value->getValue() > 0);
            } else {
                $this::assertTrue(is_infinite($value->getValue()) && $value->getValue() < 0);
            }
        } else {
            $this::assertEquals(round($expected, 3), round($value->getValue(), 3));
        }
    }

    public function testNonSingleCardinalityOperand(): void
    {
        $expression = $this->createFakeExpression(MathFunctions::CEIL);
        $operands = new OperandsCollection(
            [
                new MultipleContainer(BaseType::FLOAT, [new QtiFloat(1.2)]),
            ]
        );
        $processor = new MathOperatorProcessor($expression, $operands);

        $this->expectException(OperatorProcessingException::class);
        $this->expectExceptionMessage('The MathOperator operator only accepts operands with a single cardinality.');

        $result = $processor->process();
    }

    public function testNonNumericOperand(): void
    {
        $expression = $this->createFakeExpression(MathFunctions::CEIL);
        $operands = new OperandsCollection(
            [
                new QtiBoolean(true),
            ]
        );
        $processor = new MathOperatorProcessor($expression, $operands);

        $this->expectException(OperatorProcessingException::class);
        $this->expectExceptionMessage('The MathOperator operator only accepts operands with an integer or float baseType.');

        $processor->process();
    }

    public function testAtan2SingleOperator(): void
    {
        $expression = $this->createFakeExpression(MathFunctions::ATAN2);
        $operands = new OperandsCollection(
            [
                new QtiFloat(0.0),
            ]
        );
        $processor = new MathOperatorProcessor($expression, $operands);

        $this->expectException(OperatorProcessingException::class);
        $this->expectExceptionMessage('The atan2 math function of the MathOperator requires 2 operands, 1 operand given.');

        $processor->process();
    }

    public function testAtan2ThreeOperators(): void
    {
        $expression = $this->createFakeExpression(MathFunctions::ATAN2);
        $operands = new OperandsCollection(
            [
                new QtiFloat(0.0),
                new QtiFloat(0.0),
                new QtiFloat(0.0),
            ]
        );
        $processor = new MathOperatorProcessor($expression, $operands);

        $this->expectException(OperatorProcessingException::class);
        $this->expectExceptionMessage('The atan2 math function of the MathOperator requires 2 operands, more than 2 operands given.');

        $processor->process();
    }

    /**
     * @return array
     */
    public function sinProvider(): array
    {
        return [
            [new QtiFloat(1.5708), 1],
            [new QtiFloat(INF), null], // falls outside the domain.
        ];
    }

    /**
     * @return array
     */
    public function cosProvider(): array
    {
        return [
            [new QtiInteger(25), 0.99120281],
            [new QtiFloat(INF), null], // falls outside the domain.
        ];
    }

    /**
     * @return array
     */
    public function tanProvider(): array
    {
        return [
            [new QtiFloat(2.65), -0.53543566],
            [new QtiFloat(INF), null],
        ];
    }

    /**
     * @return array
     */
    public function secProvider(): array
    {
        return [
            [new QtiFloat(deg2rad(85)), 11.4737],
        ];
    }

    /**
     * @return array
     */
    public function cscProvider(): array
    {
        return [
            [new QtiFloat(deg2rad(31.67)), 1.904667],
        ];
    }

    /**
     * @return array
     */
    public function cotProvider(): array
    {
        return [
            [new QtiFloat(2.09), -0.571505],
        ];
    }

    /**
     * @return array
     */
    public function asinProvider(): array
    {
        return [
            [new QtiInteger(2), null],
            [new QtiInteger(1), 1.570796],
            [new QtiFloat(1.1), null],
        ];
    }

    /**
     * @return array
     */
    public function acosProvider(): array
    {
        return [
            [new QtiFloat(0.3), 1.266103673],
        ];
    }

    /**
     * @return array
     */
    public function atanProvider(): array
    {
        return [
            [new QtiInteger(2), 1.107148718],
        ];
    }

    /**
     * @return array
     */
    public function atan2Provider(): array
    {
        $data = [
            [new QtiFloat(NAN), new QtiInteger(10), null],
            [new QtiInteger(+0), new QtiInteger(25), 0],
            [new QtiInteger(25), new QtiFloat(+INF), 0],
            [new QtiInteger(-0), new QtiInteger(25), 0],
            [new QtiInteger(-25), new QtiFloat(+INF), 0],
            [new QtiInteger(+0), new QtiInteger(-25), M_PI],
            [new QtiInteger(25), new QtiFloat(-INF), M_PI],
            //array(-0, -19, -M_PI), Cannot be tested, because no valid way to express negative zero in PHP.
            [new QtiInteger(-25), new QtiFloat(-INF), -M_PI],
            [new QtiInteger(25), new QtiInteger(-0), M_PI_2],
            [new QtiFloat(INF), new QtiInteger(25), M_PI_2],
            [new QtiInteger(-10), new QtiInteger(+0), -M_PI_2],
            [new QtiFloat(-INF), new QtiInteger(14), -M_PI_2],
        ];

        //Sometimes atan2 with both INF arguments returns NAN. I have no idea why it happens (PHP 5.6.13, win10).
        if (!is_nan(atan2(INF, INF))) {
            $data[] = [new QtiFloat(INF), new QtiFloat(INF), M_PI_4];
            $data[] = [new QtiFloat(INF), new QtiFloat(-INF), 3 * M_PI_4];
            $data[] = [new QtiFloat(-INF), new QtiFloat(INF), -M_PI_4];
            $data[] = [new QtiFloat(-INF), new QtiFloat(-INF), -3 * M_PI_4];
        }

        return $data;
    }

    /**
     * @return array
     */
    public function asecProvider(): array
    {
        return [
            [new QtiInteger(-5), 1.7721],
            [new QtiInteger(0), null],
            [new QtiFloat(0.45), null],
            [new QtiFloat(-0.45), null],
        ];
    }

    /**
     * @return array
     */
    public function acscProvider(): array
    {
        return [
            [new QtiInteger(-5), -0.20135],
            [new QtiInteger(0), null],
            [new QtiFloat(-0.45), null],
        ];
    }

    /**
     * @return array
     */
    public function acotProvider(): array
    {
        return [
            [new QtiInteger(-5), -0.197396],
            [new QtiInteger(-0), M_PI_2],
        ];
    }

    /**
     * @return array
     */
    public function sinhProvider(): array
    {
        return [
            [new QtiInteger(5), 74.203210578],
            [new QtiInteger(-5), -74.203210578],
            [new QtiInteger(0), 0],
            [new QtiFloat(INF), INF],
            [new QtiFloat(-INF), -INF],
        ];
    }

    /**
     * @return array
     */
    public function coshProvider(): array
    {
        return [
            [new QtiInteger(0), 1],
            [new QtiInteger(1), 1.543080],
            [new QtiFloat(NAN), null],
            [null, null],
            [new QtiFloat(INF), INF],
            [new QtiFloat(-INF), INF],
        ];
    }

    /**
     * @return array
     */
    public function tanhProvider(): array
    {
        return [
            [new QtiInteger(0), 0],
            [new QtiInteger(1), 0.761594155956],
            [new QtiFloat(-1.5), -0.905148253645],
            [new QtiFloat(INF), 1],
            [new QtiFloat(-INF), -1],
        ];
    }

    /**
     * @return array
     */
    public function sechProvider(): array
    {
        return [
            [new QtiFloat(NAN), null],
            [new QtiFloat(INF), 0],
            [new QtiFloat(-INF), 0],
            [new QtiInteger(0), null],
            [new QtiInteger(-0), null],
            [new QtiInteger(1), 0.64805],
        ];
    }

    /**
     * @return array
     */
    public function cschProvider(): array
    {
        return [
            [new QtiFloat(NAN), null],
            [new QtiFloat(INF), 0],
            [new QtiFloat(-INF), 0],
            [new QtiInteger(0), null],
            [new QtiInteger(-0), null],
            [new QtiInteger(1), 0.850918],
        ];
    }

    /**
     * @return array
     */
    public function cothProvider(): array
    {
        return [
            [new QtiFloat(NAN), null],
            [new QtiFloat(INF), 0],
            [new QtiFloat(-INF), 0],
            [new QtiInteger(0), null],
            [new QtiInteger(-0), null],
            [new QtiInteger(1), 1.31304],
            [new QtiFloat(-2.1), -1.03045],
        ];
    }

    /**
     * @return array
     */
    public function logProvider(): array
    {
        return [
            [new QtiFloat(-0.5), null],
            [new QtiFloat(INF), INF],
            [new QtiInteger(0), -INF],
            [new QtiInteger(112), 2.049218],
        ];
    }

    /**
     * @return array
     */
    public function lnProvider(): array
    {
        return [
            [new QtiFloat(-0.5), null],
            [new QtiFloat(INF), INF],
            [new QtiInteger(0), -INF],
            [new QtiInteger(10), 2.30258],
        ];
    }

    /**
     * @return array
     */
    public function expProvider(): array
    {
        return [
            [new QtiFloat(NAN), null],
            [null, null],
            [new QtiFloat(INF), INF],
            [new QtiFloat(-INF), 0],
            [new QtiInteger(3), 20.08554],
            [new QtiInteger(-3), 0.04979],
        ];
    }

    /**
     * @return array
     */
    public function absProvider(): array
    {
        return [
            [new QtiInteger(0), 0],
            [new QtiInteger(-0), 0],
            [new QtiFloat(INF), INF],
            [new QtiFloat(-INF), INF],
            [new QtiFloat(NAN), null],
            [new QtiFloat(25.3), 25.3],
            [new QtiInteger(24), 24],
            [new QtiFloat(-25.3), 25.3],
            [new QtiInteger(-24), 24],
            [null, null],
        ];
    }

    /**
     * @return array
     */
    public function signumProvider(): array
    {
        return [
            [new QtiInteger(0), 0],
            [new QtiInteger(-0), 0],
            [new QtiFloat(0.1), 1],
            [new QtiInteger(25), 1],
            [new QtiFloat(-0.1), -1],
            [new QtiInteger(-25), -1],
            [null, null],
            [new QtiFloat(NAN), null],
        ];
    }

    /**
     * @return array
     */
    public function floorProvider(): array
    {
        return [
            [new QtiInteger(10), 10],
            [new QtiInteger(-10), -10],
            [new QtiFloat(4.3), 4],
            [new QtiFloat(9.999), 9],
            [new QtiFloat(-3.14), -4],
            [null, null],
            [new QtiFloat(NAN), null],
            [new QtiFloat(INF), INF],
            [new QtiFloat(-INF), -INF],
        ];
    }

    /**
     * @return array
     */
    public function ceilProvider(): array
    {
        return [
            [new QtiInteger(10), 10],
            [new QtiInteger(-10), -10],
            [new QtiFloat(4.3), 5],
            [new QtiFloat(9.999), 10],
            [new QtiFloat(-3.14), -3],
            [null, null],
            [new QtiFloat(NAN), null],
            [new QtiFloat(INF), INF],
            [new QtiFloat(-INF), -INF],
        ];
    }

    /**
     * @return array
     */
    public function toDegreesProvider(): array
    {
        return [
            [new QtiFloat(NAN), null],
            [new QtiFloat(INF), INF],
            [new QtiFloat(-INF), -INF],
            [null, null],
            [new QtiFloat(2.1), 120.321],
            [new QtiFloat(-2.1), -120.321],
            [new QtiInteger(0), 0.0],
        ];
    }

    /**
     * @return array
     */
    public function toRadiansProvider(): array
    {
        return [
            [new QtiFloat(NAN), null],
            [new QtiFloat(INF), INF],
            [new QtiFloat(-INF), -INF],
            [null, null],
            [new QtiInteger(0), 0.0],
            [new QtiInteger(90), 1.571],
            [new QtiInteger(180), 3.142],
            [new QtiInteger(270), 4.712],
            [new QtiInteger(360), 6.283],
        ];
    }

    /**
     * @param $constant
     * @return QtiComponent
     * @throws MarshallerNotFoundException
     */
    public function createFakeExpression($constant): QtiComponent
    {
        return $this->createComponentFromXml('
			<mathOperator name="' . MathFunctions::getNameByConstant($constant) . '">
				<baseValue baseType="float">1.5708</baseValue>
			</mathOperator>
		');
    }
}
