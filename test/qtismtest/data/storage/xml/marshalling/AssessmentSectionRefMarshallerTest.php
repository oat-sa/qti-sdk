<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\AssessmentSectionRef;
use qtismtest\QtiSmTestCase;

class AssessmentSectionRefMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $identifier = 'mySectionRef';
        $href = 'http://www.rdfabout.com';

        $component = new AssessmentSectionRef($identifier, $href);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf(\DOMElement::class, $element);
        $this->assertEquals('assessmentSectionRef', $element->nodeName);
        $this->assertEquals($identifier, $element->getAttribute('identifier'));
        $this->assertEquals($href, $element->getAttribute('href'));
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<assessmentSectionRef xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="mySectionRef" href="http://www.rdfabout.com"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(AssessmentSectionRef::class, $component);
        $this->assertEquals($component->getIdentifier(), 'mySectionRef');
        $this->assertEquals($component->getHref(), 'http://www.rdfabout.com');
    }
}
