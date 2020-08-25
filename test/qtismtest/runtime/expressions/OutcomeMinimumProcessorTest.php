<?php

namespace qtismtest\runtime\expressions;

use qtism\common\collections\IdentifierCollection;
use qtism\common\datatypes\QtiFloat;
use qtism\common\enums\BaseType;
use qtism\data\expressions\OutcomeMinimum;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\expressions\OutcomeMinimumProcessor;
use qtismtest\QtiSmItemSubsetTestCase;

/**
 * Class OutcomeMinimumProcessorTest
 *
 * @package qtismtest\runtime\expressions
 */
class OutcomeMinimumProcessorTest extends QtiSmItemSubsetTestCase
{
    /**
     * @dataProvider outcomeMinimumProvider
     *
     * @param OutcomeMinimum $expression
     * @param int $expectedResult
     */
    public function testOutcomeMaximum(OutcomeMinimum $expression, $expectedResult)
    {
        $session = $this->getTestSession();

        $processor = new OutcomeMinimumProcessor($expression);
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
    public function outcomeMinimumProvider()
    {
        return [
            [self::getOutcomeMinimum('SCORE'), new MultipleContainer(BaseType::FLOAT, [new QtiFloat(-2.0), new QtiFloat(0.5), new QtiFloat(1.0), new QtiFloat(1.0), new QtiFloat(1.0), new QtiFloat(1.0)])],
            [self::getOutcomeMinimum('SCORE', '', '', new IdentifierCollection(['minimum'])), new MultipleContainer(BaseType::FLOAT, [new QtiFloat(-2.0), new QtiFloat(0.5), new QtiFloat(1.0), new QtiFloat(1.0), new QtiFloat(1.0), new QtiFloat(1.0)])],
            [self::getOutcomeMinimum('SCORE', 'W01', '', new IdentifierCollection(['minimum'])), new MultipleContainer(BaseType::FLOAT, [new QtiFloat(-4.0), new QtiFloat(1.0), new QtiFloat(2.0), new QtiFloat(2.0), new QtiFloat(2.0), new QtiFloat(2.0)])],
            [self::getOutcomeMinimum('SCORE', 'W01', '', new IdentifierCollection(['minimum', 'maximum'])), new MultipleContainer(BaseType::FLOAT, [new QtiFloat(-4.0), new QtiFloat(1.0), new QtiFloat(2.0), new QtiFloat(2.0), new QtiFloat(2.0), new QtiFloat(2.0)])],
            [self::getOutcomeMinimum('SCORE', 'W01'), new MultipleContainer(BaseType::FLOAT, [new QtiFloat(-4.0), new QtiFloat(1.0), new QtiFloat(2.0), new QtiFloat(2.0), new QtiFloat(2.0), new QtiFloat(2.0)])],
            [self::getOutcomeMinimum('SCORE', 'W02', '', new IdentifierCollection(['minimum'])), new MultipleContainer(BaseType::FLOAT, [new QtiFloat(-2.0), new QtiFloat(0.5), new QtiFloat(1.0), new QtiFloat(1.0), new QtiFloat(1.0), new QtiFloat(1.0)])], // Weight not found
        ];
    }

    /**
     * @param $outcomeIdentifier
     * @param string $weightIdentifier
     * @param string $sectionIdentifier
     * @param IdentifierCollection|null $includeCategories
     * @param IdentifierCollection|null $excludeCategories
     * @return OutcomeMinimum
     */
    protected static function getOutcomeMinimum($outcomeIdentifier, $weightIdentifier = '', $sectionIdentifier = '', IdentifierCollection $includeCategories = null, IdentifierCollection $excludeCategories = null)
    {
        $outcomeMinimum = new OutcomeMinimum($outcomeIdentifier);
        $outcomeMinimum->setSectionIdentifier($sectionIdentifier);

        if (empty($includeCategories) === false) {
            $outcomeMinimum->setIncludeCategories($includeCategories);
        }

        if (empty($excludeCategories) === false) {
            $outcomeMinimum->setExcludeCategories($excludeCategories);
        }

        if (empty($weightIdentifier) === false) {
            $outcomeMinimum->setWeightIdentifier($weightIdentifier);
        }

        return $outcomeMinimum;
    }
}
