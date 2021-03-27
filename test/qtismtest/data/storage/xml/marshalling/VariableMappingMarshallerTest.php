<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\data\state\VariableMapping;
use qtismtest\QtiSmTestCase;

/**
 * Class VariableMappingMarshallerTest
 */
class VariableMappingMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $source = 'myIdentifier1';
        $target = 'myIdentifier2';

        $component = new VariableMapping($source, $target);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('variableMapping', $element->nodeName);
        $this::assertEquals($source, $element->getAttribute('sourceIdentifier'));
        $this::assertEquals($target, $element->getAttribute('targetIdentifier'));
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<variableMapping xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" sourceIdentifier="myIdentifier1" targetIdentifier="myIdentifier2"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(VariableMapping::class, $component);
        $this::assertEquals('myIdentifier1', $component->getSource());
        $this::assertEquals('myIdentifier2', $component->getTarget());
    }
}
