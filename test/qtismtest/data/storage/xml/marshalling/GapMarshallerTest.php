<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\interactions\Gap;
use qtism\data\ShowHide;
use qtismtest\QtiSmTestCase;

/**
 * Class GapMarshallerTest
 */
class GapMarshallerTest extends QtiSmTestCase
{
    public function testMarshall21()
    {
        $gap = new Gap('gap1', true, 'my-gap', 'gaps');
        $gap->setFixed(false);
        $gap->setTemplateIdentifier('tpl-gap');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($gap);
        $element = $marshaller->marshall($gap);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals(
            '<gap identifier="gap1" templateIdentifier="tpl-gap" required="true" id="my-gap" class="gaps"/>',
            $dom->saveXML($element)
        );
    }

    public function testUnmarshall21()
    {
        $element = $this->createDOMElement('
	        <gap identifier="gap1" templateIdentifier="tpl-gap" required="true" id="my-gap" class="gaps" showHide="hide"/>
	    ');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $gap = $marshaller->unmarshall($element);

        $this->assertInstanceOf(Gap::class, $gap);
        $this->assertEquals('gap1', $gap->getIdentifier());
        $this->assertEquals('tpl-gap', $gap->getTemplateIdentifier());
        $this->assertTrue($gap->hasTemplateIdentifier());
        $this->assertTrue($gap->isRequired());
        $this->assertEquals('gaps', $gap->getClass());
        $this->assertEquals(ShowHide::HIDE, $gap->getShowHide());
    }
}
