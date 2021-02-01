<?php

namespace qtismtest\runtime\expressions;

use qtism\common\collections\IdentifierCollection;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiPoint;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\expressions\NumberCorrect;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\expressions\NumberCorrectProcessor;
use qtismtest\QtiSmItemSubsetTestCase;

/**
 * Class NumberCorrectProcessorTest
 */
class NumberCorrectProcessorTest extends QtiSmItemSubsetTestCase
{
    public function testNumberCorrect()
    {
        $session = $this->getTestSession();

        $overallCorrect = self::getNumberCorrect();
        $includeMathResponded = self::getNumberCorrect('', new IdentifierCollection(['mathematics']));
        $processor = new NumberCorrectProcessor($overallCorrect);
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
        $processor->setExpression($overallCorrect);
        $this::assertEquals(1, $processor->process()->getValue());
        $processor->setExpression($includeMathResponded);
        $this::assertEquals(1, $processor->process()->getValue());
        $session->moveNext();

        // Q02
        $responses->reset();
        $session->beginAttempt();
        // Incorrect!
        $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, [new QtiPair('A', 'P'), new QtiPair('D', 'L')])));
        $session->endAttempt($responses);
        $processor->setExpression($overallCorrect);
        $this::assertEquals(1, $processor->process()->getValue());
        $processor->setExpression($includeMathResponded);
        $this::assertEquals(1, $processor->process()->getValue());
        $session->moveNext();

        // Q03
        // Incorrect!
        $session->beginAttempt();
        $session->endAttempt(new State());
        $processor->setExpression($overallCorrect);
        $this::assertEquals(1, $processor->process()->getValue());
        $session->moveNext();

        // Q04
        // Correct!
        $responses->reset();
        $session->beginAttempt();
        $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR, new MultipleContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('W', 'G1'), new QtiDirectedPair('Su', 'G2')])));
        $session->endAttempt($responses);
        $this::assertEquals(2, $processor->process()->getValue());
        $session->moveNext();

        // Q05
        // Incorrect!
        $session->beginAttempt();
        $session->endAttempt(new State());
        $this::assertEquals(2, $processor->process()->getValue());
        $session->moveNext();

        // Q06
        // Correct!
        $responses->reset();
        $session->beginAttempt();
        $responses->setVariable(new ResponseVariable('answer', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('A')));
        $session->endAttempt($responses);
        $this::assertEquals(3, $processor->process()->getValue());
        $session->moveNext();

        // Q07.1
        // Incorrect
        $responses->reset();
        $session->beginAttempt();
        $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(100, 100)));
        $session->endAttempt($responses);
        $this::assertEquals(1, $session['Q07.1.SCORE']->getValue());
        $this::assertEquals(3, $processor->process()->getValue());
        $session->moveNext();

        // Q07.2
        // Incorrect!
        $responses->reset();
        $session->beginAttempt();
        $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(10, 10)));
        $session->endAttempt($responses);
        $this::assertEquals(0, $session['Q07.2.SCORE']->getValue());
        $this::assertEquals(3, $processor->process()->getValue());
        $session->moveNext();

        // Q07.3
        // Correct!
        $responses->reset();
        $session->beginAttempt();
        $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(102, 113)));
        $session->endAttempt($responses);
        $this::assertEquals(1, $session['Q07.3.SCORE']->getValue());
        $this::assertEquals(4, $processor->process()->getValue());
        $session->moveNext();
    }

    /**
     * @param string $sectionIdentifier
     * @param IdentifierCollection|null $includeCategories
     * @param IdentifierCollection|null $excludeCategories
     * @return NumberCorrect
     */
    protected static function getNumberCorrect($sectionIdentifier = '', IdentifierCollection $includeCategories = null, IdentifierCollection $excludeCategories = null)
    {
        $numberCorrect = new NumberCorrect();
        $numberCorrect->setSectionIdentifier($sectionIdentifier);

        if (empty($includeCategories) === false) {
            $numberCorrect->setIncludeCategories($includeCategories);
        }

        if (empty($excludeCategories) === false) {
            $numberCorrect->setExcludeCategories($excludeCategories);
        }

        return $numberCorrect;
    }
}
