<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\processing\OutcomeProcessing;
use qtism\data\rules\LookupOutcomeValue;
use qtism\data\rules\OutcomeRuleCollection;
use qtism\data\rules\SetOutcomeValue;
use qtismtest\QtiSmTestCase;

/**
 * Class OutcomeProcessingMarshallerTest
 */
class OutcomeProcessingMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $outcomeRules = new OutcomeRuleCollection();
        $outcomeRules[] = new LookupOutcomeValue('output1', new BaseValue(BaseType::FLOAT, 24.3));
        $outcomeRules[] = new SetOutcomeValue('output2', new BaseValue(BaseType::BOOLEAN, true));

        $component = new OutcomeProcessing($outcomeRules);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);

        $this->assertTrue($element->getElementsByTagName('lookupOutcomeValue')->item(0)->parentNode === $element);
        $this->assertTrue($element->getElementsByTagName('baseValue')->item(0)->parentNode === $element->getElementsByTagName('lookupOutcomeValue')->item(0));
        $this->assertEquals('24.3', $element->getElementsByTagName('baseValue')->item(0)->nodeValue);

        $this->assertTrue($element->getElementsByTagName('setOutcomeValue')->item(0)->parentNode === $element);
        $this->assertTrue($element->getElementsByTagName('baseValue')->item(1)->parentNode === $element->getElementsByTagName('setOutcomeValue')->item(0));
        $this->assertEquals('true', $element->getElementsByTagName('baseValue')->item(1)->nodeValue);
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<outcomeProcessing xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1">
				<lookupOutcomeValue identifier="output1">
					<baseValue baseType="float">24.3</baseValue>
				</lookupOutcomeValue>
				<setOutcomeValue identifier="output2">
					<baseValue baseType="boolean">true</baseValue>
				</setOutcomeValue>
			</outcomeProcessing>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(OutcomeProcessing::class, $component);

        $outcomeRules = $component->getOutcomeRules();
        $this->assertInstanceOf(LookupOutcomeValue::class, $outcomeRules[0]);
        $this->assertEquals('output1', $outcomeRules[0]->getIdentifier());
        $this->assertInstanceOf(SetOutcomeValue::class, $outcomeRules[1]);
        $this->assertEquals('output2', $outcomeRules[1]->getIdentifier());

        $this->assertInstanceOf(BaseValue::class, $outcomeRules[0]->getExpression());
        $this->assertIsFloat($outcomeRules[0]->getExpression()->getValue());
        $this->assertEquals(24.3, $outcomeRules[0]->getExpression()->getValue());

        $this->assertInstanceOf(BaseValue::class, $outcomeRules[1]->getExpression());
        $this->assertIsBool($outcomeRules[1]->getExpression()->getValue());
        $this->assertTrue($outcomeRules[1]->getExpression()->getValue());
    }
}
