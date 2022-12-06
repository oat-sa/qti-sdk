<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\rules\OutcomeElse;
use qtism\data\rules\OutcomeElseIf;
use qtism\data\rules\OutcomeIf;
use qtism\data\rules\OutcomeRuleCollection;
use qtism\data\rules\SetOutcomeValue;
use qtismtest\QtiSmTestCase;

/**
 * Class OutcomeControlMarshallerTest
 */
class OutcomeControlMarshallerTest extends QtiSmTestCase
{
    public function testMarshallIfMinimal(): void
    {
        $setOutcomeValue = new SetOutcomeValue('myStringVar', new BaseValue(BaseType::STRING, 'Tested!'));
        $baseValue = new BaseValue(BaseType::BOOLEAN, true);

        $component = new OutcomeIf($baseValue, new OutcomeRuleCollection([$setOutcomeValue]));

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('outcomeIf', $element->nodeName);
        $this::assertEquals(2, $element->getElementsByTagName('baseValue')->length);

        $expression = $element->getElementsByTagName('baseValue')->item(0);
        $this::assertSame($element, $expression->parentNode);
        $this::assertEquals('boolean', $expression->getAttribute('baseType'));
        $this::assertEquals('true', $expression->nodeValue);

        $setOutcomeValue = $element->getElementsByTagName('setOutcomeValue')->item(0);
        $this::assertEquals('myStringVar', $setOutcomeValue->getAttribute('identifier'));

        $tested = $element->getElementsByTagName('baseValue')->item(1);
        $this::assertSame($setOutcomeValue, $tested->parentNode);
        $this::assertEquals('Tested!', $tested->nodeValue);
        $this::assertEquals('string', $tested->getAttribute('baseType'));
    }

    public function testMarshallElseIfMinimal(): void
    {
        $setOutcomeValue = new SetOutcomeValue('myStringVar', new BaseValue(BaseType::STRING, 'Tested!'));
        $baseValue = new BaseValue(BaseType::BOOLEAN, true);

        $component = new OutcomeElseIf($baseValue, new OutcomeRuleCollection([$setOutcomeValue]));

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('outcomeElseIf', $element->nodeName);
        $this::assertEquals(2, $element->getElementsByTagName('baseValue')->length);

        $expression = $element->getElementsByTagName('baseValue')->item(0);
        $this::assertSame($element, $expression->parentNode);
        $this::assertEquals('boolean', $expression->getAttribute('baseType'));
        $this::assertEquals('true', $expression->nodeValue);

        $setOutcomeValue = $element->getElementsByTagName('setOutcomeValue')->item(0);
        $this::assertEquals('myStringVar', $setOutcomeValue->getAttribute('identifier'));

        $tested = $element->getElementsByTagName('baseValue')->item(1);
        $this::assertSame($setOutcomeValue, $tested->parentNode);
        $this::assertEquals('Tested!', $tested->nodeValue);
        $this::assertEquals('string', $tested->getAttribute('baseType'));
    }

    public function testMarshallElseMinimal(): void
    {
        $setOutcomeValue = new SetOutcomeValue('myStringVar', new BaseValue(BaseType::STRING, 'Tested!'));
        $component = new OutcomeElse(new OutcomeRuleCollection([$setOutcomeValue]));

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('outcomeElse', $element->nodeName);
        $this::assertEquals(1, $element->getElementsByTagName('baseValue')->length);

        $setOutcomeValue = $element->getElementsByTagName('setOutcomeValue')->item(0);
        $this::assertEquals('myStringVar', $setOutcomeValue->getAttribute('identifier'));

        $tested = $element->getElementsByTagName('baseValue')->item(0);
        $this::assertSame($setOutcomeValue, $tested->parentNode);
        $this::assertEquals('string', $tested->getAttribute('baseType'));
        $this::assertEquals('Tested!', $tested->nodeValue);
    }

    public function testUnmarshallIfMinimal(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<outcomeIf xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1">
				<baseValue baseType="boolean">true</baseValue>
				<setOutcomeValue identifier="myStringVar">
					<baseValue baseType="string">Tested!</baseValue>
				</setOutcomeValue>
			</outcomeIf>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(OutcomeIf::class, $component);
        $this::assertCount(1, $component->getOutcomeRules());
        $this::assertInstanceOf(BaseValue::class, $component->getExpression());

        $outcomeRules = $component->getOutcomeRules();
        $this::assertInstanceOf(SetOutcomeValue::class, $outcomeRules[0]);
        $this::assertInstanceOf(BaseValue::class, $outcomeRules[0]->getExpression());
        $this::assertIsString($outcomeRules[0]->getExpression()->getValue());
        $this::assertEquals('Tested!', $outcomeRules[0]->getExpression()->getValue());
        $this::assertEquals(BaseType::STRING, $outcomeRules[0]->getExpression()->getBaseType());
    }

    public function testUnmarshallElseIfMinimal(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<outcomeElseIf xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1">
				<baseValue baseType="boolean">true</baseValue>
				<setOutcomeValue identifier="myStringVar">
					<baseValue baseType="string">Tested!</baseValue>
				</setOutcomeValue>
			</outcomeElseIf>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(OutcomeElseIf::class, $component);
        $this::assertCount(1, $component->getOutcomeRules());
        $this::assertInstanceOf(BaseValue::class, $component->getExpression());

        $outcomeRules = $component->getOutcomeRules();
        $this::assertInstanceOf(SetOutcomeValue::class, $outcomeRules[0]);
        $this::assertInstanceOf(BaseValue::class, $outcomeRules[0]->getExpression());
        $this::assertIsString($outcomeRules[0]->getExpression()->getValue());
        $this::assertEquals('Tested!', $outcomeRules[0]->getExpression()->getValue());
        $this::assertEquals(BaseType::STRING, $outcomeRules[0]->getExpression()->getBaseType());
    }

    public function testUnmarshallElseMinimal(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<outcomeElse xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1">
				<setOutcomeValue identifier="myStringVar">
					<baseValue baseType="string">Tested!</baseValue>
				</setOutcomeValue>
			</outcomeElse>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(OutcomeElse::class, $component);
        $this::assertCount(1, $component->getOutcomeRules());

        $outcomeRules = $component->getOutcomeRules();
        $this::assertInstanceOf(SetOutcomeValue::class, $outcomeRules[0]);
        $this::assertInstanceOf(BaseValue::class, $outcomeRules[0]->getExpression());
        $this::assertIsString($outcomeRules[0]->getExpression()->getValue());
        $this::assertEquals('Tested!', $outcomeRules[0]->getExpression()->getValue());
        $this::assertEquals(BaseType::STRING, $outcomeRules[0]->getExpression()->getBaseType());
    }
}
