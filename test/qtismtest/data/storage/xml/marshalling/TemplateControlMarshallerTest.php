<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\rules\SetTemplateValue;
use qtism\data\rules\TemplateElse;
use qtism\data\rules\TemplateElseIf;
use qtism\data\rules\TemplateIf;
use qtism\data\rules\TemplateRuleCollection;
use qtismtest\QtiSmTestCase;

/**
 * Class TemplateControlMarshallerTest
 */
class TemplateControlMarshallerTest extends QtiSmTestCase
{
    public function testMarshallTemplateIfSimple(): void
    {
        $true = new BaseValue(BaseType::BOOLEAN, true);
        $setTemplateValue = new SetTemplateValue('tpl1', new BaseValue(BaseType::INTEGER, 1337));
        $templateIf = new TemplateIf($true, new TemplateRuleCollection([$setTemplateValue]));

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($templateIf)->marshall($templateIf);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<templateIf><baseValue baseType="boolean">true</baseValue><setTemplateValue identifier="tpl1"><baseValue baseType="integer">1337</baseValue></setTemplateValue></templateIf>', $dom->saveXML($element));
    }

    public function testUnmarshallTemplateIfSimple(): void
    {
        $element = $this->createDOMElement('
	        <templateIf>
	            <baseValue baseType="boolean">true</baseValue>
	            <setTemplateValue identifier="tpl1">
	                <baseValue baseType="integer">1337</baseValue>
	            </setTemplateValue>
	        </templateIf>
	    ');

        $templateIf = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this::assertInstanceOf(TemplateIf::class, $templateIf);
        $this::assertInstanceOf(BaseValue::class, $templateIf->getExpression());
        $templateRules = $templateIf->getTemplateRules();
        $this::assertCount(1, $templateRules);
        $this::assertInstanceOf(SetTemplateValue::class, $templateRules[0]);
        $this::assertInstanceOf(BaseValue::class, $templateRules[0]->getExpression());
    }

    public function testMarshallTemplateIfMultipleRules(): void
    {
        $true = new BaseValue(BaseType::BOOLEAN, true);
        $setTemplateValue1 = new SetTemplateValue('tpl1', new BaseValue(BaseType::INTEGER, 1337));
        $setTemplateValue2 = new SetTemplateValue('tpl2', new BaseValue(BaseType::INTEGER, 1338));
        $templateIf = new TemplateIf($true, new TemplateRuleCollection([$setTemplateValue1, $setTemplateValue2]));

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($templateIf)->marshall($templateIf);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals(
            '<templateIf><baseValue baseType="boolean">true</baseValue><setTemplateValue identifier="tpl1"><baseValue baseType="integer">1337</baseValue></setTemplateValue><setTemplateValue identifier="tpl2"><baseValue baseType="integer">1338</baseValue></setTemplateValue></templateIf>',
            $dom->saveXML($element)
        );
    }

    public function testUnmarshallTemplateIfMultipleRules(): void
    {
        $element = $this->createDOMElement('
	        <templateIf>
	            <baseValue baseType="boolean">true</baseValue>
	            <setTemplateValue identifier="tpl1">
	                <baseValue baseType="integer">1337</baseValue>
	            </setTemplateValue>
	            <setTemplateValue identifier="tpl2">
	                <baseValue baseType="integer">1338</baseValue>
	            </setTemplateValue>
	        </templateIf>
	    ');

        $templateIf = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this::assertInstanceOf(TemplateIf::class, $templateIf);
        $this::assertInstanceOf(BaseValue::class, $templateIf->getExpression());

        $templateRules = $templateIf->getTemplateRules();
        $this::assertCount(2, $templateRules);

        $this::assertInstanceOf(SetTemplateValue::class, $templateRules[0]);
        $this::assertEquals('tpl1', $templateRules[0]->getIdentifier());
        $this::assertInstanceOf(BaseValue::class, $templateRules[0]->getExpression());
        $this::assertEquals(1337, $templateRules[0]->getExpression()->getValue());

        $this::assertInstanceOf(SetTemplateValue::class, $templateRules[1]);
        $this::assertEquals('tpl2', $templateRules[1]->getIdentifier());
        $this::assertInstanceOf(BaseValue::class, $templateRules[1]->getExpression());
        $this::assertEquals(1338, $templateRules[1]->getExpression()->getValue());
    }

    public function testMarshallTemplateElseIfSimple(): void
    {
        $true = new BaseValue(BaseType::BOOLEAN, true);
        $setTemplateValue = new SetTemplateValue('tpl1', new BaseValue(BaseType::INTEGER, 1337));
        $templateElseIf = new TemplateElseIf($true, new TemplateRuleCollection([$setTemplateValue]));

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($templateElseIf)->marshall($templateElseIf);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<templateElseIf><baseValue baseType="boolean">true</baseValue><setTemplateValue identifier="tpl1"><baseValue baseType="integer">1337</baseValue></setTemplateValue></templateElseIf>', $dom->saveXML($element));
    }

    public function testUnmarshallTemplateElseIfSimple(): void
    {
        $element = $this->createDOMElement('
	        <templateElseIf>
	            <baseValue baseType="boolean">true</baseValue>
	            <setTemplateValue identifier="tpl1">
	                <baseValue baseType="integer">1337</baseValue>
	            </setTemplateValue>
	        </templateElseIf>
	    ');

        $templateElseIf = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this::assertInstanceOf(TemplateElseIf::class, $templateElseIf);
        $this::assertInstanceOf(BaseValue::class, $templateElseIf->getExpression());
        $templateRules = $templateElseIf->getTemplateRules();
        $this::assertCount(1, $templateRules);
        $this::assertInstanceOf(SetTemplateValue::class, $templateRules[0]);
        $this::assertInstanceOf(BaseValue::class, $templateRules[0]->getExpression());
    }

    public function testMarshallTemplateElseSimple(): void
    {
        $setTemplateValue = new SetTemplateValue('tpl1', new BaseValue(BaseType::INTEGER, 1337));
        $templateIf = new TemplateElse(new TemplateRuleCollection([$setTemplateValue]));

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($templateIf)->marshall($templateIf);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<templateElse><setTemplateValue identifier="tpl1"><baseValue baseType="integer">1337</baseValue></setTemplateValue></templateElse>', $dom->saveXML($element));
    }

    public function testUnmarshallTemplateElseSimple(): void
    {
        $element = $this->createDOMElement('
	        <templateElse>
	            <setTemplateValue identifier="tpl1">
	                <baseValue baseType="integer">1337</baseValue>
	            </setTemplateValue>
	        </templateElse>
	    ');

        $templateElse = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this::assertInstanceOf(TemplateElse::class, $templateElse);
        $templateRules = $templateElse->getTemplateRules();
        $this::assertCount(1, $templateRules);
        $this::assertInstanceOf(SetTemplateValue::class, $templateRules[0]);
        $this::assertInstanceOf(BaseValue::class, $templateRules[0]->getExpression());
    }

    public function testMarshallTemplateElseMultipleRules(): void
    {
        $setTemplateValue1 = new SetTemplateValue('tpl1', new BaseValue(BaseType::INTEGER, 1337));
        $setTemplateValue2 = new SetTemplateValue('tpl2', new BaseValue(BaseType::INTEGER, 1338));
        $templateElse = new TemplateElse(new TemplateRuleCollection([$setTemplateValue1, $setTemplateValue2]));

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($templateElse)->marshall($templateElse);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<templateElse><setTemplateValue identifier="tpl1"><baseValue baseType="integer">1337</baseValue></setTemplateValue><setTemplateValue identifier="tpl2"><baseValue baseType="integer">1338</baseValue></setTemplateValue></templateElse>', $dom->saveXML($element));
    }
}
