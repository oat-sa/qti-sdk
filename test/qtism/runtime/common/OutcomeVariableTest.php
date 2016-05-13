<?php
require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiPair;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\MultipleContainer;
use qtism\common\enums\Cardinality;
use qtism\common\enums\BaseType;

class OutcomeVariableTest extends QtiSmTestCase {
	
	public function testInstantiate() {
		$outcome = new OutcomeVariable('var1', Cardinality::SINGLE, BaseType::INTEGER);
		$this->assertTrue(is_null($outcome->getValue()));
		
		$outcome = new OutcomeVariable('var1', Cardinality::MULTIPLE, BaseType::INTEGER);
		$this->assertInstanceOf('qtism\\runtime\\common\\MultipleContainer', $outcome->getValue());
		
		$outcome = new OutcomeVariable('var1', Cardinality::ORDERED, BaseType::INTEGER);
		$this->assertInstanceOf('qtism\\runtime\\common\\OrderedContainer', $outcome->getValue());
		
		$outcome = new OutcomeVariable('var1', Cardinality::RECORD);
		$this->assertInstanceOf('qtism\\runtime\\common\\RecordContainer', $outcome->getValue());
	}
	
	public function testCardinalitySingle() {
		$variable = new OutcomeVariable('outcome1', Cardinality::SINGLE, BaseType::INTEGER);
		$this->assertInstanceOf('qtism\\runtime\\common\\OutcomeVariable', $variable);
		$this->assertEquals('outcome1', $variable->getIdentifier());
		$this->assertEquals(BaseType::INTEGER, $variable->getBaseType());
		$this->assertEquals(Cardinality::SINGLE, $variable->getCardinality());
		$this->assertTrue(null === $variable->getValue());
		$this->assertEquals(0, count($variable->getViews()));
		$this->assertFalse($variable->getNormalMaximum());
		$this->assertFalse($variable->getNormalMinimum());
		$this->assertFalse($variable->getMasteryValue());
		$this->assertTrue(null === $variable->getLookupTable());
		
		$variable->setValue(new QtiInteger(16));
		$variable->setDefaultValue(new QtiInteger(-1));
		$this->assertInstanceOf(QtiInteger::class, $variable->getValue());
		$this->assertEquals(16, $variable->getValue()->getValue());
		$this->assertInstanceOf(QtiInteger::class, $variable->getDefaultValue());
		$this->assertEquals(-1, $variable->getDefaultValue()->getValue());
		
		// If I reinit the variable, I should see the NULL value inside.
		$variable->initialize();
		$this->assertSame(null, $variable->getValue());
		
		// If I apply the default value, 0 should be inside because
		// baseType is integer, cardinality single, and no default value
		// was given.
		$variable->setDefaultValue(null);
		$variable->applyDefaultValue();
		$this->assertInstanceOf(QtiInteger::class, $variable->getValue());
		$this->assertEquals(0, $variable->getValue()->getValue());
	}
	
	public function testCardinalityMultiple() {
		$variable = new OutcomeVariable('outcome1', Cardinality::MULTIPLE, BaseType::INTEGER);
		$this->assertInstanceOf('qtism\\runtime\\common\\OutcomeVariable', $variable);
		$this->assertEquals(Cardinality::MULTIPLE, $variable->getCardinality());
		
		$variable->setValue(new MultipleContainer(BaseType::INTEGER));
		
		// Try to set up a value with an incorrect baseType.
		try {
			$variable->setValue(new MultipleContainer(BaseType::DURATION));
			// This code portion should not be reached.
			$this->assertTrue(false, 'Developer: Exception not thrown but not compliant baseType?!');
		}
		catch (InvalidArgumentException $e) {
			$this->assertTrue(true);
		}
		
		// Try to set up a value with an incorrect cardinality (1).
		try {
			$variable->setValue(new OrderedContainer(BaseType::INTEGER));
			$this->assertTrue(false, 'Developer: Exception not thrown but not compliant cardinality?!');
		}
		catch (InvalidArgumentException $e) {
			$this->assertTrue(true);
		}
		
		// Try to set up a value with an incorrect cardinality (2).
		try {
		    $variable->setValue(new QtiInteger(25));
		    $this->assertTrue(false, 'Developer: Exception not thrown but not compliant cardinality?!');
		}
		catch (InvalidArgumentException $e) {
		    $this->assertTrue(true);
		} 
	}
	
	public function testCreateFromVariableDeclarationMinimal() {
		$factory = $this->getMarshallerFactory();
		$element = $this->createDOMElement('<outcomeDeclaration	xmlns="http://www.imsglobal.org/xsd/imsqti_v2p0" identifier="outcome1" baseType="integer" cardinality="single"/>');
		$outcomeDeclaration = $factory->createMarshaller($element)->unmarshall($element);
		$outcomeVariable = OutcomeVariable::createFromDataModel($outcomeDeclaration);
		
		$this->assertInstanceOf('qtism\\runtime\\common\\OutcomeVariable', $outcomeVariable);
		$this->assertEquals('outcome1', $outcomeVariable->getIdentifier());
		$this->assertEquals(BaseType::INTEGER, $outcomeVariable->getBaseType());
		$this->assertEquals(Cardinality::SINGLE, $outcomeVariable->getCardinality());
	}
	
	public function testCreateFromVariableDeclarationDefaultValueSingleCardinality() {
		$factory = $this->getMarshallerFactory();
		$element = $this->createDOMElement('
			<outcomeDeclaration xmlns="http://www.imsglobal.org/xsd/imsqti_v2p0" identifier="outcome1" baseType="pair" cardinality="single">
				<defaultValue>
					<value>A B</value>
				</defaultValue>
			</outcomeDeclaration>
		');
		$outcomeDeclaration = $factory->createMarshaller($element)->unmarshall($element);
		$outcomeVariable = OutcomeVariable::createFromDataModel($outcomeDeclaration);
		
		$pair = new QtiPair('A', 'B');
		$this->assertTrue($pair->equals($outcomeVariable->getDefaultValue()));
	}
	
	public function testCreateFromVariableDeclarationDefaultValueMultipleCardinality() {
		$factory = $this->getMarshallerFactory();
		$element = $this->createDOMElement('
			<outcomeDeclaration xmlns="http://www.imsglobal.org/xsd/imsqti_v2p0" identifier="outcome1" baseType="pair" cardinality="multiple">
				<defaultValue>
					<value>A B</value>
					<value>B C</value>
				</defaultValue>
			</outcomeDeclaration>
		');
		$outcomeDeclaration = $factory->createMarshaller($element)->unmarshall($element);
		$outcomeVariable = OutcomeVariable::createFromDataModel($outcomeDeclaration);
		
		$defaultValue = $outcomeVariable->getDefaultValue();
		$this->assertInstanceOf('qtism\\runtime\\common\\MultipleContainer', $defaultValue);
		$this->assertEquals(2, count($defaultValue));
		$this->assertEquals(Cardinality::MULTIPLE, $defaultValue->getCardinality());
		$this->assertTrue($defaultValue[0]->equals(new QtiPair('A', 'B')));
		$this->assertTrue($defaultValue[1]->equals(new QtiPair('B', 'C')));
	}
	
	public function testCreateFromVariableDeclarationDefaultValueRecordCardinality() {
	    $factory = $this->getMarshallerFactory();
	    $element = $this->createDOMElement('
			<outcomeDeclaration identifier="outcome1" cardinality="record">
				<defaultValue>
					<value fieldIdentifier="A" baseType="pair">A B</value>
					<value fieldIdentifier="B" baseType="float">1.11</value>
				</defaultValue>
			</outcomeDeclaration>
		');
	    $outcomeDeclaration = $factory->createMarshaller($element)->unmarshall($element);
	    $outcomeVariable = OutcomeVariable::createFromDataModel($outcomeDeclaration);
	    
	    $defaultValue = $outcomeVariable->getDefaultValue();
	    $this->assertInstanceOf('qtism\\runtime\\common\\RecordContainer', $defaultValue);
	    $this->assertEquals(2, count($defaultValue));
	    
	    $this->assertInstanceOf(QtiPair::class, $defaultValue['A']);
	    $this->assertInstanceOf(QtiFloat::class, $defaultValue['B']);
	}
	
	public function testCreateFromVariableDeclarationExtended() {
		$factory = $this->getMarshallerFactory();
		$element = $this->createDOMElement('
			<outcomeDeclaration xmlns="http://www.imsglobal.org/xsd/imsqti_v2p0" 
								identifier="outcome1" 
								baseType="pair" 
								cardinality="ordered"
								views="author candidate"
								normalMinimum="1.0"
								normalMaximum="2.1"
								masteryValue="1.5">
				<defaultValue>
					<value>A B</value>
					<value>B C</value>
				</defaultValue>
				<matchTable>
					<matchTableEntry sourceValue="0" targetValue="E F"/>
					<matchTableEntry sourceValue="1" targetValue="G H"/>
				</matchTable>
			</outcomeDeclaration>
		');
		$outcomeDeclaration = $factory->createMarshaller($element)->unmarshall($element);
		$outcomeVariable = OutcomeVariable::createFromDataModel($outcomeDeclaration);
		
		$this->assertEquals(Cardinality::ORDERED, $outcomeVariable->getCardinality());
		
		$defaultValue = $outcomeVariable->getDefaultValue();
		$this->assertEquals($outcomeVariable->getCardinality(), $defaultValue->getCardinality());
		$this->assertEquals($outcomeVariable->getBaseType(), $defaultValue->getBaseType());
		
		$this->assertEquals(1.0, $outcomeVariable->getNormalMinimum());
		$this->assertEquals(2.1, $outcomeVariable->getNormalMaximum());
		$this->assertEquals(1.5, $outcomeVariable->getMasteryValue());
		
		$matchTable = $outcomeVariable->getLookupTable();
		$this->assertInstanceOf('qtism\\data\\state\\MatchTable', $matchTable);
		$matchTableEntries = $matchTable->getMatchTableEntries();
		$this->assertEquals(2, count($matchTableEntries));
		$this->assertEquals(0, $matchTableEntries[0]->getSourceValue());
		$targetValue = $matchTableEntries[0]->getTargetValue();
		$this->assertTrue($targetValue->equals(new QtiPair('E', 'F')));
	}
	
	public function testIsNull() {
		$outcome = new OutcomeVariable('var1', Cardinality::SINGLE, BaseType::STRING);
		$this->assertTrue($outcome->isNull());
		$outcome->setValue(new QtiString(''));
		$this->assertTrue($outcome->isNull());
		$outcome->setValue(new QtiString('String!'));
		$this->assertFalse($outcome->isNull());
		
		$outcome = new OutcomeVariable('var1', Cardinality::SINGLE, BaseType::INTEGER);
		$this->assertTrue($outcome->isNull());
		$outcome->setValue(new QtiInteger(0));
		$this->assertFalse($outcome->isNull());
		$outcome->setValue(new QtiInteger(-1));
		$this->assertFalse($outcome->isNull());
		$outcome->setValue(new QtiInteger(100));
		$this->assertFalse($outcome->isNull());
		
		$outcome = new OutcomeVariable('var1', Cardinality::SINGLE, BaseType::FLOAT);
		$this->assertTrue($outcome->isNull());
		$outcome->setValue(new QtiFloat(0.25));
		$this->assertFalse($outcome->isNull());
		$outcome->setValue(new QtiFloat(-1.2));
		$this->assertFalse($outcome->isNull());
		$outcome->setValue(new QtiFloat(100.12));
		$this->assertFalse($outcome->isNull());
		
		$outcome = new OutcomeVariable('var1', Cardinality::SINGLE, BaseType::BOOLEAN);
		$this->assertTrue($outcome->isNull());
		$outcome->setValue(new QtiBoolean(true));
		$this->assertFalse($outcome->isNull());
		$outcome->setValue(new QtiBoolean(false));
		$this->assertFalse($outcome->isNull());
		
		$outcome = new OutcomeVariable('var1', Cardinality::MULTIPLE, BaseType::BOOLEAN);
		$this->assertTrue($outcome->isNull());
		$value = $outcome->getValue();
		$value[] = new QtiBoolean(true);
		$this->assertFalse($outcome->isNull());
		
		$outcome = new OutcomeVariable('var1', Cardinality::ORDERED, BaseType::STRING);
		$this->assertTrue($outcome->isNull());
		$value = $outcome->getValue();
		$value[] = new QtiString('string!');
		$this->assertFalse($outcome->isNull());
		
		$outcome = new OutcomeVariable('var1', Cardinality::RECORD);
		$this->assertTrue($outcome->isNull());
		$value = $outcome->getValue();
		$value['point1'] = new QtiPoint(100, 200);
		$this->assertFalse($outcome->isNull());
	}
}
