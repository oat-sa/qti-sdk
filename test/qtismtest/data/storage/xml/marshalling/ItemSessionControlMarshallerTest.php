<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\data\ItemSessionControl;
use qtismtest\QtiSmTestCase;

/**
 * Class ItemSessionControlMarshallerTest
 */
class ItemSessionControlMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $component = new ItemSessionControl();
        $component->setAllowComment(true);
        $component->setMaxAttempts(2);
        $component->setValidateResponses(false);

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);
        $this->assertEquals('itemSessionControl', $element->nodeName);
        $this->assertEquals('true', $element->getAttribute('allowComment'));
        $this->assertEquals('2', $element->getAttribute('maxAttempts'));
        $this->assertEquals('false', $element->getAttribute('validateResponses'));
        $this->assertEquals('true', $element->getAttribute('allowReview'));
        $this->assertEquals('false', $element->getAttribute('showSolution'));
        $this->assertEquals('true', $element->getAttribute('allowSkipping'));
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<itemSessionControl xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" validateResponses="true" showFeedback="false" allowReview="true" showSolution="true" allowComment="true" allowSkipping="false"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(ItemSessionControl::class, $component);
        $this->assertTrue($component->mustValidateResponses());
        $this->assertFalse($component->mustShowFeedback());
        $this->assertTrue($component->doesAllowReview());
        $this->assertTrue($component->mustShowSolution());
        $this->assertTrue($component->doesAllowComment());
        $this->assertFalse($component->doesAllowSkipping());
    }
}
