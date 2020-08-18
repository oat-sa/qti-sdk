<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\rules\ExitTemplate;
use qtismtest\QtiSmTestCase;

class ExitTemplateMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $exitTemplate = new ExitTemplate();
        $element = $this->getMarshallerFactory()->createMarshaller($exitTemplate)->marshall($exitTemplate);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<exitTemplate/>', $dom->saveXML($element));
    }

    public function testUnmarshall()
    {
        $element = $this->createDOMElement('<exitTemplate/>');

        $exitTemplate = $this->getMarshallerFactory()->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf(ExitTemplate::class, $exitTemplate);
    }
}
