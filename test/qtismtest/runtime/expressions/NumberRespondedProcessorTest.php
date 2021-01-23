<?php

namespace qtismtest\runtime\expressions;

use qtism\common\collections\IdentifierCollection;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiPoint;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\expressions\NumberResponded;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\expressions\NumberRespondedProcessor;
use qtismtest\QtiSmItemSubsetTestCase;

/**
 * Class NumberRespondedProcessorTest
 */
class NumberRespondedProcessorTest extends QtiSmItemSubsetTestCase
{
    public function testNumberResponded()
    {
        $session = $this->getTestSession();

        $overallResponded = self::getNumberResponded();
        $includeMathResponded = self::getNumberResponded('', new IdentifierCollection(['mathematics']));
        $processor = new NumberRespondedProcessor($overallResponded);
        $processor->setState($session);

        // Nothing responded yet.
        $this::assertEquals(0, $processor->process()->getValue());
        $processor->setExpression($includeMathResponded);
        $this::assertEquals(0, $processor->process()->getValue());

        // Q01
        $session->beginAttempt();
        $responses = new State();
        // Correct!
        $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA')));
        $session->endAttempt($responses);
        $processor->setExpression($overallResponded);
        $this::assertEquals(1, $processor->process()->getValue());
        $processor->setExpression($includeMathResponded);
        $this::assertEquals(1, $processor->process()->getValue());
        $session->moveNext();

        // Q02
        $responses->reset();
        $session->beginAttempt();
        $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, [new QtiPair('A', 'P'), new QtiPair('D', 'L')])));
        $session->endAttempt($responses);
        $this::assertEquals(3, $session['Q02.SCORE']->getValue());  // just for fun...
        $processor->setExpression($overallResponded);
        $this::assertEquals(2, $processor->process()->getValue());
        $processor->setExpression($includeMathResponded);
        $this::assertEquals(1, $processor->process()->getValue());
        $session->moveNext();

        // Q03
        $session->beginAttempt();
        $session->endAttempt(new State());
        $processor->setExpression($overallResponded);
        $this::assertEquals(2, $processor->process()->getValue());
        $session->moveNext();

        // Q04
        $responses->reset();
        $session->beginAttempt();
        $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR, new MultipleContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('W', 'G1'), new QtiDirectedPair('Su', 'G2')])));
        $session->endAttempt($responses);
        $this::assertEquals(3, $session['Q04.SCORE']->getValue());
        $this::assertEquals(3, $processor->process()->getValue());
        $session->moveNext();

        // Q05
        $session->beginAttempt();
        $session->endAttempt(new State());
        $this::assertEquals(3, $processor->process()->getValue());
        $session->moveNext();

        // Q06
        $responses->reset();
        $session->beginAttempt();
        $responses->setVariable(new ResponseVariable('answer', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('A')));
        $session->endAttempt($responses);
        $this::assertEquals(4, $processor->process()->getValue());
        $session->moveNext();

        // Q07.1
        $responses->reset();
        $session->beginAttempt();
        $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(100, 100)));
        $session->endAttempt($responses);
        $this::assertEquals(1, $session['Q07.1.SCORE']->getValue());
        $this::assertEquals(5, $processor->process()->getValue());
        $session->moveNext();

        // Q07.2
        $responses->reset();
        $session->beginAttempt();
        $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(10, 10)));
        $session->endAttempt($responses);
        $this::assertEquals(0, $session['Q07.2.SCORE']->getValue());
        $this::assertEquals(6, $processor->process()->getValue());
        $session->moveNext();

        // Q07.3
        $responses->reset();
        $session->beginAttempt();
        $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(102, 113)));
        $session->endAttempt($responses);
        $this::assertEquals(1, $session['Q07.3.SCORE']->getValue());
        $this::assertEquals(7, $processor->process()->getValue());
        $session->moveNext();
    }

    /**
     * @param string $sectionIdentifier
     * @param IdentifierCollection|null $includeCategories
     * @param IdentifierCollection|null $excludeCategories
     * @return NumberResponded
     */
    protected static function getNumberResponded($sectionIdentifier = '', IdentifierCollection $includeCategories = null, IdentifierCollection $excludeCategories = null)
    {
        $numberResponded = new NumberResponded();
        $numberResponded->setSectionIdentifier($sectionIdentifier);

        if (empty($includeCategories) === false) {
            $numberResponded->setIncludeCategories($includeCategories);
        }

        if (empty($excludeCategories) === false) {
            $numberResponded->setExcludeCategories($excludeCategories);
        }

        return $numberResponded;
    }
}
