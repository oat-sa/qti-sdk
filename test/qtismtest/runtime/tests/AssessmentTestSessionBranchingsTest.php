<?php

namespace qtismtest\runtime\tests;

use qtism\common\datatypes\files\FileSystemFileManager;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\storage\php\PhpStorageException;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\data\storage\xml\XmlStorageException;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\tests\AssessmentItemSessionException;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\runtime\tests\AssessmentTestSessionException;
use qtism\runtime\tests\AssessmentTestSessionState;
use qtism\runtime\tests\SessionManager;
use qtismtest\QtiSmAssessmentTestSessionTestCase;

/**
 * Class AssessmentTestSessionBranchingsTest
 */
class AssessmentTestSessionBranchingsTest extends QtiSmAssessmentTestSessionTestCase
{
    public function testInstantiationSample1(): void
    {
        $doc = new XmlCompactDocument('2.1');
        $doc->load(self::samplesDir() . 'custom/runtime/branchings/branchings_single_section_linear.xml');

        $manager = new SessionManager(new FileSystemFileManager());
        $testSession = $manager->createAssessmentTestSession($doc->getDocumentComponent());

        $route = $testSession->getRoute();

        // $routeItemQ01 must have a single branchRule targeting Q03.
        $routeItemQ01 = $route->getRouteItemAt(0);
        $branchRules = $routeItemQ01->getBranchRules();
        $this::assertCount(1, $branchRules);
        $this::assertEquals('Q03', $branchRules[0]->getTarget());

        // $routeItemQ02 must have a single branchRule targeting Q04.
        $routeItemQ02 = $route->getRouteItemAt(1);
        $branchRules = $routeItemQ02->getBranchRules();
        $this::assertCount(1, $branchRules);
        $this::assertEquals('Q04', $branchRules[0]->getTarget());

        // $routeItemQ03 must have a single branchRule targeting EXIT_TEST
        $routeItemQ03 = $route->getRouteItemAt(2);
        $branchRules = $routeItemQ03->getBranchRules();
        $this::assertCount(1, $branchRules);
        $this::assertEquals('EXIT_TEST', $branchRules[0]->getTarget());

        // $routeItemQ04 is the end of the test and has no branchRules.
        $routeItemQ04 = $route->getRouteItemAt(3);
        $this::assertCount(0, $routeItemQ04->getBranchRules());
    }

    public function testBranchingSingleSectionLinear1(): void
    {
        $doc = new XmlCompactDocument('2.1');
        $doc->load(self::samplesDir() . 'custom/runtime/branchings/branchings_single_section_linear.xml');

        $manager = new SessionManager(new FileSystemFileManager());
        $testSession = $manager->createAssessmentTestSession($doc->getDocumentComponent());
        $testSession->beginTestSession();

        // Q01 - We answer correct to bypass Q02.
        $testSession->beginAttempt();
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]);
        $testSession->endAttempt($responses);

        // Correct? Then we should go to Q03.
        $this::assertEquals(1.0, $testSession['Q01.SCORE']->getValue());
        $testSession->moveNext();

        // Q03 - Are we there? We answer incorrect to take Q04.
        $this::assertEquals('Q03', $testSession->getCurrentAssessmentItemRef()->getIdentifier());
        $testSession->beginAttempt();
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceZ'))]);
        $testSession->endAttempt($responses);
        $testSession->moveNext();

        // Q04 - Last item, nothing special.
        $this::assertEquals('Q04', $testSession->getCurrentAssessmentItemRef()->getIdentifier());
        $testSession->beginAttempt();
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceD'))]);
        $testSession->endAttempt($responses);
        $testSession->moveNext();

        // Test the global scope.
        $this::assertFalse($testSession->isRunning());

        $this::assertEquals(1.0, $testSession['Q01.SCORE']->getValue());
        $this::assertNull($testSession['Q02.SCORE']); // Not eligible.
        $this::assertEquals(0.0, $testSession['Q03.SCORE']->getValue());
        $this::assertEquals(1.0, $testSession['Q04.SCORE']->getValue());
    }

    public function testBranchingSingleSectionLinear2(): void
    {
        $doc = new XmlCompactDocument('2.1');
        $doc->load(self::samplesDir() . 'custom/runtime/branchings/branchings_single_section_linear.xml');

        $manager = new SessionManager(new FileSystemFileManager());
        $testSession = $manager->createAssessmentTestSession($doc->getDocumentComponent());
        $testSession->beginTestSession();

        // Q01 - We answer correct to move to Q03.
        $testSession->beginAttempt();
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]);
        $testSession->endAttempt($responses);
        $testSession->moveNext();

        // Q03 - We want to reach the EXIT_TEST target.
        $testSession->beginAttempt();
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceC'))]);
        $testSession->endAttempt($responses);
        $testSession->moveNext();

        // We should have reached the end.
        $this::assertFalse($testSession->isRunning());
        $this::assertEquals(AssessmentTestSessionState::CLOSED, $testSession->getState());
        $this::assertEquals(1.0, $testSession['Q01.SCORE']->getValue());
        $this::assertNull($testSession['Q02.SCORE']); // Not eligible.
        $this::assertEquals(1.0, $testSession['Q03.SCORE']->getValue());
        $this::assertNull($testSession['Q04.SCORE']); // Not eligible.
    }

    public function testBranchingSingleSectionNonLinear1(): void
    {
        // This test only aims at testing if branch rules
        // are correctly ignored when the navigation mode is non linear.
        $doc = new XmlCompactDocument('2.1');
        $doc->load(self::samplesDir() . 'custom/runtime/branchings/branchings_single_section_nonlinear.xml');

        // Q01 - We answer correct. In linear mode we should go to Q03.
        // However, in non linear mode branch rules are ignored and we go then
        // to Q02.
        $manager = new SessionManager(new FileSystemFileManager());
        $testSession = $manager->createAssessmentTestSession($doc->getDocumentComponent());
        $testSession->beginTestSession();

        $testSession->beginAttempt();
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]);
        $testSession->endAttempt($responses);
        $testSession->moveNext();

        $this::assertEquals('Q02', $testSession->getCurrentAssessmentItemRef()->getIdentifier());
    }

    public function testBranchingSingleSectionNonLinear2(): void
    {
        // This test aims at testing that branch rules are not
        // ignored in non-linear tests if force branching is in force.
        $doc = new XmlCompactDocument('2.1');
        $doc->load(self::samplesDir() . 'custom/runtime/branchings/branchings_single_section_nonlinear.xml');

        // Q01 - We answer correct. In linear mode we should go to Q03.
        // As force branching is in force, it should behave as in linear mode.
        $manager = new SessionManager(new FileSystemFileManager());
        $testSession = $manager->createAssessmentTestSession($doc->getDocumentComponent(), null, AssessmentTestSession::FORCE_BRANCHING);
        $testSession->beginTestSession();

        $testSession->beginAttempt();
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]);
        $testSession->endAttempt($responses);
        $testSession->moveNext();

        $this::assertEquals('Q03', $testSession->getCurrentAssessmentItemRef()->getIdentifier());
    }

    /**
     * @dataProvider branchingMultipleOccurencesProvider
     * @param QtiIdentifier|null $response
     * @param string $expectedTarget
     * @param int $occurence
     * @throws AssessmentItemSessionException
     * @throws AssessmentTestSessionException
     * @throws PhpStorageException
     * @throws XmlStorageException
     */
    public function testBranchingMultipleOccurences($response, $expectedTarget, $occurence): void
    {
        // This test aims at testing the possibility to jump
        // on a particular item ref occurence.
        $doc = new XmlCompactDocument('2.1');
        $doc->load(self::samplesDir() . 'custom/runtime/branchings/branchings_multiple_occurences.xml');

        $manager = new SessionManager(new FileSystemFileManager());
        $testSession = $manager->createAssessmentTestSession($doc->getDocumentComponent());
        $testSession->beginTestSession();

        $testSession->beginAttempt();

        if (empty($response)) {
            // Skip!
            $testSession->endAttempt(new State());
            $testSession->moveNext();
        } else {
            $testSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, $response)]));
            $testSession->moveNext();
        }

        $this::assertEquals($expectedTarget, $testSession->getCurrentAssessmentItemRef()->getIdentifier());
        $this::assertEquals($occurence, $testSession->getCurrentAssessmentItemRefOccurence());
    }

    /**
     * @return array
     */
    public function branchingMultipleOccurencesProvider(): array
    {
        return [
            [new QtiIdentifier('goto21'), 'Q02', 0],
            [new QtiIdentifier('goto22'), 'Q02', 1],
            [new QtiIdentifier('goto23'), 'Q02', 2],
            [null, 'Q02', 3],
        ];
    }

    public function testBranchingOnPreconditon(): void
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/branchings_preconditions/branchings_preconditions_branchtopreconditionitem.xml');
        $session->beginTestSession();

        // Only the first item session should be created.
        $this::assertSame(0.0, $session['Q01.SCORE']->getValue());
        $this::assertNull($session['Q02.SCORE']);
        $this::assertNull($session['Q03.SCORE']);
        $this::assertNull($session['Q04.SCORE']);

        // Q01 - Incorrect
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB'))]));
        $session->moveNext();

        // Q04 - We should be at Q04.
        // -> because Q03 has a precondition which returns false.
        $this::assertEquals('Q04', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceD'))]));
        $session->moveNext();

        // Only item sessions related to Q01 and Q04 should be instantiated.
        $this::assertSame(0.0, $session['Q01.SCORE']->getValue());
        $this::assertNull($session['Q02.SCORE']);
        $this::assertNull($session['Q03.SCORE']);
        $this::assertSame(1.0, $session['Q04.SCORE']->getValue());
    }

    public function testBranchingOnTestPartsSimple1(): void
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/branchings/branchings_testparts.xml');
        $session->beginTestSession();

        // We are starting at item Q01, in testPart P01.
        $this->assertEquals('Q01', $session->getCurrentAssessmentItemRef()->getIdentifier());

        // Let's jump to testPart P03.
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('GotoP03'))]));
        $session->moveNext();

        // We expect to land in testPart P03, item Q03.
        $this->assertEquals('Q03', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertEquals('P03', $session->getCurrentTestPart()->getIdentifier());
    }

    public function testBranchingRules(): void
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/branchings/branching_rules.xml');
        $session->beginTestSession();

        $this->assertEquals('testPart-1', $session->getCurrentTestPart()->getIdentifier());
        $this->assertEquals('assessmentSection-1', $session->getCurrentAssessmentSection()->getIdentifier());
        $this->assertEquals('item-1', $session->getCurrentAssessmentItemRef()->getIdentifier());

        $session->moveNext();

        $this->assertEquals('testPart-1', $session->getCurrentTestPart()->getIdentifier());
        $this->assertEquals('assessmentSection-1', $session->getCurrentAssessmentSection()->getIdentifier());
        $this->assertEquals('item-3', $session->getCurrentAssessmentItemRef()->getIdentifier());

        $session->moveNext();

        $this->assertEquals('testPart-1', $session->getCurrentTestPart()->getIdentifier());
        $this->assertEquals('assessmentSection-3', $session->getCurrentAssessmentSection()->getIdentifier());
        $this->assertEquals('item-5', $session->getCurrentAssessmentItemRef()->getIdentifier());

        $session->moveNext();

        $this->assertEquals('testPart-3', $session->getCurrentTestPart()->getIdentifier());
        $this->assertEquals('assessmentSection-5', $session->getCurrentAssessmentSection()->getIdentifier());
        $this->assertEquals('item-7', $session->getCurrentAssessmentItemRef()->getIdentifier());

        $session->moveNext();

        $this->assertEquals('testPart-4', $session->getCurrentTestPart()->getIdentifier());
        $this->assertEquals('subsection-1', $session->getCurrentAssessmentSection()->getIdentifier());
        $this->assertEquals('item-9', $session->getCurrentAssessmentItemRef()->getIdentifier());

        $session->moveNext();

        $this->assertEquals('testPart-5', $session->getCurrentTestPart()->getIdentifier());
        $this->assertEquals('assessmentSection-8', $session->getCurrentAssessmentSection()->getIdentifier());
        $this->assertEquals('item-11', $session->getCurrentAssessmentItemRef()->getIdentifier());

        $session->moveNext();

        $this->assertEquals('testPart-6', $session->getCurrentTestPart()->getIdentifier());
        $this->assertEquals('assessmentSection-9', $session->getCurrentAssessmentSection()->getIdentifier());
        $this->assertEquals('item-12', $session->getCurrentAssessmentItemRef()->getIdentifier());

        $session->moveNext();

        $this->assertEquals('testPart-8', $session->getCurrentTestPart()->getIdentifier());
        $this->assertEquals('assessmentSection-10', $session->getCurrentAssessmentSection()->getIdentifier());
        $this->assertEquals('item-14', $session->getCurrentAssessmentItemRef()->getIdentifier());
    }
}
