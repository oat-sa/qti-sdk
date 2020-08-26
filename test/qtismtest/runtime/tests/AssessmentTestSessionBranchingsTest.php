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
use qtism\runtime\tests\OrderingException;
use qtism\runtime\tests\SessionManager;
use qtismtest\QtiSmAssessmentTestSessionTestCase;

/**
 * Class AssessmentTestSessionBranchingsTest
 */
class AssessmentTestSessionBranchingsTest extends QtiSmAssessmentTestSessionTestCase
{
    public function testInstantiationSample1()
    {
        $doc = new XmlCompactDocument('2.1');
        $doc->load(self::samplesDir() . 'custom/runtime/branchings/branchings_single_section_linear.xml');

        $manager = new SessionManager(new FileSystemFileManager());
        $testSession = $manager->createAssessmentTestSession($doc->getDocumentComponent());

        $route = $testSession->getRoute();

        // $routeItemQ01 must have a single branchRule targeting Q03.
        $routeItemQ01 = $route->getRouteItemAt(0);
        $branchRules = $routeItemQ01->getBranchRules();
        $this->assertEquals(1, count($branchRules));
        $this->assertEquals('Q03', $branchRules[0]->getTarget());

        // $routeItemQ02 must have a single branchRule targeting Q04.
        $routeItemQ02 = $route->getRouteItemAt(1);
        $branchRules = $routeItemQ02->getBranchRules();
        $this->assertEquals(1, count($branchRules));
        $this->assertEquals('Q04', $branchRules[0]->getTarget());

        // $routeItemQ03 must have a single branchRule targeting EXIT_TEST
        $routeItemQ03 = $route->getRouteItemAt(2);
        $branchRules = $routeItemQ03->getBranchRules();
        $this->assertEquals(1, count($branchRules));
        $this->assertEquals('EXIT_TEST', $branchRules[0]->getTarget());

        // $routeItemQ04 is the end of the test and has no branchRules.
        $routeItemQ04 = $route->getRouteItemAt(3);
        $this->assertEquals(0, count($routeItemQ04->getBranchRules()));
    }

    public function testBranchingSingleSectionLinear1()
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
        $this->assertEquals(1.0, $testSession['Q01.SCORE']->getValue());
        $testSession->moveNext();

        // Q03 - Are we there? We answer incorrect to take Q04.
        $this->assertEquals('Q03', $testSession->getCurrentAssessmentItemRef()->getIdentifier());
        $testSession->beginAttempt();
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceZ'))]);
        $testSession->endAttempt($responses);
        $testSession->moveNext();

        // Q04 - Last item, nothing special.
        $this->assertEquals('Q04', $testSession->getCurrentAssessmentItemRef()->getIdentifier());
        $testSession->beginAttempt();
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceD'))]);
        $testSession->endAttempt($responses);
        $testSession->moveNext();

        // Test the global scope.
        $this->assertFalse($testSession->isRunning());

        $this->assertEquals(1.0, $testSession['Q01.SCORE']->getValue());
        $this->assertSame(null, $testSession['Q02.SCORE']); // Not eligible.
        $this->assertEquals(0.0, $testSession['Q03.SCORE']->getValue());
        $this->assertEquals(1.0, $testSession['Q04.SCORE']->getValue());
    }

    public function testBranchingSingleSectionLinear2()
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
        $this->assertFalse($testSession->isRunning());
        $this->assertEquals($testSession->getState(), AssessmentTestSessionState::CLOSED);
        $this->assertEquals(1.0, $testSession['Q01.SCORE']->getValue());
        $this->assertSame(null, $testSession['Q02.SCORE']); // Not eligible.
        $this->assertEquals(1.0, $testSession['Q03.SCORE']->getValue());
        $this->assertSame(null, $testSession['Q04.SCORE']); // Not eligible.
    }

    public function testBranchingSingleSectionNonLinear1()
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

        $this->assertEquals('Q02', $testSession->getCurrentAssessmentItemRef()->getIdentifier());
    }

    public function testBranchingSingleSectionNonLinear2()
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

        $this->assertEquals('Q03', $testSession->getCurrentAssessmentItemRef()->getIdentifier());
    }

    /**
     * @dataProvider branchingMultipleOccurencesProvider
     * @param $response
     * @param $expectedTarget
     * @param $occurence
     * @throws XmlStorageException
     * @throws AssessmentItemSessionException
     * @throws AssessmentTestSessionException
     * @throws PhpStorageException
     */
    public function testBranchingMultipleOccurences($response, $expectedTarget, $occurence)
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

        $this->assertEquals($expectedTarget, $testSession->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertEquals($occurence, $testSession->getCurrentAssessmentItemRefOccurence());
    }

    /**
     * @return array
     */
    public function branchingMultipleOccurencesProvider()
    {
        return [
            [new QtiIdentifier('goto21'), 'Q02', 0],
            [new QtiIdentifier('goto22'), 'Q02', 1],
            [new QtiIdentifier('goto23'), 'Q02', 2],
            [null, 'Q02', 3],
        ];
    }

    public function testBranchingOnPreconditon()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/branchings_preconditions/branchings_preconditions_branchtopreconditionitem.xml');
        $session->beginTestSession();

        // Only the first item session should be created.
        $this->assertSame(0.0, $session['Q01.SCORE']->getValue());
        $this->assertSame(null, $session['Q02.SCORE']);
        $this->assertSame(null, $session['Q03.SCORE']);
        $this->assertSame(null, $session['Q04.SCORE']);

        // Q01 - Incorrect
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB'))]));
        $session->moveNext();

        // Q04 - We should be at Q04.
        // -> because Q03 has a precondition which returns false.
        $this->assertEquals('Q04', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceD'))]));
        $session->moveNext();

        // Only item sessions related to Q01 and Q04 should be instantiated.
        $this->assertSame(0.0, $session['Q01.SCORE']->getValue());
        $this->assertSame(null, $session['Q02.SCORE']);
        $this->assertSame(null, $session['Q03.SCORE']);
        $this->assertSame(1.0, $session['Q04.SCORE']->getValue());
    }
}
