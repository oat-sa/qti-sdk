<?php

namespace qtismtest\runtime\rules;

use InvalidArgumentException;
use qtism\common\datatypes\QtiInteger;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\State;
use qtism\runtime\rules\OutcomeConditionProcessor;
use qtismtest\QtiSmTestCase;

/**
 * Class OutcomeConditionProcessorTest
 */
class OutcomeConditionProcessorTest extends QtiSmTestCase
{
    /**
     * @dataProvider outcomeConditionComplexProvider
     *
     * @param int $t
     * @param int $tt
     * @param string $expectedX
     * @param string $expectedY
     * @param string $expectedZ
     * @throws MarshallerNotFoundException
     */
    public function testOutcomeConditionComplex($t, $tt, $expectedX, $expectedY, $expectedZ)
    {
        $rule = $this->createComponentFromXml('
			<outcomeCondition>
				<outcomeIf>
					<equal>
						<variable identifier="t"/>
						<baseValue baseType="integer">1</baseValue>
					</equal>
					<outcomeCondition>
						<outcomeIf>
							<equal>
								<variable identifier="tt"/>
								<baseValue baseType="integer">1</baseValue>
							</equal>
							<setOutcomeValue identifier="x">
								<baseValue baseType="string">A</baseValue>
							</setOutcomeValue>
						</outcomeIf>
						<outcomeElse>
							<setOutcomeValue identifier="x">
								<baseValue baseType="string">B</baseValue>
							</setOutcomeValue>
						</outcomeElse>
					</outcomeCondition>
					<setOutcomeValue identifier="y">
						<baseValue baseType="string">C</baseValue>
					</setOutcomeValue>
				</outcomeIf>
				<outcomeElseIf>
					<equal>
						<variable identifier="t"/>
						<baseValue baseType="integer">2</baseValue>
					</equal>
					<setOutcomeValue identifier="y">
						<baseValue baseType="string">A</baseValue>
					</setOutcomeValue>
					<setOutcomeValue identifier="z">
						<baseValue baseType="string">B</baseValue>
					</setOutcomeValue>
				</outcomeElseIf>
				<outcomeElseIf>
					<equal>
						<variable identifier="t"/>
						<baseValue baseType="integer">3</baseValue>
					</equal>
					<setOutcomeValue identifier="x">
						<baseValue baseType="string">V</baseValue>
					</setOutcomeValue>
				</outcomeElseIf>
				<outcomeElse>
					<setOutcomeValue identifier="x">
						<baseValue baseType="string">Z</baseValue>
					</setOutcomeValue>
				</outcomeElse>
			</outcomeCondition>
		');

        $state = new State();
        $state->setVariable(new OutcomeVariable('t', Cardinality::SINGLE, BaseType::INTEGER, $t));
        $state->setVariable(new OutcomeVariable('tt', Cardinality::SINGLE, BaseType::INTEGER, $tt));
        $state->setVariable(new OutcomeVariable('x', Cardinality::SINGLE, BaseType::STRING));
        $state->setVariable(new OutcomeVariable('y', Cardinality::SINGLE, BaseType::STRING));
        $state->setVariable(new OutcomeVariable('z', Cardinality::SINGLE, BaseType::STRING));

        $processor = new OutcomeConditionProcessor($rule);
        $processor->setState($state);
        $processor->process();

        $this->check($expectedX, $state['x']);
        $this->check($expectedY, $state['y']);
        $this->check($expectedZ, $state['z']);
    }

    /**
     * @param $expected
     * @param $value
     */
    protected function check($expected, $value)
    {
        if ($expected === null) {
            $this::assertNull($value);
        } else {
            $this::assertSame($expected, $value->getValue());
        }
    }

    public function testWrongRuleType()
    {
        $rule = $this->createComponentFromXml('
			<responseCondition>
				<responseIf>
					<equal>
						<variable identifier="t"/>
						<baseValue baseType="integer">1337</baseValue>
					</equal>
					<setOutcomeValue identifier="x">
						<baseValue baseType="string">Piece of cake!</baseValue>
					</setOutcomeValue>
				</responseIf>
			</responseCondition>
		');

        $this->expectException(InvalidArgumentException::class);
        $engine = new OutcomeConditionProcessor($rule);
    }

    /**
     * @return array
     */
    public function outcomeConditionComplexProvider()
    {
        return [
            [new QtiInteger(1), new QtiInteger(1), 'A', 'C', null],
            [new QtiInteger(1), new QtiInteger(0), 'B', 'C', null],
            [new QtiInteger(2), new QtiInteger(0), null, 'A', 'B'],
            [new QtiInteger(3), new QtiInteger(0), 'V', null, null],
            [new QtiInteger(4), new QtiInteger(1), 'Z', null, null],
        ];
    }
}
