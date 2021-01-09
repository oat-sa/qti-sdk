<?php

namespace qtismtest\runtime\expressions;

use qtism\common\collections\IdentifierCollection;
use qtism\data\expressions\NumberPresented;
use qtism\data\storage\php\PhpStorageException;
use qtism\runtime\expressions\NumberPresentedProcessor;
use qtism\runtime\tests\AssessmentItemSessionException;
use qtism\runtime\tests\AssessmentTestSessionException;
use qtismtest\QtiSmItemSubsetTestCase;

/**
 * Class NumberPresentedProcessorTest
 */
class NumberPresentedProcessorTest extends QtiSmItemSubsetTestCase
{
    /**
     * @dataProvider numberPresentedProvider
     *
     * @param NumberPresented $expression
     * @param array $expectedResults
     * @throws PhpStorageException
     * @throws AssessmentItemSessionException
     * @throws AssessmentTestSessionException
     */
    public function testNumberPresented(NumberPresented $expression, array $expectedResults)
    {
        $session = $this->getTestSession();
        $processor = new NumberPresentedProcessor($expression);
        $processor->setState($session);

        // At the moment, nothing presented.
        $result = $processor->process();
        $this::assertEquals(0, $result->getValue());

        for ($i = 0; $i < $session->getRouteCount(); $i++) {
            $session->beginAttempt();
            $processor = new NumberPresentedProcessor($expression);
            $processor->setState($session);
            $result = $processor->process();

            $this::assertEquals($expectedResults[$i], $result->getValue());
            $session->skip();
            $session->moveNext();
        }
    }

    /**
     * @return array
     */
    public function numberPresentedProvider()
    {
        return [
            [self::getNumberPresented(), [1, 2, 3, 4, 5, 6, 7, 8, 9]],
            [self::getNumberPresented('S01'), [1, 2, 3, 3, 3, 3, 3, 3, 3]],
            [self::getNumberPresented('S02'), [0, 0, 0, 1, 2, 3, 3, 3, 3]],
            [self::getNumberPresented('', new IdentifierCollection(['mathematics'])), [1, 1, 2, 2, 2, 3, 3, 3, 3]],
            [self::getNumberPresented('S01', new IdentifierCollection(['mathematics'])), [1, 1, 2, 2, 2, 2, 2, 2, 2]],
            [self::getNumberPresented('', null, new IdentifierCollection(['mathematics'])), [0, 1, 1, 2, 3, 3, 4, 5, 6]],
            [self::getNumberPresented('S02', null, new IdentifierCollection(['mathematics'])), [0, 0, 0, 1, 2, 2, 2, 2, 2]],
            [self::getNumberPresented('S03'), [0, 0, 0, 0, 0, 0, 1, 2, 3]],
        ];
    }

    /**
     * @param string $sectionIdentifier
     * @param IdentifierCollection|null $includeCategories
     * @param IdentifierCollection|null $excludeCategories
     * @return NumberPresented
     */
    protected static function getNumberPresented($sectionIdentifier = '', IdentifierCollection $includeCategories = null, IdentifierCollection $excludeCategories = null)
    {
        $numberPresented = new NumberPresented();
        $numberPresented->setSectionIdentifier($sectionIdentifier);

        if (empty($includeCategories) === false) {
            $numberPresented->setIncludeCategories($includeCategories);
        }

        if (empty($excludeCategories) === false) {
            $numberPresented->setExcludeCategories($excludeCategories);
        }

        return $numberPresented;
    }
}
