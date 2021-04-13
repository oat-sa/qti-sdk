<?php

namespace qtismtest\runtime\tests;

use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\tests\AssessmentTestSession;
use qtismtest\QtiSmAssessmentTestSessionTestCase;

/**
 * Class AssessmentTestSessionPreConditionsTest
 */
class AssessmentTestSessionPreConditionsTest extends QtiSmAssessmentTestSessionTestCase
{
    public function testInstantiationSample1()
    {
        $testSession = self::instantiate(self::samplesDir() . 'custom/runtime/preconditions/preconditions_single_section_linear.xml');
        $route = $testSession->getRoute();

        // Q01 - No precondtions.
        $routeItem = $route->getRouteItemAt(0);
        $this::assertCount(0, $routeItem->getPreConditions());

        // Q02 - A precondition based on Q01.SCORE.
        $routeItem = $route->getRouteItemAt(1);
        $preConditions = $routeItem->getPreConditions();
        $this::assertCount(1, $preConditions);
        $var = $preConditions[0]->getComponentsByClassName('variable');
        $this::assertEquals('Q01.SCORE', $var[0]->getIdentifier());

        // Q03 - A precondition based on Q02.SCORE.
        $routeItem = $route->getRouteItemAt(2);
        $preConditions = $routeItem->getPreConditions();
        $this::assertCount(1, $preConditions);
        $var = $preConditions[0]->getComponentsByClassName('variable');
        $this::assertEquals('Q02.SCORE', $var[0]->getIdentifier());

        // Q04 - A precondition based on Q03.SCORE.
        $routeItem = $route->getRouteItemAt(3);
        $preConditions = $routeItem->getPreConditions();
        $this::assertCount(1, $preConditions);
        $var = $preConditions[0]->getComponentsByClassName('variable');
        $this::assertEquals('Q03.SCORE', $var[0]->getIdentifier());
    }

    public function testSingleSectionLinear1()
    {
        $testSession = self::instantiate(self::samplesDir() . 'custom/runtime/preconditions/preconditions_single_section_linear.xml');
        $testSession->beginTestSession();

        // Q01 - Answer incorrect to be redirected by successive false evaluated preconditions.
        $testSession->beginAttempt();
        $testSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB'))]));
        $testSession->moveNext();

        // Because of the autoforward, the test is finished.
        $this::assertFalse($testSession->isRunning());
        $this::assertInstanceOf(QtiFloat::class, $testSession['Q01.SCORE']);
        $this::assertEquals(0.0, $testSession['Q01.SCORE']->getValue());
        $this::assertNull($testSession['Q02.SCORE']);
        $this::assertNull($testSession['Q03.SCORE']);
        $this::assertNull($testSession['Q04.SCORE']);
    }

    public function testSingleSectionNonLinear1()
    {
        // This test aims at checking that preconditions are by default ignored when
        // the navigation mode is non linear.
        $testSession = self::instantiate(self::samplesDir() . 'custom/runtime/preconditions/preconditions_single_section_nonlinear.xml');
        $testSession->beginTestSession();

        // Q01 - Answer incorrect, you will get the next item.
        $this::assertEquals('Q01', $testSession->getCurrentAssessmentItemRef()->getIdentifier());
        $testSession->beginAttempt();
        $testSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB'))]));
        $testSession->moveNext();

        // Q02
        $this::assertTrue($testSession->isRunning(), 'The test session must be running.');
        $this::assertEquals('Q02', $testSession->getCurrentAssessmentItemRef()->getIdentifier());
    }

    public function testSingleSectionNonLinearForcePreconditions()
    {
        // This test aims at testing that when forcing preconditions is in force,
        // they are executed even if the current navigation mode is non linear.
        $testSession = self::instantiate(self::samplesDir() . 'custom/runtime/preconditions/preconditions_single_section_nonlinear.xml', false, AssessmentTestSession::FORCE_PRECONDITIONS);
        $testSession->beginTestSession();

        // Q01 - Answer incorrect to be redirected by successive false evaluated preconditions.
        $testSession->beginAttempt();
        $testSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB'))]));
        $testSession->moveNext();

        // Because of the autoforward, the test is finished.
        $this::assertFalse($testSession->isRunning());
        $this::assertInstanceOf(QtiFloat::class, $testSession['Q01.SCORE']);
        $this::assertEquals(0.0, $testSession['Q01.SCORE']->getValue());
        $this::assertNull($testSession['Q02.SCORE']);
        $this::assertNull($testSession['Q03.SCORE']);
        $this::assertNull($testSession['Q04.SCORE']);
    }

    public function testKillerTestEpicFail()
    {
        $testSession = self::instantiate(self::samplesDir() . 'custom/runtime/preconditions/preconditions_killertest.xml');
        $testSession->beginTestSession();

        $testSession->beginAttempt();
        $testSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('BadChoice'))]));
        $testSession->moveNext();

        // Incorrect answer = end of test.
        $this::assertFalse($testSession->isRunning());
        $this::assertInstanceOf(QtiFloat::class, $testSession['Q01.SCORE']);
        $this::assertEquals(0.0, $testSession['Q01.SCORE']->getValue());

        // Other items could not be instantiated.
        $this::assertNull($testSession['Q02.SCORE']);
        $this::assertNull($testSession['Q03.SCORE']);
        $this::assertNull($testSession['Q04.SCORE']);
        $this::assertNull($testSession['Q05.SCORE']);
    }

    public function testKillerTestEpicWin()
    {
        $testSession = self::instantiate(self::samplesDir() . 'custom/runtime/preconditions/preconditions_killertest.xml');
        $testSession->beginTestSession();

        $this::assertEquals('Q01', $testSession->getCurrentAssessmentItemRef()->getIdentifier());
        $testSession->beginAttempt();
        $testSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('GoodChoice'))]));
        $testSession->moveNext();
        $this::assertEquals(1.0, $testSession['Q01.SCORE']->getValue());

        $this::assertEquals('Q02', $testSession->getCurrentAssessmentItemRef()->getIdentifier());
        $testSession->beginAttempt();
        $testSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('GoodChoice'))]));
        $testSession->moveNext();
        $this::assertEquals(1.0, $testSession['Q02.SCORE']->getValue());

        $this::assertEquals('Q03', $testSession->getCurrentAssessmentItemRef()->getIdentifier());
        $testSession->beginAttempt();
        $testSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('GoodChoice'))]));
        $testSession->moveNext();
        $this::assertEquals(1.0, $testSession['Q03.SCORE']->getValue());

        $this::assertEquals('Q04', $testSession->getCurrentAssessmentItemRef()->getIdentifier());
        $testSession->beginAttempt();
        $testSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('GoodChoice'))]));
        $testSession->moveNext();
        $this::assertEquals(1.0, $testSession['Q04.SCORE']->getValue());

        $this::assertEquals('Q05', $testSession->getCurrentAssessmentItemRef()->getIdentifier());
        $testSession->beginAttempt();
        $testSession->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('GoodChoice'))]));
        $testSession->moveNext();
        $this::assertEquals(1.0, $testSession['Q05.SCORE']->getValue());

        $this::assertFalse($testSession->isRunning());
    }
}
