<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\RubricBlockRef;
use qtism\data\storage\xml\marshalling\Compact21MarshallerFactory;
use qtismtest\QtiSmTestCase;

/**
 * Class RubricBlockRefMarshallerTest
 */
class RubricBlockRefMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $component = new RubricBlockRef('R01', './R01.xml');
        $marshaller = (new Compact21MarshallerFactory('2.1.0'))->createMarshaller($component);
        $elt = $marshaller->marshall($component);

        $this::assertEquals('rubricBlockRef', $elt->nodeName);
        $this::assertEquals('R01', $elt->getAttribute('identifier'));
        $this::assertEquals('./R01.xml', $elt->getAttribute('href'));
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<rubricBlockRef identifier="R01" href="./R01.xml"/>');
        $element = $dom->documentElement;

        $marshaller = (new Compact21MarshallerFactory('2.1.0'))->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertEquals('R01', $component->getIdentifier());
        $this::assertEquals('./R01.xml', $component->getHref());
    }
}
