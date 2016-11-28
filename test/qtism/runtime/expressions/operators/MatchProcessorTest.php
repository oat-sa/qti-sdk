<?php

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\datatypes\files\FileSystemFileManager;
use qtism\common\datatypes\files\FileSystemFile;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiIntOrIdentifier;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiInteger;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtism\runtime\expressions\operators\MatchProcessor;

class MatchProcessorTest extends QtiSmTestCase {
	
	public function testScalar() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiInteger(10), new QtiInteger(10)));
		$processor = new MatchProcessor($expression, $operands);
		$this->assertTrue($processor->process()->getValue() === true);
		
		$operands = new OperandsCollection(array(new QtiInteger(10), new QtiInteger(11)));
		$processor->setOperands($operands);
		$this->assertFalse($processor->process()->getValue() === true);
	}
	
	public function testContainer() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(5), new QtiInteger(4), new QtiInteger(3), new QtiInteger(2), new QtiInteger(1)));
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(1), new QtiInteger(2), new QtiInteger(3), new QtiInteger(4), new QtiInteger(5)));
		$processor = new MatchProcessor($expression, $operands);
		
		$this->assertTrue($processor->process()->getValue() === true);
		
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(5), new QtiInteger(4), new QtiInteger(3), new QtiInteger(2), new QtiInteger(1)));
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(1), new QtiInteger(6), new QtiInteger(7), new QtiInteger(8), new QtiInteger(5)));
		$processor->setOperands($operands);
		$this->assertFalse($processor->process()->getValue() === true);
	}
	
	public function testFile() {
	    $fManager = new FileSystemFileManager();
	    $expression = $this->createFakeExpression();
	    $operands = new OperandsCollection();
	    
	    $file1 = $fManager->createFromData('Some text', 'text/plain');
	    $file2 = $fManager->createFromData('Some text', 'text/plain');
	    
	    $operands[] = $file1;
	    $operands[] = $file2;
	    $processor = new MatchProcessor($expression, $operands);
	    
	    $this->assertTrue($processor->process()->getValue());
	    $fManager->delete($file1);
	    $fManager->delete($file2);
	    
	    $operands->reset();
	    $file1 = $fManager->createFromData('Some text', 'text/plain');
	    $file2 = $fManager->createFromData('Other text', 'text/plain');
	    $operands[] = $file1;
	    $operands[] = $file2;

	    $this->assertFalse($processor->process()->getValue());
	    $fManager->delete($file1);
	    $fManager->delete($file2);
	}
	
	public function testWrongBaseType() {
	    $expression = $this->createFakeExpression();
	    $operands = new OperandsCollection();
	    $operands[] = new MultipleContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('txt1'), new QtiIdentifier('txt2')));
	    $operands[] = new MultipleContainer(BaseType::STRING, array(new QtiString('txt1'), new QtiString('txt2')));
	    $processor = new MatchProcessor($expression, $operands);
	    $this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
	    $processor->process();
	}
	
	public function testWrongBaseTypeCompliance() {
	    $expression = $this->createFakeExpression();
	    $operands = new OperandsCollection();
	    $operands[] = new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(new QtiIntOrIdentifier('txt1'), new QtiIntOrIdentifier('txt2')));
	    $operands[] = new MultipleContainer(BaseType::STRING, array(new QtiString('txt1'), new QtiString('txt2')));
	    $processor = new MatchProcessor($expression, $operands);
	    
	    // Unfortunately, INT_OR_IDENTIFIER cannot be considered as compliant with STRING.
	    $this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
	    $processor->process();
	}
	
	public function testDifferentBaseTypesScalar() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiInteger(15);
		$operands[] = new QtiString('String!');
		$processor = new MatchProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testDifferentBaseTypesContainer() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(10), new QtiInteger(20), new QtiInteger(30), new QtiInteger(40)));
		$operands[] = new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(10.0), new QtiFloat(20.0), new QtiFloat(30.0), new QtiFloat(40.0)));
		$processor = new MatchProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testDifferentBaseTypesMixed() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiString('String!');
		$operands[] = new OrderedContainer(BaseType::FLOAT, array(new QtiFloat(10.0), new QtiFloat(20.0)));
		$processor = new MatchProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testDifferentCardinalitiesOne() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new QtiString('String!');
		$operands[] = new MultipleContainer(BaseType::STRING, array(new QtiString('String!')));
		$processor = new MatchProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testDifferentCardinalitiesTwo() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::STRING, array(new QtiString('String!')));
		$operands[] = new MultipleContainer(BaseType::STRING, array(new QtiString('String!')));
		$processor = new MatchProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testDifferentCardinalitiesThree() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::STRING, array(new QtiString('String!')));
		$operands[] = new RecordContainer(array('entry1' => new QtiString('String!')));
		$processor = new MatchProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testNotEnoughOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiInteger(15)));
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new MatchProcessor($expression, $operands);
	}
	
	public function testTooMuchOperands() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiInteger(25), new QtiInteger(25), new QtiInteger(25)));
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$processor = new MatchProcessor($expression, $operands);
	}
	
	public function testNullScalar() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection(array(new QtiFloat(15.0), null));
		$processor = new MatchProcessor($expression, $operands);
		$this->assertSame(null, $processor->process());
	}
	
	public function testNullContainer() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(10), new QtiInteger(20)));
		$operands[] = new MultipleContainer(BaseType::INTEGER);
		$processor = new MatchProcessor($expression, $operands);
		$this->assertSame(null, $processor->process());
	}
	
	private function createFakeExpression() {
		return $this->createComponentFromXml('
			<match>
				<baseValue baseType="integer">10</baseValue>
				<baseValue baseType="integer">11</baseValue>
			</match>
		');
	}
}
