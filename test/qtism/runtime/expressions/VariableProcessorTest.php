<?php
require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

use qtism\runtime\tests\AssessmentItemSession;
use qtism\data\TestPartCollection;
use qtism\data\AssessmentSection;
use qtism\data\AssessmentSectionCollection;
use qtism\data\TestPart;
use qtism\data\AssessmentTest;
use qtism\data\state\OutcomeDeclaration;
use qtism\data\ExtendedAssessmentItemRef;
use qtism\data\AssessmentItemRefCollection;
use qtism\data\AssessmentItemRef;
use qtism\data\state\WeightCollection;
use qtism\runtime\expressions\VariableProcessor;
use qtism\common\enums\Cardinality;
use qtism\common\enums\BaseType;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\State;
use qtism\data\state\Weight;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\tests\AssessmentTestSession;

class VariableProcessorTest extends QtiSmTestCase {

	public function testSimple() {
		$variableExpr = $this->createComponentFromXml('<variable identifier="var1"/>');
		
		// single cardinality test.
		$var1 = new OutcomeVariable('var1', Cardinality::SINGLE, BaseType::INTEGER, 1337);
		$state = new State(array($var1));
		$this->assertInstanceOf('qtism\\runtime\\common\\OutcomeVariable', $state->getVariable('var1'));
		
		$variableProcessor = new VariableProcessor($variableExpr);
		$this->assertTrue($variableProcessor->process() === null); // State is raw.
		
		$variableProcessor->setState($state); // State is populated with var1.
		$result = $variableProcessor->process();
		$this->assertInternalType('integer', $result);
		$this->assertEquals(1337, $result);
		
		// multiple cardinality test.
		$val = new OrderedContainer(BaseType::INTEGER, array(10, 12));
		$var2 = new OutcomeVariable('var2', Cardinality::ORDERED, BaseType::INTEGER, $val);
		$state->setVariable($var2);
		$variableExpr = $this->createComponentFromXml('<variable identifier="var2"/>');
		$variableProcessor->setExpression($variableExpr);
		$result = $variableProcessor->process();
		$this->assertInstanceOf('qtism\\runtime\\common\\OrderedContainer', $result);
		$this->assertEquals(10, $result[0]);
		$this->assertEquals(12, $result[1]);
	}
	
	public function testWeighted() {
		$assessmentItemRef = new ExtendedAssessmentItemRef('Q01', './Q01.xml');
		$weights = new WeightCollection(array(new Weight('weight1', 1.1)));
		$assessmentItemRef->setWeights($weights);
		$assessmentItemRef->addOutcomeDeclaration(new OutcomeDeclaration('var1', BaseType::INTEGER, Cardinality::SINGLE));
		$assessmentItemRef->addOutcomeDeclaration(new OutcomeDeclaration('var2', BaseType::FLOAT, Cardinality::MULTIPLE));
		
		$assessmentItemRefs = new AssessmentItemRefCollection(array($assessmentItemRef));
		$assessmentTest = new AssessmentTest('A01', 'An assessmentTest');
		$assessmentSection = new AssessmentSection('S01', 'An assessmentSection', true);
		$assessmentSection->setSectionParts($assessmentItemRefs);
		$assessmentSections = new AssessmentSectionCollection(array($assessmentSection));
		$testPart = new TestPart('P01', $assessmentSections);
		$assessmentTest->setTestParts(new TestPartCollection(array($testPart)));
		
		$assessmentTestSession = AssessmentTestSession::instantiate($assessmentTest);
		$itemSession = new AssessmentItemSession($assessmentItemRef);
		$itemSession->beginItemSession();
		$assessmentTestSession->addItemSession($itemSession);
		
		$assessmentTestSession['Q01.var1'] = 1337;
		$variableExpr = $this->createComponentFromXml('<variable identifier="Q01.var1" weightIdentifier="weight1" />');
		
		$variableProcessor = new VariableProcessor($variableExpr);
		$variableProcessor->setState($assessmentTestSession);
		
		// -- single cardinality test.
		$result = $variableProcessor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(1470.7, $result);
		// The value in the state must be intact.
		$this->assertEquals(1337, $assessmentTestSession['Q01.var1']);
		
		// What if the indicated weight is not found?
		$weights['weight1']->setIdentifier('weight2');
		$result = $variableProcessor->process();
		$this->assertEquals(1337, $result);
		
		// Remember! the identifier has changed... Magic isn't it ? x)
		$weights['weight2']->setIdentifier('weight1');
		
		// -- multiple cardinality test.
		$assessmentTestSession['Q01.var2'] = new MultipleContainer(BaseType::FLOAT, array(10.1, 12.1));
		$variableExpr = $this->createComponentFromXml('<variable identifier="Q01.var2" weightIdentifier="weight1"/>');
		$variableProcessor->setExpression($variableExpr);
		$result = $variableProcessor->process();
		$this->assertEquals(11.11, $result[0]);
		$this->assertEquals(13.31, $result[1]);
		// The value in the state must be unchanged.
		$stateVal = $assessmentTestSession['Q01.var2'];
		$this->assertEquals(10.1, $stateVal[0]);
		$this->assertEquals(12.1, $stateVal[1]);
		
		// What if the indicated weight is not found?
		unset($weights['weight1']);
		$result = $variableProcessor->process();
		$this->assertEquals(10.1, $result[0]);
		$this->assertEquals(12.1, $result[1]);
	}
}