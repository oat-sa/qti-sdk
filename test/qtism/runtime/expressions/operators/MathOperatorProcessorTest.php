<?php
require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\data\expressions\operators\MathFunctions;
use qtism\runtime\expressions\operators\MathOperatorProcessor;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiFloat;
use qtism\runtime\expressions\operators\OperandsCollection;

class MathOperatorProcessorTest extends QtiSmTestCase {
	
	/**
	 * @dataProvider sinProvider
	 * 
	 * @param number $operand operand in radians
	 * @param number $expected
	 */
	public function testSin($operand, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::SIN);
		$operands = new OperandsCollection(array($operand));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
		$this->assertTrue(!$result instanceof QtiInteger);
	}
	
	/**
	 * @dataProvider cosProvider
	 * 
	 * @param number $operand
	 * @param number $expected
	 */
	public function testCos($operand, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::COS);
		$operands = new OperandsCollection(array($operand));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
		$this->assertTrue(!$result instanceof QtiInteger);;
	}
	
	/**
	 * @dataProvider tanProvider
	 *
	 * @param number $operand
	 * @param number $expected
	 */
	public function testTan($operand, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::TAN);
		$operands = new OperandsCollection(array($operand));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
		$this->assertTrue(!$result instanceof QtiInteger);
	}
	
	/**
	 * @dataProvider secProvider
	 *
	 * @param number $operand
	 * @param number $expected
	 */
	public function testSec($operand, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::SEC);
		$operands = new OperandsCollection(array($operand));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
		$this->assertTrue(!$result instanceof QtiInteger);
	}
	
	/**
	 * @dataProvider cscProvider
	 * 
	 * @param number $operand
	 * @param number $expected
	 */
	public function testCsc($operand, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::CSC);
		$operands = new OperandsCollection(array($operand));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
		$this->assertTrue(!$result instanceof QtiInteger);
	}
	
	/**
	 * @dataProvider cotProvider
	 *
	 * @param number $operand
	 * @param number $expected
	 */
	public function testCot($operand, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::COT);
		$operands = new OperandsCollection(array($operand));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
		$this->assertTrue(!$result instanceof QtiInteger);
	}
	
	/**
	 * @dataProvider asinProvider
	 *
	 * @param number $operand
	 * @param number $expected
	 */
	public function testAsin($operand, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::ASIN);
		$operands = new OperandsCollection(array($operand));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
		$this->assertTrue(!$result instanceof QtiInteger);
	}
	
	/**
	 * @dataProvider atan2Provider
	 *
	 * @param number $operand1
	 * @param number $operand2
	 * @param number $expected
	 */
	public function testAtan2($operand1, $operand2, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::ATAN2);
		$operands = new OperandsCollection(array($operand1, $operand2));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
		$this->assertTrue(!$result instanceof QtiInteger);
	}
	
	/**
	 * @dataProvider asecProvider
	 *
	 * @param number $operand
	 * @param number $expected
	 */
	public function testAsec($operand, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::ASEC);
		$operands = new OperandsCollection(array($operand));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
		$this->assertTrue(!$result instanceof QtiInteger);
	}
	
	/**
	 * @dataProvider acscProvider
	 *
	 * @param number $operand
	 * @param number $expected
	 */
	public function testAcsc($operand, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::ACSC);
		$operands = new OperandsCollection(array($operand));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
		$this->assertTrue(!$result instanceof QtiInteger);
	}
	
	/**
	 * @dataProvider acotProvider
	 *
	 * @param number $operand
	 * @param number $expected
	 */
	public function testAcot($operand, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::ACOT);
		$operands = new OperandsCollection(array($operand));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
		$this->assertTrue(!$result instanceof QtiInteger);
	}
	
	/**
	 * @dataProvider logProvider
	 *
	 * @param number $operand
	 * @param number $expected
	 */
	public function testLog($operand, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::LOG);
		$operands = new OperandsCollection(array($operand));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
		$this->assertTrue(!$result instanceof QtiInteger);
	}
	
	/**
	 * @dataProvider lnProvider
	 *
	 * @param number $operand
	 * @param number $expected
	 */
	public function testLn($operand, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::LN);
		$operands = new OperandsCollection(array($operand));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
		$this->assertTrue(!$result instanceof QtiInteger);
	}
	
	/**
	 * @dataProvider sinhProvider
	 *
	 * @param number $operand
	 * @param number $expected
	 */
	public function testSinh($operand, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::SINH);
		$operands = new OperandsCollection(array($operand));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
		$this->assertTrue(!$result instanceof QtiInteger);
	}
	
	/**
	 * @dataProvider coshProvider
	 *
	 * @param number $operand
	 * @param number $expected
	 */
	public function testCosh($operand, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::COSH);
		$operands = new OperandsCollection(array($operand));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
		$this->assertTrue(!$result instanceof QtiInteger);
	}
	
	/**
	 * @dataProvider tanhProvider
	 *
	 * @param number $operand
	 * @param number $expected
	 */
	public function testTanh($operand, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::TANH);
		$operands = new OperandsCollection(array($operand));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
		$this->assertTrue(!$result instanceof QtiInteger);
	}
	
	/**
	 * @dataProvider sechProvider
	 *
	 * @param number $operand
	 * @param number $expected
	 */
	public function testSech($operand, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::SECH);
		$operands = new OperandsCollection(array($operand));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
		$this->assertTrue(!$result instanceof QtiInteger);
	}
	
	/**
	 * @dataProvider cschProvider
	 *
	 * @param number $operand
	 * @param number $expected
	 */
	public function testCsch($operand, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::CSCH);
		$operands = new OperandsCollection(array($operand));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
		$this->assertTrue(!$result instanceof QtiInteger);
	}
	
	/**
	 * @dataProvider cothProvider
	 *
	 * @param number $operand
	 * @param number $expected
	 */
	public function testCoth($operand, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::COTH);
		$operands = new OperandsCollection(array($operand));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
		$this->assertTrue(!$result instanceof QtiInteger);
	}
	
	/**
	 * @dataProvider absProvider
	 *
	 * @param number $operand
	 * @param number $expected
	 */
	public function testAbs($operand, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::ABS);
		$operands = new OperandsCollection(array($operand));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
		$this->assertTrue(!$result instanceof QtiInteger);
	}
	
	/**
	 * @dataProvider expProvider
	 *
	 * @param number $operand
	 * @param number $expected
	 */
	public function testExp($operand, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::EXP);
		$operands = new OperandsCollection(array($operand));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
		$this->assertTrue(!$result instanceof QtiInteger);
	}
	
	/**
	 * @dataProvider signumProvider
	 *
	 * @param number $operand
	 * @param number $expected
	 */
	public function testSignum($operand, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::SIGNUM);
		$operands = new OperandsCollection(array($operand));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
		$this->assertTrue(!$result instanceof QtiFloat);
	}
	
	/**
	 * @dataProvider floorProvider
	 *
	 * @param number $operand
	 * @param number $expected
	 */
	public function testFloor($operand, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::FLOOR);
		$operands = new OperandsCollection(array($operand));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
	}
	
	/**
	 * @dataProvider ceilProvider
	 *
	 * @param number $operand
	 * @param number $expected
	 */
	public function testCeil($operand, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::CEIL);
		$operands = new OperandsCollection(array($operand));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
	}
	
	/**
	 * @dataProvider toDegreesProvider
	 *
	 * @param number $operand
	 * @param number $expected
	 */
	public function testToDegrees($operand, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::TO_DEGREES);
		$operands = new OperandsCollection(array($operand));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
		$this->assertTrue(!$result instanceof QtiInteger);
	}
	
	/**
	 * @dataProvider toRadiansProvider
	 *
	 * @param number $operand
	 * @param number $expected
	 */
	public function testToRadians($operand, $expected) {
		$expression = $this->createFakeExpression(MathFunctions::TO_RADIANS);
		$operands = new OperandsCollection(array($operand));
		$processor = new MathOperatorProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertEqualsRounded($expected, $result);
		$this->assertTrue(!$result instanceof QtiInteger);
	}
	
	protected function assertEqualsRounded($expected, $value) {
		if (is_null($expected)) {
			$this->assertSame(null, $value);
		}
		else if (is_infinite($expected)) {
			if ($expected > 0) {
				$this->assertTrue(is_infinite($value->getValue()) && $value->getValue() > 0);
			}
			else {
				$this->assertTrue(is_infinite($value->getValue()) && $value->getValue() < 0);
			}
		}
		else {
			$this->assertEquals(round($expected, 3), round($value->getValue(), 3));
		}
	}
	
	public function sinProvider() {
		return array(
			array(new QtiFloat(1.5708), 1),
			array(new QtiFloat(INF), null), // falls outside the domain.
		);
	}
	
	public function cosProvider() {
		return array(
			array(new QtiInteger(25), 0.99120281),
			array(new QtiFloat(INF), null), // falls outside the domain.
		);
	}
	
	public function tanProvider() {
		return array(
			array(new QtiFloat(2.65), -0.53543566),
			array(new QtiFloat(INF), null)
		);
	}
	
	public function secProvider() {
		return array(
			array(new QtiFloat(deg2rad(85)), 11.4737)
		);
	}
	
	public function cscProvider() {
		return array(
			array(new QtiFloat(deg2rad(31.67)), 1.904667)
		);
	}
	
	public function cotProvider() {
		return array(
			array(new QtiFloat(2.09), -0.571505)
		);
	}
	
	public function asinProvider() {
		return array(
			array(new QtiInteger(2), null),
			array(new QtiInteger(1), 1.570796),
			array(new QtiFloat(1.1), null)
		);
	}
	
	public function atan2Provider() {
		return array(
			array(new QtiFloat(NAN), new QtiInteger(10), null),
			array(new QtiInteger(+0), new QtiInteger(25), 0),
			array(new QtiInteger(25), new QtiFloat(+INF), 0),
			array(new QtiInteger(-0), new QtiInteger(25), 0),
			array(new QtiInteger(-25), new QtiFloat(+INF), 0),
			array(new QtiInteger(+0), new QtiInteger(-25), M_PI),
			array(new QtiInteger(25), new QtiFloat(-INF), M_PI),
			//array(-0, -19, -M_PI), Cannot be tested, because no valid way to express negative zero in PHP.
			array(new QtiInteger(-25), new QtiFloat(-INF), -M_PI),
			array(new QtiInteger(25), new QtiInteger(-0), M_PI_2),
			array(new QtiFloat(INF), new QtiInteger(25), M_PI_2),
			array(new QtiInteger(-10), new QtiInteger(+0), -M_PI_2),
			array(new QtiFloat(-INF), new QtiInteger(14), -M_PI_2),
			array(new QtiFloat(INF), new QtiFloat(INF), M_PI_4),
			array(new QtiFloat(INF), new QtiFloat(-INF), 3 * M_PI_4),
			array(new QtiFloat(-INF), new QtiFloat(INF), -M_PI_4),
			array(new QtiFloat(-INF), new QtiFloat(-INF), -3 * M_PI_4)
		);
	}
	
	public function asecProvider() {
		return array(
			array(new QtiInteger(-5), 1.7721),
			array(new QtiInteger(0), null),
			array(new QtiFloat(0.45), null),
			array(new QtiFloat(-0.45), null)
		);
	}
	
	public function acscProvider() {
		return array(
			array(new QtiInteger(-5), -0.20135),
			array(new QtiInteger(0), null),
			array(new QtiFloat(-0.45), null)
		);
	}
	
	public function acotProvider() {
		return array(
			array(new QtiInteger(-5), -0.197396),
			array(new QtiInteger(-0), M_PI_2)
		);
	}
	
	public function sinhProvider() {
		return array(
			array(new QtiInteger(5), 74.203210578),
			array(new QtiInteger(-5), -74.203210578),
			array(new QtiInteger(0), 0),
			array(new QtiFloat(INF), INF),
			array(new QtiFloat(-INF), -INF)
		);
	}
	
	public function coshProvider() {
		return array(
			array(new QtiInteger(0), 1),
			array(new QtiInteger(1), 1.543080),
			array(new QtiFloat(NAN), null),
			array(null, null),
			array(new QtiFloat(INF), INF),
			array(new QtiFloat(-INF), INF)
		);
	}
	
	public function tanhProvider() {
		return array(
			array(new QtiInteger(0), 0),
			array(new QtiInteger(1), 0.761594155956),
			array(new QtiFloat(-1.5), -0.905148253645),
			array(new QtiFloat(INF), 1),
			array(new QtiFloat(-INF), -1)		
		);
	}
	
	public function sechProvider() {
		return array(
			array(new QtiFloat(NAN), null),
			array(new QtiFloat(INF), 0),
			array(new QtiFloat(-INF), 0),
			array(new QtiInteger(0), null),
			array(new QtiInteger(-0), null),
			array(new QtiInteger(1), 0.64805)		
		);
	}
	
	public function cschProvider() {
		return array(
			array(new QtiFloat(NAN), null),
			array(new QtiFloat(INF), 0),
			array(new QtiFloat(-INF), 0),
			array(new QtiInteger(0), null),
			array(new QtiInteger(-0), null),
			array(new QtiInteger(1), 0.850918)
		);
	}
	
	public function cothProvider() {
		return array(
			array(new QtiFloat(NAN), null),
			array(new QtiFloat(INF), 0),
			array(new QtiFloat(-INF), 0),
			array(new QtiInteger(0), null),
			array(new QtiInteger(-0), null),
			array(new QtiInteger(1), 1.31304),
			array(new QtiFloat(-2.1), -1.03045)
		);
	}
	
	public function logProvider() {
		return array(
			array(new QtiFloat(-0.5), null),
			array(new QtiFloat(INF), INF),
			array(new QtiInteger(0), -INF),
			array(new QtiInteger(112), 2.049218)
		);
	}
	
	public function lnProvider() {
		return array(
			array(new QtiFloat(-0.5), null),
			array(new QtiFloat(INF), INF),
			array(new QtiInteger(0), -INF),
			array(new QtiInteger(10), 2.30258)
		);
	}
	
	public function expProvider() {
		return array(
			array(new QtiFloat(NAN), null),
			array(null, null),
			array(new QtiFloat(INF), INF)	,
			array(new QtiFloat(-INF), 0),
			array(new QtiInteger(3), 20.08554),
			array(new QtiInteger(-3), 0.04979)
		);
	}
	
	public function absProvider() {
		return array(
			array(new QtiInteger(0), 0),
			array(new QtiInteger(-0), 0),
			array(new QtiFloat(INF), INF),
			array(new QtiFloat(-INF), INF),
			array(new QtiFloat(NAN), null),
			array(new QtiFloat(25.3), 25.3),
			array(new QtiInteger(24), 24),
			array(new QtiFloat(-25.3), 25.3),
			array(new QtiInteger(-24), 24),
			array(null, null)
		);
	}
	
	public function signumProvider() {
		return array(
			array(new QtiInteger(0), 0)	,
			array(new QtiInteger(-0), 0),
			array(new QtiFloat(0.1), 1),
			array(new QtiInteger(25), 1),
			array(new QtiFloat(-0.1), -1),
			array(new QtiInteger(-25), -1),
			array(null, null),
			array(new QtiFloat(NAN), null)
		);
	}
	
	public function floorProvider() {
		return array(
			array(new QtiInteger(10), 10),
			array(new QtiInteger(-10), -10),
			array(new QtiFloat(4.3), 4),
			array(new QtiFloat(9.999), 9),
			array(new QtiFloat(-3.14), -4),
			array(null, null),
			array(new QtiFloat(NAN), null),
			array(new QtiFloat(INF), INF),
			array(new QtiFloat(-INF), -INF)	
		);
	}
	
	public function ceilProvider() {
		return array(
			array(new QtiInteger(10), 10),
			array(new QtiInteger(-10), -10),
			array(new QtiFloat(4.3), 5),
			array(new QtiFloat(9.999), 10),
			array(new QtiFloat(-3.14), -3),
			array(null, null),
			array(new QtiFloat(NAN), null),
			array(new QtiFloat(INF), INF),
			array(new QtiFloat(-INF), -INF)	
		);
	}
	
	public function toDegreesProvider() {
		return array(
			array(new QtiFloat(NAN), null),
			array(new QtiFloat(INF), INF),
			array(new QtiFloat(-INF), -INF),
			array(null, null),
			array(new QtiFloat(2.1), 120.321),
			array(new QtiFloat(-2.1), -120.321),
			array(new QtiInteger(0), 0.0)
		);
	}
	
	public function toRadiansProvider() {
		return array(
			array(new QtiFloat(NAN), null),
			array(new QtiFloat(INF), INF)	,
			array(new QtiFloat(-INF), -INF),
			array(null, null),
			array(new QtiInteger(0), 0.0),
			array(new QtiInteger(90), 1.571),
			array(new QtiInteger(180), 3.142),
			array(new QtiInteger(270), 4.712),
			array(new QtiInteger(360), 6.283)
		);
	}
	
	public function createFakeExpression($constant) {
		return $this->createComponentFromXml('
			<mathOperator name="' . MathFunctions::getNameByConstant($constant) . '">
				<baseValue baseType="float">1.5708</baseValue>
			</mathOperator>
		');
	}
}
