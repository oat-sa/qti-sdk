<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\RubricBlockRef;
use qtism\data\storage\xml\marshalling\CompactMarshallerFactory;
use qtismtest\QtiSmTestCase;

class RubricBlockRefMarshallerTest extends QtiSmTestCase
{
    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<rubricBlockRef identifier="R01" href="./R01.xml"/>');
        $element = $dom->documentElement;
        $factory = new CompactMarshallerFactory();
        $ref = $factory->createMarshaller($element)->unmarshall($element);

        $this->assertEquals('R01', $ref->getIdentifier());
        $this->assertEquals('./R01.xml', $ref->getHref());
    }

    public function testMarshall()
    {
        $ref = new RubricBlockRef('R01', './R01.xml');
        $factory = new CompactMarshallerFactory();
        $marshaller = $factory->createMarshaller($ref);
        $elt = $marshaller->marshall($ref);

        $this->assertEquals('rubricBlockRef', $elt->nodeName);
        $this->assertEquals('R01', $elt->getAttribute('identifier'));
        $this->assertEquals('./R01.xml', $elt->getAttribute('href'));
    }
}
