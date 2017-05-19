<?php
namespace qtismtest\runtime\expressions\operators;

use qtismtest\QtiSmTestCase;
use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiDuration;
use qtism\common\enums\BaseType;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\expressions\operators\TruncateProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;

class TruncateProcessorTest extends QtiSmTestCase {
	
	public function testRound() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiFloat(6.8);
		$processor = new TruncateProcessor($expression, $operands);
		
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\common\\datatypes\\QtiInteger', $result);
		$this->assertEquals(6, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiFloat(6.5);
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\common\\datatypes\\QtiInteger', $result);
		$this->assertEquals(6, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiFloat(6.49);
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\common\\datatypes\\QtiInteger', $result);
		$this->assertEquals(6, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiFloat(-6.5);
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\common\\datatypes\\QtiInteger', $result);
		$this->assertEquals(-6, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiFloat(-6.8);
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\common\\datatypes\\QtiInteger', $result);
		$this->assertEquals(-6, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiFloat(-6.49);
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\common\\datatypes\\QtiInteger', $result);
		$this->assertEquals(-6, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiInteger(0);
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\common\\datatypes\\QtiInteger', $result);
		$this->assertEquals(0, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiFloat(-0.0);
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\common\\datatypes\\QtiInteger', $result);
		$this->assertEquals(0, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiFloat(-0.5);
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\common\\datatypes\\QtiInteger', $result);
		$this->assertEquals(0, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiFloat(-0.4);
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\common\\datatypes\\QtiInteger', $result);
		$this->assertEquals(0, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiFloat(-0.6);
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\common\\datatypes\\QtiInteger', $result);
		$this->assertEquals(0, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiFloat(NAN);
		$result = $processor->process();
		$this->assertSame(null, $result);
		
		$operands->reset();
		$operands[] = new QtiFloat(-INF);
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\common\\datatypes\\QtiFloat', $result);
		$this->assertEquals(-INF, $result->getValue());
		
		$operands->reset();
		$operands[] = new QtiFloat(INF);
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\common\\datatypes\\QtiFloat', $result);
		$this->assertEquals(INF, $result->getValue());
	}
	
	public function testNull() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = null;
		$processor = new TruncateProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::FLOAT, array(new QtiFloat(1.1), new QtiFloat(2.2)));
		$processor = new TruncateProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongBaseTypeOne() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiBoolean(true);
		$processor = new TruncateProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongBaseTypeTwo() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiDuration('P1D');
		$processor = new TruncateProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new TruncateProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiInteger(10);
		$operands[] = new QtiFloat(1.1);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new TruncateProcessor($expression, $operands);
	}
	
	public function createFakeExpression() {
		return $this->createComponentFromXml('
			<truncate>
				<baseValue baseType="float">6.49</baseValue>
			</truncate>
		');
	}

    public function Provider() {
        return [[97.2, 97],
            [97.5, 97],
            [97.9, 97],
            [98.0, 98]
        ];
    }

    /**
     * @dataProvider Provider
     *
     * @val float
     * @val integer
     */

    public function testForProvider($val, $expected) {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiFloat($val);
        $processor = new TruncateProcessor($expression, $operands);

        $result = $processor->process();
        $this->assertInstanceOf('qtism\\common\\datatypes\\QtiInteger', $result);
        $this->assertEquals($expected, $result->getValue());
    }
}