<?php
namespace qtismtest\runtime\tests;

use qtismtest\QtiSmAssessmentTestSessionTestCase;
use qtismtest\runtime\tests\mocks\SimpleResultsSubmittableTestSession;
use qtismtest\runtime\tests\mocks\SimpleResultsSubmittableTestSessionFactory;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\files\FileSystemFileManager;
use qtism\runtime\tests\TestResultsSubmission;
use qtism\runtime\tests\AssessmentTestSessionState;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\data\storage\xml\XmlCompactDocument;

class AssessmentTestSessionResultsTest extends QtiSmAssessmentTestSessionTestCase {
    
    public function testTestResultsSubmissionNonLinearOutcomeProcessing() {
        // This test focuses on test results submission at outcome processing time.
        $file = self::samplesDir() . 'custom/runtime/results_linear.xml';
        $doc = new XmlCompactDocument();
        $doc->load($file);
        $factory = new SimpleResultsSubmittableTestSessionFactory(new FileSystemFileManager());
        $testSession = $factory->createAssessmentTestSession($doc->getDocumentComponent());
        $testSession->setTestResultsSubmission(TestResultsSubmission::OUTCOME_PROCESSING);
        $this->assertEquals($testSession->getState(), AssessmentTestSessionState::INITIAL);
        $testSession->beginTestSession();
        $this->assertEquals($testSession->getState(), AssessmentTestSessionState::INTERACTING);
        
        // Q01 - Failure
        $testSession->beginAttempt();
        $testSession->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB')))));
        $this->assertSame(0.0, $testSession['Q01.SCORE']->getValue());
        $testSession->moveNext();
        
        // Q02 - Success
        $testSession->beginAttempt();
        $testSession->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB')))));
        $this->assertSame(1.0, $testSession['Q02.SCORE']->getValue());
        $testSession->moveNext();
        
        // Q03 - Success
        $testSession->beginAttempt();
        $testSession->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceC')))));
        $this->assertSame(1.0, $testSession['Q03.SCORE']->getValue());
        $testSession->moveNext();
        
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $testSession->getState());
        
        // -- Let's test the submitted results.
        $submittedTestResults = $testSession->getSubmittedTestResults();
        $submittedItemResults = $testSession->getSubmittedItemResults();
        
        // Test Item Q01.
        $this->assertSame(0.0, $submittedItemResults['Q01.0.SCORE'][0]->getValue());
        
        // Test Item Q02.
        $this->assertSame(1.0, $submittedItemResults['Q02.0.SCORE'][0]->getValue());
        
        // Test Item Q03.
        $this->assertSame(1.0, $submittedItemResults['Q03.0.SCORE'][0]->getValue());
        
        // Test Results.
        $this->assertSame(0.0, $submittedTestResults['TEST_SCORE'][0]->getValue());
        $this->assertSame(round(0.50000, 3), round($submittedTestResults['TEST_SCORE'][1]->getValue(), 3));
        $this->assertSame(round(0.66666, 3), round($submittedTestResults['TEST_SCORE'][2]->getValue(), 3));
    }
    
    public function testTestResultsSubmissionNonLinearEnd() {
        // This test focuses on test results submission at outcome processing time.
        $file = self::samplesDir() . 'custom/runtime/results_linear.xml';
        $doc = new XmlCompactDocument();
        $doc->load($file);
        $factory = new SimpleResultsSubmittableTestSessionFactory(new FileSystemFileManager());
        $testSession = $factory->createAssessmentTestSession($doc->getDocumentComponent());
        $testSession->setTestResultsSubmission(TestResultsSubmission::END);
        $this->assertEquals($testSession->getState(), AssessmentTestSessionState::INITIAL);
        $testSession->beginTestSession();
        $this->assertEquals($testSession->getState(), AssessmentTestSessionState::INTERACTING);
    
        // Q01 - Failure
        $testSession->beginAttempt();
        $testSession->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB')))));
        $this->assertSame(0.0, $testSession['Q01.SCORE']->getValue());
        $testSession->moveNext();
    
        // Q02 - Success
        $testSession->beginAttempt();
        $testSession->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB')))));
        $this->assertSame(1.0, $testSession['Q02.SCORE']->getValue());
        $testSession->moveNext();
    
        // Q03 - Success
        $testSession->beginAttempt();
        $testSession->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceC')))));
        $this->assertSame(1.0, $testSession['Q03.SCORE']->getValue());
        $testSession->moveNext();
    
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $testSession->getState());
    
        // -- Let's test the submitted results.
        $submittedTestResults = $testSession->getSubmittedTestResults();
        $submittedItemResults = $testSession->getSubmittedItemResults();
    
        // Test Item Q01.
        $this->assertSame(0.0, $submittedItemResults['Q01.0.SCORE'][0]->getValue());
    
        // Test Item Q02.
        $this->assertSame(1.0, $submittedItemResults['Q02.0.SCORE'][0]->getValue());
    
        // Test Item Q03.
        $this->assertSame(1.0, $submittedItemResults['Q03.0.SCORE'][0]->getValue());
    
        // Test Results (submitted once).
        $this->assertSame(round(0.66666, 3), round($submittedTestResults['TEST_SCORE'][0]->getValue(), 3));
        $this->assertEquals(1, count($submittedTestResults['TEST_SCORE']));
    }
}
