<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\common\datatypes\QtiDuration;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\AssessmentSectionCollection;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\InlineCollection;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\text\P;
use qtism\data\expressions\BaseValue;
use qtism\data\ExtendedAssessmentSection;
use qtism\data\ExtendedAssessmentTest;
use qtism\data\ExtendedTestPart;
use qtism\data\ItemSessionControl;
use qtism\data\processing\OutcomeProcessing;
use qtism\data\rules\BranchRule;
use qtism\data\rules\BranchRuleCollection;
use qtism\data\rules\OutcomeRuleCollection;
use qtism\data\rules\PreCondition;
use qtism\data\rules\PreConditionCollection;
use qtism\data\rules\SetOutcomeValue;
use qtism\data\ShowHide;
use qtism\data\state\OutcomeDeclaration;
use qtism\data\state\OutcomeDeclarationCollection;
use qtism\data\storage\xml\marshalling\CompactMarshallerFactory;
use qtism\data\TestFeedback;
use qtism\data\TestFeedbackAccess;
use qtism\data\TestFeedbackCollection;
use qtism\data\TestFeedbackRef;
use qtism\data\TestFeedbackRefCollection;
use qtism\data\TestPartCollection;
use qtism\data\TimeLimits;
use qtismtest\QtiSmTestCase;

class ExtendedAssessmentTestMarshallerTest extends QtiSmTestCase
{
    public function testMarshallMaximal()
    {
        $assessmentSection1 = new ExtendedAssessmentSection('section1', 'My Section 1', true);
        $assessmentSection2 = new ExtendedAssessmentSection('section2', 'My Section 2', true);

        $preCondition = new PreCondition(new BaseValue(BaseType::BOOLEAN, true));
        $branching = new BranchRule(new BaseValue(BaseType::BOOLEAN, true), 'EXIT_TESTPART');

        $itemSessionControl = new ItemSessionControl();
        $itemSessionControl->setShowSolution(true);

        $timeLimits = new TimeLimits(null, new QtiDuration('PT1M40S'));

        $p = new P();
        $p->setContent(new InlineCollection([new TextRun('Prima!')]));
        $testFeedback = new TestFeedback('feedback1', 'show', new FlowStaticCollection([$p]));
        $testFeedback->setTitle('hello!');
        $testFeedback->setAccess(TestFeedbackAccess::AT_END);
        $testFeedback->setShowHide(ShowHide::SHOW);

        $testFeedbackRef = new TestFeedbackRef('feedback1', 'show', TestFeedbackAccess::AT_END, ShowHide::SHOW, './TF01.xml');

        $assessmentSections = new AssessmentSectionCollection([$assessmentSection1, $assessmentSection2]);
        $preConditions = new PreConditionCollection([$preCondition]);
        $branchings = new BranchRuleCollection([$branching]);
        $testFeedbacks = new TestFeedbackCollection([$testFeedback]);
        $testFeedbackRefs = new TestFeedbackRefCollection([$testFeedbackRef]);

        $extendedTestPart = new ExtendedTestPart('part1', $assessmentSections);
        $extendedTestPart->setPreConditions($preConditions);
        $extendedTestPart->setBranchRules($branchings);
        $extendedTestPart->setItemSessionControl($itemSessionControl);
        $extendedTestPart->setTimeLimits($timeLimits);
        $extendedTestPart->setTestFeedbacks($testFeedbacks);
        $extendedTestPart->setTestFeedbackRefs($testFeedbackRefs);

        $outcomeDeclaration = new OutcomeDeclaration('COUNT', BaseType::INTEGER, Cardinality::SINGLE);
        $outcomeDeclarations = new OutcomeDeclarationCollection([$outcomeDeclaration]);

        $timeLimits = new TimeLimits(null, new QtiDuration('PT10M'));

        $outcomeRules = new OutcomeRuleCollection([new SetOutcomeValue('COUNT', new BaseValue(BaseType::INTEGER, 1))]);
        $outcomeProcessing = new OutcomeProcessing($outcomeRules);

        $p = new P();
        $p->setContent(new InlineCollection([new TextRun('Good!')]));
        $testFeedback = new TestFeedback('feedbackTest', 'show', new FlowStaticCollection([$p]));
        $testFeedback->setTitle('hello!');
        $testFeedback->setAccess(TestFeedbackAccess::AT_END);
        $testFeedback->setShowHide(ShowHide::SHOW);
        $testFeedbacks = new TestFeedbackCollection([$testFeedback]);

        $testFeedbackRef = new TestFeedbackRef('feedbackTest', 'show', TestFeedbackAccess::AT_END, ShowHide::SHOW, './TF02.xml');
        $testFeedbackRefs = new TestFeedbackRefCollection([$testFeedbackRef]);

        $extendedAssessmentTest = new ExtendedAssessmentTest('test1', 'A Test');
        $extendedAssessmentTest->setToolName('qtisdk');
        $extendedAssessmentTest->setToolVersion('0.0.0');
        $extendedAssessmentTest->setOutcomeDeclarations($outcomeDeclarations);
        $extendedAssessmentTest->setOutcomeProcessing($outcomeProcessing);
        $extendedAssessmentTest->setTestFeedbacks($testFeedbacks);
        $extendedAssessmentTest->setTestFeedbackRefs($testFeedbackRefs);
        $extendedAssessmentTest->setTestParts(new TestPartCollection([$extendedTestPart]));
        $extendedAssessmentTest->setTimeLimits($timeLimits);

        $factory = new CompactMarshallerFactory();
        $element = $factory->createMarshaller($extendedAssessmentTest)->marshall($extendedAssessmentTest);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals(
            '<assessmentTest identifier="test1" title="A Test" toolName="qtisdk" toolVersion="0.0.0"><outcomeDeclaration identifier="COUNT" cardinality="single" baseType="integer"/><timeLimits maxTime="600" allowLateSubmission="false"/><testPart identifier="part1" navigationMode="linear" submissionMode="individual"><preCondition><baseValue baseType="boolean">true</baseValue></preCondition><branchRule target="EXIT_TESTPART"><baseValue baseType="boolean">true</baseValue></branchRule><itemSessionControl maxAttempts="1" showFeedback="false" allowReview="true" showSolution="true" allowComment="false" allowSkipping="true" validateResponses="false"/><timeLimits maxTime="100" allowLateSubmission="false"/><assessmentSection identifier="section1" required="false" fixed="false" title="My Section 1" visible="true" keepTogether="true"/><assessmentSection identifier="section2" required="false" fixed="false" title="My Section 2" visible="true" keepTogether="true"/><testFeedback access="atEnd" outcomeIdentifier="show" showHide="show" identifier="feedback1" title="hello!"><p>Prima!</p></testFeedback><testFeedbackRef identifier="feedback1" outcomeIdentifier="show" access="atEnd" showHide="show" href="./TF01.xml"/></testPart><outcomeProcessing><setOutcomeValue identifier="COUNT"><baseValue baseType="integer">1</baseValue></setOutcomeValue></outcomeProcessing><testFeedback access="atEnd" outcomeIdentifier="show" showHide="show" identifier="feedbackTest" title="hello!"><p>Good!</p></testFeedback><testFeedbackRef identifier="feedbackTest" outcomeIdentifier="show" access="atEnd" showHide="show" href="./TF02.xml"/></assessmentTest>',
            $dom->saveXML($element)
        );
    }

    public function testUnmarshallMaximal()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '<assessmentTest identifier="test1" title="A Test" toolName="qtisdk" toolVersion="0.0.0">
                  <outcomeDeclaration identifier="COUNT" cardinality="single" baseType="integer"/>
                  <timeLimits maxTime="600" allowLateSubmission="false"/>
                  <testPart identifier="part1" navigationMode="linear" submissionMode="individual">
                    <preCondition>
                      <baseValue baseType="boolean">true</baseValue>
                    </preCondition>
                    <branchRule target="EXIT_TESTPART">
                      <baseValue baseType="boolean">true</baseValue>
                    </branchRule>
                    <itemSessionControl maxAttempts="1" showFeedback="false" allowReview="true" showSolution="true" allowComment="false" allowSkipping="true" validateResponses="false"/>
                    <timeLimits maxTime="100" allowLateSubmission="false"/>
                    <assessmentSection identifier="section1" required="false" fixed="false" title="My Section 1" visible="true" keepTogether="true"/>
                    <assessmentSection identifier="section2" required="false" fixed="false" title="My Section 2" visible="true" keepTogether="true"/>
                    <testFeedback access="atEnd" outcomeIdentifier="show" showHide="show" identifier="feedback1" title="hello!">
                      <p>Prima!</p>
                    </testFeedback>
                    <testFeedbackRef identifier="feedback1" outcomeIdentifier="show" access="atEnd" showHide="show" href="./TF01.xml"/>
                  </testPart>
                  <outcomeProcessing>
                    <setOutcomeValue identifier="COUNT">
                      <baseValue baseType="integer">1</baseValue>
                    </setOutcomeValue>
                  </outcomeProcessing>
                  <testFeedback access="atEnd" outcomeIdentifier="show" showHide="show" identifier="feedbackTest" title="hello!">
                    <p>Good!</p>
                  </testFeedback>
                  <testFeedbackRef identifier="feedbackTest" outcomeIdentifier="show" access="atEnd" showHide="show" href="./TF02.xml"/>
             </assessmentTest>'
        );

        $element = $dom->documentElement;
        $factory = new CompactMarshallerFactory();

        $marshaller = $factory->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf('qtism\\data\\ExtendedAssessmentTest', $component);
        $this->assertEquals('test1', $component->getIdentifier());
        $this->assertEquals('A Test', $component->getTitle());
        $this->assertEquals('qtisdk', $component->getToolName());
        $this->assertEquals('0.0.0', $component->getToolVersion());
        $this->assertTrue($component->hasTimeLimits());
        $this->assertEquals(1, count($component->getOutcomeDeclarations()));
        $this->assertEquals(1, count($component->getTestFeedbacks()));
        $this->assertEquals(1, count($component->getTestFeedbackRefs()));
        $this->assertTrue($component->hasOutcomeProcessing());

        $testParts = $component->getTestParts();
        $testPart = $testParts['part1'];

        $this->assertInstanceOf('qtism\\data\\ExtendedTestPart', $testPart);
        $this->assertEquals(1, count($testPart->getPreConditions()));
        $this->assertEquals(1, count($testPart->getBranchRules()));
        $this->assertTrue($testPart->getItemSessionControl()->mustShowSolution());
        $this->assertTrue($testPart->getTimeLimits()->getMaxTime()->equals(new QtiDuration('PT1M40S')));
        $this->assertEquals(1, count($testPart->getTestFeedbacks()));
        $this->assertEquals(1, count($testPart->getTestFeedbackRefs()));
        $this->assertEquals(2, count($testPart->getAssessmentSections()));

        // Check that we got ExtendedAssessmentSections.
        $assessmentSections = $testPart->getAssessmentSections();
        $this->assertInstanceOf('qtism\\data\\ExtendedAssessmentSection', $assessmentSections['section1']);
        $this->assertInstanceOf('qtism\\data\\ExtendedAssessmentSection', $assessmentSections['section2']);

        // Check that we got TestFeedbackRef instances.
        $testFeedbackRefs = $testPart->getTestFeedbackRefs();
        $this->assertInstanceOf('qtism\\data\\TestFeedbackRef', $testFeedbackRefs[0]);
    }
}
