<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\rules\OutcomeCondition;
use qtism\data\rules\OutcomeIf;
use qtism\data\rules\OutcomeRuleCollection;
use qtism\data\rules\SetOutcomeValue;
use qtismtest\QtiSmTestCase;
use qtism\data\rules\OutcomeElse;
use qtism\data\rules\OutcomeElseIf;
use qtism\data\expressions\operators\Equal;

/**
 * Class OutcomeConditionMarshallerTest
 */
class OutcomeConditionMarshallerTest extends QtiSmTestCase
{
    public function testMarshallIfMinimal(): void
    {
        $setOutcomeValue = new SetOutcomeValue('myStringVar', new BaseValue(BaseType::STRING, 'Tested!'));
        $outcomeIf = new OutcomeIf(new BaseValue(BaseType::BOOLEAN, true), new OutcomeRuleCollection([$setOutcomeValue]));

        $component = new OutcomeCondition($outcomeIf);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertEquals('outcomeCondition', $element->nodeName);
        $this::assertSame($element, $element->getElementsByTagName('outcomeIf')->item(0)->parentNode);
        $this::assertSame($element->getElementsByTagName('outcomeIf')->item(0), $element->getElementsByTagName('baseValue')->item(0)->parentNode);
        $this::assertEquals('string', $element->getElementsByTagName('baseValue')->item(1)->getAttribute('baseType'));
        $this::assertSame($element->getElementsByTagName('outcomeIf')->item(0), $element->getElementsByTagName('setOutcomeValue')->item(0)->parentNode);
    }

    public function testUnmarshallConditionMinimal(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<outcomeCondition xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1">
				<outcomeIf>
					<baseValue baseType="boolean">true</baseValue>
					<setOutcomeValue identifier="myStringVar">
						<baseValue baseType="string">Tested!</baseValue>
					</setOutcomeValue>
				</outcomeIf>
			</outcomeCondition>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(OutcomeCondition::class, $component);
        $this::assertInstanceOf(OutcomeIf::class, $component->getOutcomeIf());
        $this::assertInstanceOf(BaseValue::class, $component->getOutcomeIf()->getExpression());
        $this::assertTrue($component->getOutcomeIf()->getExpression()->getValue());
        $this::assertEquals(BaseType::BOOLEAN, $component->getOutcomeIf()->getExpression()->getBaseType());

        $outcomeRules = $component->getOutcomeIf()->getOutcomeRules();
        $this::assertInstanceOf(SetOutcomeValue::class, $outcomeRules[0]);
        $this::assertEquals('myStringVar', $outcomeRules[0]->getIdentifier());
    }

    public function testUnmarshallConditionElseIf(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<outcomeCondition xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1">
				<outcomeIf>
					<baseValue baseType="boolean">true</baseValue>
					<setOutcomeValue identifier="myStringVar">
						<baseValue baseType="string">If!</baseValue>
					</setOutcomeValue>
				</outcomeIf>
				<outcomeElseIf>
					<baseValue baseType="boolean">false</baseValue>
					<setOutcomeValue identifier="myStringVar">
						<baseValue baseType="string">ElseIf!</baseValue>
					</setOutcomeValue>
				</outcomeElseIf>
			</outcomeCondition>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(OutcomeCondition::class, $component);
        $this::assertInstanceOf(OutcomeIf::class, $component->getOutcomeIf());
        $this::assertInstanceOf(BaseValue::class, $component->getOutcomeIf()->getExpression());

        $this::assertTrue($component->getOutcomeIf()->getExpression()->getValue());
        $this::assertEquals(BaseType::BOOLEAN, $component->getOutcomeIf()->getExpression()->getBaseType());

        $outcomeRules = $component->getOutcomeIf()->getOutcomeRules();
        $this::assertInstanceOf(SetOutcomeValue::class, $outcomeRules[0]);
        $this::assertEquals('myStringVar', $outcomeRules[0]->getIdentifier());
        $this::assertEquals('If!', $outcomeRules[0]->getExpression()->getValue());

        $outcomeElseIfs = $component->getOutcomeElseIfs();
        $this::assertInstanceOf(OutcomeElseIf::class, $outcomeElseIfs[0]);
        $this::assertInstanceOf(BaseValue::class, $outcomeElseIfs[0]->getExpression());

        $outcomeRules = $outcomeElseIfs[0]->getOutcomeRules();
        $this::assertInstanceOf(SetOutcomeValue::class, $outcomeRules[0]);
        $this::assertEquals('ElseIf!', $outcomeRules[0]->getExpression()->getValue());
    }

    public function testUnmarshallConditionElseIfElse(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<outcomeCondition xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1">
				<outcomeIf>
					<baseValue baseType="boolean">true</baseValue>
					<setOutcomeValue identifier="myStringVar">
						<baseValue baseType="string">If!</baseValue>
					</setOutcomeValue>
				</outcomeIf>
				<outcomeElseIf>
					<baseValue baseType="boolean">false</baseValue>
					<setOutcomeValue identifier="myStringVar">
						<baseValue baseType="string">ElseIf!</baseValue>
					</setOutcomeValue>
				</outcomeElseIf>
				<outcomeElse>
					<setOutcomeValue identifier="myStringVar">
						<baseValue baseType="string">Else!</baseValue>
					</setOutcomeValue>
				</outcomeElse>
			</outcomeCondition>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(OutcomeCondition::class, $component);
        $this::assertInstanceOf(OutcomeIf::class, $component->getOutcomeIf());
        $this::assertInstanceOf(BaseValue::class, $component->getOutcomeIf()->getExpression());

        $this::assertTrue($component->getOutcomeIf()->getExpression()->getValue());
        $this::assertEquals(BaseType::BOOLEAN, $component->getOutcomeIf()->getExpression()->getBaseType());

        $outcomeRules = $component->getOutcomeIf()->getOutcomeRules();
        $this::assertInstanceOf(SetOutcomeValue::class, $outcomeRules[0]);
        $this::assertEquals('myStringVar', $outcomeRules[0]->getIdentifier());
        $this::assertEquals('If!', $outcomeRules[0]->getExpression()->getValue());

        $outcomeElseIfs = $component->getOutcomeElseIfs();
        $this::assertInstanceOf(OutcomeElseIf::class, $outcomeElseIfs[0]);
        $this::assertInstanceOf(BaseValue::class, $outcomeElseIfs[0]->getExpression());

        $outcomeRules = $outcomeElseIfs[0]->getOutcomeRules();
        $this::assertInstanceOf(SetOutcomeValue::class, $outcomeRules[0]);
        $this::assertEquals('ElseIf!', $outcomeRules[0]->getExpression()->getValue());

        $this::assertInstanceOf(OutcomeElse::class, $component->getOutcomeElse());

        $outcomeRules = $component->getOutcomeElse()->getOutcomeRules();
        $this::assertInstanceOf(SetOutcomeValue::class, $outcomeRules[0]);
        $this::assertEquals('Else!', $outcomeRules[0]->getExpression()->getValue());
    }

    public function testUnmarshallConditionMultipleElseIf(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<outcomeCondition xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1">
				<outcomeIf>
					<baseValue baseType="boolean">true</baseValue>
					<setOutcomeValue identifier="myStringVar">
						<baseValue baseType="string">If!</baseValue>
					</setOutcomeValue>
				</outcomeIf>
				<outcomeElseIf>
					<baseValue baseType="boolean">false</baseValue>
					<setOutcomeValue identifier="myStringVar">
						<baseValue baseType="string">ElseIf1!</baseValue>
					</setOutcomeValue>
				</outcomeElseIf>
				<outcomeElseIf>
					<baseValue baseType="boolean">false</baseValue>
					<setOutcomeValue identifier="myStringVar">
						<baseValue baseType="string">ElseIf2!</baseValue>
					</setOutcomeValue>
				</outcomeElseIf>
				<outcomeElse>
					<setOutcomeValue identifier="myStringVar">
						<baseValue baseType="string">Else!</baseValue>
					</setOutcomeValue>
				</outcomeElse>
			</outcomeCondition>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(OutcomeCondition::class, $component);
        $this::assertInstanceOf(OutcomeIf::class, $component->getOutcomeIf());
        $this::assertInstanceOf(BaseValue::class, $component->getOutcomeIf()->getExpression());

        $this::assertTrue($component->getOutcomeIf()->getExpression()->getValue());
        $this::assertEquals(BaseType::BOOLEAN, $component->getOutcomeIf()->getExpression()->getBaseType());

        $outcomeRules = $component->getOutcomeIf()->getOutcomeRules();
        $this::assertInstanceOf(SetOutcomeValue::class, $outcomeRules[0]);
        $this::assertEquals('myStringVar', $outcomeRules[0]->getIdentifier());
        $this::assertEquals('If!', $outcomeRules[0]->getExpression()->getValue());

        $outcomeElseIfs = $component->getOutcomeElseIfs();
        $this::assertInstanceOf(OutcomeElseIf::class, $outcomeElseIfs[0]);
        $this::assertInstanceOf(BaseValue::class, $outcomeElseIfs[0]->getExpression());

        $outcomeRules = $outcomeElseIfs[0]->getOutcomeRules();
        $this::assertInstanceOf(SetOutcomeValue::class, $outcomeRules[0]);
        $this::assertEquals('ElseIf1!', $outcomeRules[0]->getExpression()->getValue());

        $this::assertInstanceOf(OutcomeElseIf::class, $outcomeElseIfs[1]);
        $this::assertInstanceOf(BaseValue::class, $outcomeElseIfs[1]->getExpression());

        $outcomeRules = $outcomeElseIfs[1]->getOutcomeRules();
        $this::assertInstanceOf(SetOutcomeValue::class, $outcomeRules[0]);
        $this::assertEquals('ElseIf2!', $outcomeRules[0]->getExpression()->getValue());

        $this::assertInstanceOf(OutcomeElse::class, $component->getOutcomeElse());

        $outcomeRules = $component->getOutcomeElse()->getOutcomeRules();
        $this::assertInstanceOf(SetOutcomeValue::class, $outcomeRules[0]);
        $this::assertEquals('Else!', $outcomeRules[0]->getExpression()->getValue());
    }

    public function testUnmarshallConditionUltimate(): void
    {
        // Special thanks to Younes Djaghloul ! We love you Younz :D !
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<outcomeCondition xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1">
				<outcomeIf>
					<baseValue baseType="boolean">true</baseValue>
					<outcomeCondition>
						<outcomeIf>
							<equal>
								<baseValue baseType="integer">4</baseValue>
								<sum>
									<baseValue baseType="integer">2</baseValue>
									<baseValue baseType="integer">2</baseValue>
								</sum>
							</equal>
							<setOutcomeValue identifier="mySum">
								<baseValue baseType="string">2 + 2 = 4</baseValue>
							</setOutcomeValue>
						</outcomeIf>
						<outcomeElseIf>
							<equal>
								<baseValue baseType="integer">6</baseValue>
								<sum>
									<baseValue baseType="integer">3</baseValue>
									<baseValue baseType="integer">3</baseValue>
								</sum>
							</equal>
							<setOutcomeValue identifier="mySum">
								<baseValue baseType="string">3 + 3 = 6</baseValue>
							</setOutcomeValue>
						</outcomeElseIf>
						<outcomeElse>
							<setOutcomeValue identifier="mySum">
								<baseValue baseType="string">4 + 4 = 8</baseValue>
							</setOutcomeValue>
						</outcomeElse>
					</outcomeCondition>
				</outcomeIf>
				<outcomeElseIf>
					<baseValue baseType="boolean">false</baseValue>
					<setOutcomeValue identifier="myStringVar">
						<baseValue baseType="string">ElseIf1!</baseValue>
					</setOutcomeValue>
				</outcomeElseIf>
				<outcomeElseIf>
					<baseValue baseType="boolean">false</baseValue>
					<setOutcomeValue identifier="myStringVar">
						<baseValue baseType="string">ElseIf2!</baseValue>
					</setOutcomeValue>
				</outcomeElseIf>
				<outcomeElse>
					<setOutcomeValue identifier="myStringVar">
						<baseValue baseType="string">Else!</baseValue>
					</setOutcomeValue>
				</outcomeElse>
			</outcomeCondition>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(OutcomeCondition::class, $component);
        $this::assertInstanceOf(OutcomeIf::class, $component->getOutcomeIf());
        $this::assertInstanceOf(BaseValue::class, $component->getOutcomeIf()->getExpression());
        $this::assertTrue($component->getOutcomeIf()->getExpression()->getValue());

        $outcomeRules = $component->getOutcomeIf()->getOutcomeRules();
        $this::assertInstanceOf(OutcomeCondition::class, $outcomeRules[0]);
        $this::assertInstanceOf(Equal::class, $outcomeRules[0]->getOutcomeIf()->getExpression());
        $this::assertCount(1, $outcomeRules[0]->getOutcomeElseIfs());

        $outcomeElseIfs = $outcomeRules[0]->getOutcomeElseIfs();
        $subOutcomeRules = $outcomeElseIfs[0]->getOutcomeRules();
        $this::assertEquals('3 + 3 = 6', $subOutcomeRules[0]->getExpression()->getValue());

        $outcomeElseIfs = $component->getOutcomeElseIfs();
        $this::assertInstanceOf(OutcomeElseIf::class, $outcomeElseIfs[0]);
        $this::assertInstanceOf(BaseValue::class, $outcomeElseIfs[0]->getExpression());

        $outcomeRules = $outcomeElseIfs[0]->getOutcomeRules();
        $this::assertInstanceOf(SetOutcomeValue::class, $outcomeRules[0]);
        $this::assertEquals('ElseIf1!', $outcomeRules[0]->getExpression()->getValue());

        $outcomeElseIfs = $component->getOutcomeElseIfs();
        $this::assertInstanceOf(OutcomeElseIf::class, $outcomeElseIfs[1]);
        $this::assertInstanceOf(BaseValue::class, $outcomeElseIfs[1]->getExpression());

        $outcomeRules = $outcomeElseIfs[1]->getOutcomeRules();
        $this::assertInstanceOf(SetOutcomeValue::class, $outcomeRules[0]);
        $this::assertEquals('ElseIf2!', $outcomeRules[0]->getExpression()->getValue());

        $outcomeRules = $component->getOutcomeElse()->getOutcomeRules();
        $this::assertInstanceOf(OutcomeElse::class, $component->getOutcomeElse());
        $this::assertInstanceOf(SetOutcomeValue::class, $outcomeRules[0]);
        $this::assertEquals('Else!', $outcomeRules[0]->getExpression()->getValue());
    }
}
