<?php

namespace qtismtest\runtime\expressions;

use qtism\common\collections\IdentifierCollection;
use qtism\common\datatypes\QtiFloat;
use qtism\common\enums\BaseType;
use qtism\data\expressions\OutcomeMaximum;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\expressions\OutcomeMaximumProcessor;
use qtismtest\QtiSmItemSubsetTestCase;

/**
 * Class OutcomeMaximumProcessorTest
 */
class OutcomeMaximumProcessorTest extends QtiSmItemSubsetTestCase
{
    /**
     * @dataProvider outcomeMaximumProvider
     *
     * @param OutcomeMaximum $expression
     * @param int $expectedResult
     */
    public function testOutcomeMaximum(OutcomeMaximum $expression, $expectedResult)
    {
        $session = $this->getTestSession();

        $processor = new OutcomeMaximumProcessor($expression);
        $processor->setState($session);
        $result = $processor->process();

        if ($expectedResult === null) {
            $this->assertSame($expectedResult, $result);
        } else {
            $this->assertInstanceOf(MultipleContainer::class, $result);
            $this->assertEquals(BaseType::FLOAT, $result->getBaseType());
            $this->assertTrue($result->equals($expectedResult));
        }
    }

    /**
     * @return array
     */
    public function outcomeMaximumProvider()
    {
        return [
            [self::getOutcomeMaximum('SCORE'), null], // NULL values involved, the expression returns NULL systematically.
            [self::getOutcomeMaximum('SCOREX'), null], // No variable at all matches.
            [self::getOutcomeMaximum('SCORE', '', '', new IdentifierCollection(['maximum'])), new MultipleContainer(BaseType::FLOAT, [new QtiFloat(2.5), new QtiFloat(1.5)])],
            [self::getOutcomeMaximum('SCORE', 'W0X', '', new IdentifierCollection(['maximum'])), new MultipleContainer(BaseType::FLOAT, [new QtiFloat(2.5), new QtiFloat(1.5)])], // Weight not found then not applied.
            [self::getOutcomeMaximum('SCORE', 'W01', '', new IdentifierCollection(['maximum'])), new MultipleContainer(BaseType::FLOAT, [new QtiFloat(5.0), new QtiFloat(3.0)])],
            [self::getOutcomeMaximum('SCORE', '', 'S01', new IdentifierCollection(['maximum'])), new MultipleContainer(BaseType::FLOAT, [new QtiFloat(2.5)])],
            [self::getOutcomeMaximum('SCORE', '', 'S02', new IdentifierCollection(['maximum'])), new MultipleContainer(BaseType::FLOAT, [new QtiFloat(1.5)])],
            [self::getOutcomeMaximum('SCORE', 'W01', 'S01', new IdentifierCollection(['maximum'])), new MultipleContainer(BaseType::FLOAT, [new QtiFloat(5.0)])],
            [self::getOutcomeMaximum('SCORE', 'W01', 'S02', new IdentifierCollection(['maximum'])), new MultipleContainer(BaseType::FLOAT, [new QtiFloat(3.0)])],
        ];
    }

    /**
     * @param $outcomeIdentifier
     * @param string $weightIdentifier
     * @param string $sectionIdentifier
     * @param IdentifierCollection|null $includeCategories
     * @param IdentifierCollection|null $excludeCategories
     * @return OutcomeMaximum
     */
    protected static function getOutcomeMaximum($outcomeIdentifier, $weightIdentifier = '', $sectionIdentifier = '', IdentifierCollection $includeCategories = null, IdentifierCollection $excludeCategories = null)
    {
        $outcomeMaximum = new OutcomeMaximum($outcomeIdentifier);
        $outcomeMaximum->setSectionIdentifier($sectionIdentifier);

        if (empty($includeCategories) === false) {
            $outcomeMaximum->setIncludeCategories($includeCategories);
        }

        if (empty($excludeCategories) === false) {
            $outcomeMaximum->setExcludeCategories($excludeCategories);
        }

        if (empty($weightIdentifier) === false) {
            $outcomeMaximum->setWeightIdentifier($weightIdentifier);
        }

        return $outcomeMaximum;
    }
}
