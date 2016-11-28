<?php
require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiPoint;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\expressions\operators\MemberProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;

class MemberProcessorTest extends QtiSmTestCase {
	
	public function testMultiple() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiFloat(10.1);
		$mult = new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(1.1), new QtiFloat(2.1), new QtiFloat(3.1)));
		$operands[] = $mult;
		$processor = new MemberProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertEquals(false, $result->getValue());
		
		$mult[] = new QtiFloat(10.1);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertEquals(true, $result->getValue());
	}
	
	public function testOrdered() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiPair('A', 'B');
		$ordered = new OrderedContainer(BaseType::PAIR, array(new QtiPair('B', 'C')));
		$operands[] = $ordered;
		$processor = new MemberProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertEquals(false, $result->getValue());
		
		$ordered[] = new QtiPair('A', 'B');
		$result = $processor->process();
		$this->assertInstanceOf(QtiBoolean::class, $result);
		$this->assertEquals(true, $result->getValue());
	}
	
	public function testNull() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		
		// second operand is null.
		$operands[] = new QtiInteger(10);
		$operands[] = new OrderedContainer(BaseType::INTEGER);
		$processor = new MemberProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
		
		// fist operand is null.
		$operands->reset();
		$operands[] = null;
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(10)));
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testDifferentBaseTypeOne() {
	    $expression = $this->createFakeExpression();
	    $operands = new OperandsCollection();
	    $operands[] = new QtiString('String1');
	    $operands[] = new OrderedContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('String2'), new QtiIdentifier('String1'), null));
	    $processor = new MemberProcessor($expression, $operands);
	    
	    $this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
	    $processor->process();
	}
	
	public function testDifferentBaseTypeTwo() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiPair('A', 'B');
		$operands[] = new MultipleContainer(BaseType::POINT, array(new QtiPoint(1, 2)));
		$processor = new MemberProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinalityOne() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::POINT, array(new QtiPoint(13, 37)));
		$operands[] = new MultipleContainer(BaseType::POINT, array(new QtiPoint(1, 2)));
		$processor = new MemberProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinalityTwo() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiPoint(13, 37);
		$operands[] = new RecordContainer(array('key' => new QtiString('value')));
		$processor = new MemberProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiPoint(13, 37);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new MemberProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiPoint(13, 37);
		$operands[] = new MultipleContainer(BaseType::POINT, array(new QtiPoint(1, 2)));
		$operands[] = new MultipleContainer(BaseType::POINT, array(new QtiPoint(3, 4)));
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new MemberProcessor($expression, $operands);
	}
    
    public function testSingleCardinalitySecondOperand() {
        $expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiPoint(13, 37);
		$operands[] = new QtiPoint(13, 37);
		$processor = new MemberProcessor($expression, $operands);
        $result = $processor->process();
        
        $this->assertInstanceOf('qtism\\common\\datatypes\\QtiBoolean', $result);
        $this->assertTrue($result->getValue());
    }
	
	public function createFakeExpression() {
		return $this->createComponentFromXml('
			<member>
				<baseValue baseType="boolean">true</baseValue>
				<ordered>
					<baseValue baseType="boolean">false</baseValue>
					<baseValue baseType="boolean">true</baseValue>
				</ordered>
			</member>
		');
	}
}
