<?php

namespace qtismtest\runtime\expressions;

use qtism\common\datatypes\files\FileSystemFileManager;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiInteger;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\AssessmentItemRefCollection;
use qtism\data\AssessmentSection;
use qtism\data\AssessmentSectionCollection;
use qtism\data\AssessmentTest;
use qtism\data\ExtendedAssessmentItemRef;
use qtism\data\state\OutcomeDeclaration;
use qtism\data\state\Weight;
use qtism\data\state\WeightCollection;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\data\TestPart;
use qtism\data\TestPartCollection;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\expressions\VariableProcessor;
use qtism\runtime\tests\SessionManager;
use qtismtest\QtiSmTestCase;

/**
 * Class VariableProcessorTest
 *
 * @package qtismtest\runtime\expressions
 */
class VariableProcessorTest extends QtiSmTestCase
{
    public function testSimple()
    {
        $variableExpr = $this->createComponentFromXml('<variable identifier="var1"/>');

        // single cardinality test.
        $var1 = new OutcomeVariable('var1', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(1337));
        $state = new State([$var1]);
        $this->assertInstanceOf(OutcomeVariable::class, $state->getVariable('var1'));

        $variableProcessor = new VariableProcessor($variableExpr);
        $this->assertTrue($variableProcessor->process() === null); // State is raw.

        $variableProcessor->setState($state); // State is populated with var1.
        $result = $variableProcessor->process();
        $this->assertInstanceOf(QtiInteger::class, $result);
        $this->assertEquals(1337, $result->getValue());

        // multiple cardinality test.
        $val = new OrderedContainer(BaseType::INTEGER, [new QtiInteger(10), new QtiInteger(12)]);
        $var2 = new OutcomeVariable('var2', Cardinality::ORDERED, BaseType::INTEGER, $val);
        $state->setVariable($var2);
        $variableExpr = $this->createComponentFromXml('<variable identifier="var2"/>');
        $variableProcessor->setExpression($variableExpr);
        $result = $variableProcessor->process();
        $this->assertInstanceOf(OrderedContainer::class, $result);
        $this->assertEquals(10, $result[0]->getValue());
        $this->assertEquals(12, $result[1]->getValue());
    }

    public function testWeighted()
    {
        $assessmentItemRef = new ExtendedAssessmentItemRef('Q01', './Q01.xml');
        $weights = new WeightCollection([new Weight('weight1', 1.1)]);
        $assessmentItemRef->setWeights($weights);
        $assessmentItemRef->addOutcomeDeclaration(new OutcomeDeclaration('var1', BaseType::INTEGER, Cardinality::SINGLE));
        $assessmentItemRef->addOutcomeDeclaration(new OutcomeDeclaration('var2', BaseType::FLOAT, Cardinality::MULTIPLE));

        $assessmentItemRefs = new AssessmentItemRefCollection([$assessmentItemRef]);
        $assessmentTest = new AssessmentTest('A01', 'An assessmentTest');
        $assessmentSection = new AssessmentSection('S01', 'An assessmentSection', true);
        $assessmentSection->setSectionParts($assessmentItemRefs);
        $assessmentSections = new AssessmentSectionCollection([$assessmentSection]);
        $testPart = new TestPart('P01', $assessmentSections);
        $assessmentTest->setTestParts(new TestPartCollection([$testPart]));

        $sessionManager = new SessionManager(new FileSystemFileManager());
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($assessmentTest);
        $assessmentTestSession->beginTestSession();

        $assessmentTestSession['Q01.var1'] = new QtiInteger(1337);
        $variableExpr = $this->createComponentFromXml('<variable identifier="Q01.var1" weightIdentifier="weight1" />');

        $variableProcessor = new VariableProcessor($variableExpr);
        $variableProcessor->setState($assessmentTestSession);

        // -- single cardinality test.
        $result = $variableProcessor->process();
        $this->assertInstanceOf(QtiFloat::class, $result);
        $this->assertEquals(1470.7, $result->getValue());
        // The value in the state must be intact.
        $this->assertEquals(1337, $assessmentTestSession['Q01.var1']->getValue());

        // What if the indicated weight is not found?
        $variableExpr = $this->createComponentFromXml('<variable identifier="Q01.var1" weightIdentifier="weight2" />');
        $variableProcessor->setExpression($variableExpr);
        $result = $variableProcessor->process();
        $this->assertEquals(1337, $result->getValue());

        // -- multiple cardinality test.
        $assessmentTestSession['Q01.var2'] = new MultipleContainer(BaseType::FLOAT, [new QtiFloat(10.1), new QtiFloat(12.1)]);
        $variableExpr = $this->createComponentFromXml('<variable identifier="Q01.var2" weightIdentifier="weight1"/>');
        $variableProcessor->setExpression($variableExpr);
        $result = $variableProcessor->process();
        $this->assertEquals(11.11, $result[0]->getValue());
        $this->assertEquals(13.31, $result[1]->getValue());
        // The value in the state must be unchanged.
        $stateVal = $assessmentTestSession['Q01.var2'];
        $this->assertEquals(10.1, $stateVal[0]->getValue());
        $this->assertEquals(12.1, $stateVal[1]->getValue());
    }

    public function testMultipleOccurences()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection_withreplacement.xml');

        $sessionManager = new SessionManager(new FileSystemFileManager());
        $session = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $variableExpr = $this->createComponentFromXml('<variable identifier="Q01.SCORE"/>');
        $occurenceVariableExpression = $this->createComponentFromXml('<variable identifier="Q01.1.SCORE"/>');
        $variableProcessor = new VariableProcessor($variableExpr);
        $variableProcessor->setState($session);

        // non begun test session.
        $this->assertSame(null, $variableProcessor->process());
        $variableProcessor->setExpression($occurenceVariableExpression);
        $this->assertSame(null, $variableProcessor->process());

        // begun test session.
        $variableProcessor->setExpression($variableExpr);
        $session->beginTestSession();

        // Why not 0.0? Because we are in individual mode.
        $variableProcessor->setExpression($occurenceVariableExpression);

        // Why not NULL? Because we are in a linear test and Q01 is eligible for selection.
        // The item session is then instantiated. Outcome variables are set to their default
        // when the item session instantiation occurs.
        $this->assertSame(0.0, $variableProcessor->process()->getValue());

        // Q01.1
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));

        $variableProcessor->setExpression($variableExpr);
        $result = $variableProcessor->process();
        // Null because submission mode is individual...
        // From IMS QTI:
        // The value of an item variable taken from an item instantiated multiple times from the same
        // assessmentItemRef (through the use of selection withReplacement) is taken from the last instance
        // submitted if submission is simultaneous, otherwise it is undefined.
        $this->assertSame(null, $result);

        $variableProcessor->setExpression($occurenceVariableExpression);
        $result = $variableProcessor->process();
        $this->assertInstanceOf(QtiFloat::class, $result);
        $this->assertEquals(1.0, $result->getValue());
        $session->moveNext();

        // Q01.2
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB'))]));

        $variableProcessor->setExpression($variableExpr);
        $result = $variableProcessor->process();
        $this->assertSame(null, $result);

        // $occurenceVariableExpression still targets Q01.1
        $variableProcessor->setExpression($occurenceVariableExpression);
        $result = $variableProcessor->process();
        $this->assertInstanceOf(QtiFloat::class, $result);
        $this->assertEquals(1.0, $result->getValue());

        // $occurenceVariableExpression now targets Q01.2
        $occurenceVariableExpression->setIdentifier('Q01.2.SCORE');
        $result = $variableProcessor->process();
        $this->assertInstanceOf(QtiFloat::class, $result);
        $this->assertEquals(0.0, $result->getValue());
    }
}
