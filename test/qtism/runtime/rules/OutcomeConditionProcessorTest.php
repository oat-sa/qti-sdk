<?php
require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\State;
use qtism\runtime\rules\OutcomeConditionProcessor;
use qtism\runtime\rules\RuleProcessingException;

class OutcomeConditionProcessorTest extends QtiSmTestCase {
	
	/**
	 * @dataProvider testOutcomeConditionComplexProvider
	 * 
	 * @param integer $t
	 * @param integer $tt
	 * @param string $expectedX
	 * @param string $expectedY
	 * @param string $expectedZ
	 */
	public function testOutcomeConditionComplex($t, $tt, $expectedX, $expectedY, $expectedZ) {
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
		
		$this->assertSame($expectedX, $state['x']);
		$this->assertSame($expectedY, $state['y']);
		$this->assertSame($expectedZ, $state['z']);
	}
	
	public function testWrongRuleType() {
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
		
		$this->setExpectedException('\\InvalidArgumentException');
		$engine = new OutcomeConditionProcessor($rule);
	}
	
	public function testOutcomeConditionComplexProvider() {
		return array(
			array(1, 1, 'A', 'C', null),
			array(1, 0, 'B', 'C', null),
			array(2, 0, null, 'A', 'B'),
			array(3, 0, 'V', null, null),
			array(4, 1, 'Z', null, null)
		);
	}
}