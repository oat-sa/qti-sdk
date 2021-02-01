<?php

namespace qtismtest\runtime\expressions;

use qtism\common\collections\IdentifierCollection;
use qtism\data\expressions\NumberSelected;
use qtism\runtime\expressions\NumberSelectedProcessor;
use qtismtest\QtiSmItemSubsetTestCase;

/**
 * Class NumberSelectedProcessorTest
 */
class NumberSelectedProcessorTest extends QtiSmItemSubsetTestCase
{
    /**
     * @dataProvider numberSelectedProvider
     *
     * @param NumberSelected $expression
     * @param int $expectedResult
     */
    public function testNumberSelected(NumberSelected $expression, $expectedResult)
    {
        $session = $this->getTestSession();

        // The test is totally linear, the selection is then complete
        // when AssessmentTestSession::beginTestSession is called.
        $processor = new NumberSelectedProcessor($expression);
        $processor->setState($session);
        $result = $processor->process();
        $this::assertEquals($expectedResult, $result->getValue());
    }

    /**
     * @return array
     */
    public function numberSelectedProvider()
    {
        return [
            [self::getNumberSelected(), 9],
            [self::getNumberSelected('', new IdentifierCollection(['mathematics', 'chemistry'])), 4],
            [self::getNumberSelected('S01', new IdentifierCollection(['mathematics', 'chemistry'])), 2],
            [self::getNumberSelected('', null, new IdentifierCollection(['mathematics'])), 6],
        ];
    }

    /**
     * @param string $sectionIdentifier
     * @param IdentifierCollection|null $includeCategories
     * @param IdentifierCollection|null $excludeCategories
     * @return NumberSelected
     */
    protected static function getNumberSelected($sectionIdentifier = '', IdentifierCollection $includeCategories = null, IdentifierCollection $excludeCategories = null)
    {
        $numberSelected = new NumberSelected();
        $numberSelected->setSectionIdentifier($sectionIdentifier);

        if (empty($includeCategories) === false) {
            $numberSelected->setIncludeCategories($includeCategories);
        }

        if (empty($excludeCategories) === false) {
            $numberSelected->setExcludeCategories($excludeCategories);
        }

        return $numberSelected;
    }
}
