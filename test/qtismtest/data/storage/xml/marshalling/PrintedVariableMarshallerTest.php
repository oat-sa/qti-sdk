<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\data\content\enums\AriaOrientation;
use qtism\data\content\PrintedVariable;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\data\storage\xml\marshalling\MarshallingException;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

/**
 * Class PrintedVariableMarshallerTest
 */
class PrintedVariableMarshallerTest extends QtiSmTestCase
{
    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshall21(): void
    {
        $component = new PrintedVariable('PRID');
        $component->setIndex(0);
        $component->setField('field');
        $component->setXmlBase('/home/jerome');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('printedVariable', $element->nodeName);
        $this::assertEquals('PRID', $element->getAttribute('identifier'));
        $this::assertEquals('0', $element->getAttribute('index'));
        $this::assertEquals('field', $element->getAttribute('field'));
        $this::assertEquals('/home/jerome', $element->getAttribute('xml:base'));
    }

    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshall22(): void
    {
        $component = new PrintedVariable('PRID');
        $component->setAriaOrientation(AriaOrientation::VERTICAL);

        $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        // aria-* must be ignored for printedVariables.
        $this::assertFalse($element->hasAttribute('aria-orientation'));
    }

    public function testUnmarshall21(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<printedVariable xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="PRID" index="0" field="field" xml:base="/home/jerome"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(PrintedVariable::class, $component);
        $this::assertEquals('PRID', $component->getIdentifier());
        $this::assertEquals(0, $component->getIndex());
        $this::assertEquals('field', $component->getField());
        $this::assertEquals('/home/jerome', $component->getXmlBase());
    }

    /**
     * @throws MarshallerNotFoundException
     */
    public function testUnmarshall22(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<printedVariable xmlns="http://www.imsglobal.org/xsd/imsqti_v2p2" identifier="PRID" aria-orientation="horizontal"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($element);
        /** @var PrintedVariable $component */
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(PrintedVariable::class, $component);
        $this::assertEquals('PRID', $component->getIdentifier());
        $this::assertFalse($component->hasAriaOrientation());
    }

    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshallNoIdentifier(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<printedVariable xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" index="0" field="field" xml:base="/home/jerome"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'identifier' attribute is missing from the 'printedVariable' element");
        $component = $marshaller->unmarshall($element);
    }
}
