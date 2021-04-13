<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\AssessmentItem;
use qtism\data\state\OutcomeDeclaration;
use qtism\data\state\OutcomeDeclarationCollection;
use qtism\data\state\ResponseDeclaration;
use qtism\data\state\ResponseDeclarationCollection;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

/**
 * Class AssessmentItemMarshallerTest
 */
class AssessmentItemMarshallerTest extends QtiSmTestCase
{
    public function testMarshallMinimal()
    {
        $identifier = 'Q01';
        $timeDependent = false;
        $title = 'Question 1';
        $label = 'Label of Question 1';
        $toolName = 'QTISM';
        $toolVersion = '0.6.0';

        $assessmentItem = new AssessmentItem($identifier, $title, $timeDependent);
        $assessmentItem->setLabel($label);
        $assessmentItem->setToolName($toolName);
        $assessmentItem->setToolVersion($toolVersion);

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($assessmentItem);
        $element = $marshaller->marshall($assessmentItem);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('assessmentItem', $element->nodeName);

        // adaptive, timeDependent, identifier, title, label, toolName, toolVersion
        $this::assertEquals(7, $element->attributes->length);
        $this::assertEquals($identifier, $element->getAttribute('identifier'));
        $this::assertEquals($title, $element->getAttribute('title'));
        $this::assertEquals('false', $element->getAttribute('timeDependent'));
        $this::assertEquals('false', $element->getAttribute('adaptive'));
        $this::assertEquals($label, $element->getAttribute('label'));
        $this::assertEquals($toolName, $element->getAttribute('toolName'));
        $this::assertEquals($toolVersion, $element->getAttribute('toolVersion'));
    }

    public function testUnmarshallMinimal()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<assessmentItem xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="Q01" title="Test Item" timeDependent="false" label="My Label" toolName="My Tool" toolVersion="0.6.0"/>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(AssessmentItem::class, $component);
        $this::assertEquals('Q01', $component->getIdentifier());
        $this::assertEquals('Test Item', $component->getTitle());
        $this::assertFalse($component->isTimeDependent());
        $this::assertFalse($component->isAdaptive());
        $this::assertFalse($component->hasLang());
        $this::assertTrue($component->hasLabel());
        $this::assertEquals('My Label', $component->getLabel());
        $this::assertTrue($component->hasToolName());
        $this::assertEquals('My Tool', $component->getToolName());
        $this::assertTrue($component->hasToolVersion());
        $this::assertEquals('0.6.0', $component->getToolVersion());
    }

    public function testMarshallMaximal()
    {
        $identifier = 'Q01';
        $title = 'Test Item';
        $timeDependent = true;
        $adaptive = true;
        $lang = 'en-YO'; // Yoda English ;)

        $responseDeclarations = new ResponseDeclarationCollection();
        $responseDeclarations[] = new ResponseDeclaration('resp1', BaseType::INTEGER, Cardinality::SINGLE);
        $responseDeclarations[] = new ResponseDeclaration('resp2', BaseType::FLOAT, Cardinality::SINGLE);

        $outcomeDeclarations = new OutcomeDeclarationCollection();
        $outcomeDeclarations[] = new OutcomeDeclaration('out1', BaseType::BOOLEAN, Cardinality::MULTIPLE);
        $outcomeDeclarations[] = new OutcomeDeclaration('out2', BaseType::IDENTIFIER, Cardinality::SINGLE);

        $item = new AssessmentItem($identifier, $title, $timeDependent, $lang);
        $item->setAdaptive($adaptive);
        $item->setResponseDeclarations($responseDeclarations);
        $item->setOutcomeDeclarations($outcomeDeclarations);

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($item);
        $element = $marshaller->marshall($item);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('assessmentItem', $element->nodeName);

        // adaptive, timeDependent, identifier, lang, title
        $this::assertEquals(5, $element->attributes->length);
        $this::assertEquals($identifier, $element->getAttribute('identifier'));
        $this::assertEquals($title, $element->getAttribute('title'));
        $this::assertEquals('true', $element->getAttribute('timeDependent'));
        $this::assertEquals('true', $element->getAttribute('adaptive'));
        $this::assertEquals($lang, $element->getAttribute('lang'));

        $responseDeclarationElts = $element->getElementsByTagName('responseDeclaration');
        $this::assertEquals(2, $responseDeclarationElts->length);
        $this::assertEquals('resp1', $responseDeclarationElts->item(0)->getAttribute('identifier'));
        $this::assertEquals('resp2', $responseDeclarationElts->item(1)->getAttribute('identifier'));

        $outcomeDeclarationElts = $element->getElementsByTagName('outcomeDeclaration');
        $this::assertEquals(2, $outcomeDeclarationElts->length);
        $this::assertEquals('out1', $outcomeDeclarationElts->item(0)->getAttribute('identifier'));
        $this::assertEquals('out2', $outcomeDeclarationElts->item(1)->getAttribute('identifier'));
    }

    public function testUnmarshallMaximal()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<assessmentItem xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="Q01" timeDependent="false" adaptive="false" lang="en-YO" title="test item">
				<responseDeclaration identifier="resp1" baseType="integer" cardinality="single"/>
				<responseDeclaration identifier="resp2" baseType="float" cardinality="single"/>
				<outcomeDeclaration identifier="out1" baseType="boolean" cardinality="multiple"/>
				<outcomeDeclaration identifier="out2" baseType="identifier" cardinality="single"/>
			</assessmentItem>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(AssessmentItem::class, $component);
        $this::assertEquals('Q01', $component->getIdentifier());
        $this::assertEquals('test item', $component->getTitle());
        $this::assertFalse($component->isTimeDependent());
        $this::assertFalse($component->isAdaptive());
        $this::assertTrue($component->hasLang());
        $this::assertEquals('en-YO', $component->getLang());

        $responseDeclarations = $component->getResponseDeclarations();
        $this::assertCount(2, $responseDeclarations);

        $outcomeDeclarations = $component->getOutcomeDeclarations();
        $this::assertCount(2, $outcomeDeclarations);
    }

    /**
     * @depends testUnmarshallMinimal
     */
    public function testUnmarshallDecorate()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<assessmentItem xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="Q01" title="Test Item" timeDependent="false" label="My Label" toolName="My Tool" toolVersion="0.6.0"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $decorated = $marshaller->unmarshall($element);

        $dom->loadXML('<assessmentItem xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="Q02" title="Test Item" timeDependent="true" label="My Label" toolName="My Tool" toolVersion="0.6.0"/>');
        $element = $dom->documentElement;

        $decorated = $marshaller->unmarshall($element, $decorated);
        $this::assertEquals('Q02', $decorated->getIdentifier());
        $this::assertTrue($decorated->isTimeDependent());
    }

    /**
     * @testUnmarshallMinimal
     */
    public function testUnmarshallNoTitle()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<assessmentItem xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="Q01" timeDependent="false" label="My Label" toolName="My Tool" toolVersion="0.6.0"/>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory attribute 'title' is missing from element 'assessmentItem'.");

        $marshaller->unmarshall($element);
    }

    /**
     * @testUnmarshallMinimal
     */
    public function testUnmarshallNoTimeDependent()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<assessmentItem xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="Q01" title="title" label="My Label" toolName="My Tool" toolVersion="0.6.0"/>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory attribute 'timeDependent' is missing from element 'assessmentItem'.");

        $marshaller->unmarshall($element);
    }

    /**
     * @testUnmarshallMinimal
     */
    public function testUnmarshallNoIdentifier()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<assessmentItem xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" timeDependent="false" title="title" label="My Label" toolName="My Tool" toolVersion="0.6.0"/>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory attribute 'identifier' is missing from element 'assessmentItem'.");

        $marshaller->unmarshall($element);
    }
}
