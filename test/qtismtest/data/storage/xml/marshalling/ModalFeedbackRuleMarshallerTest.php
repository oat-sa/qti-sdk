<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\ModalFeedbackRule;
use qtism\data\ShowHide;
use qtism\data\storage\xml\marshalling\Compact21MarshallerFactory;
use qtismtest\QtiSmTestCase;

/**
 * Class ModalFeedbackRuleMarshallerTest
 *
 * @package qtismtest\data\storage\xml\marshalling
 */
class ModalFeedbackRuleMarshallerTest extends QtiSmTestCase
{
    public function testUnmarshallNoTitle()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<modalFeedbackRule outcomeIdentifier="SHOW_HIM" identifier="SHOW_MEH" showHide="show"/>');
        $element = $dom->documentElement;
        $factory = new Compact21MarshallerFactory();
        $mf = $factory->createMarshaller($element)->unmarshall($element);

        $this->assertInstanceOf(ModalFeedbackRule::class, $mf);
        $this->assertEquals('SHOW_MEH', $mf->getIdentifier());
        $this->assertEquals('SHOW_HIM', $mf->getOutcomeIdentifier());
        $this->assertEquals(ShowHide::SHOW, $mf->getShowHide());
        $this->assertFalse($mf->hasTitle());
        $this->assertSame('', $mf->getTitle());
    }

    public function testMarshallNoTitle()
    {
        $mf = new ModalFeedbackRule('SHOW_HIM', ShowHide::SHOW, 'SHOW_MEH');
        $factory = new Compact21MarshallerFactory();
        $marshaller = $factory->createMarshaller($mf);
        $elt = $marshaller->marshall($mf);

        $this->assertEquals('modalFeedbackRule', $elt->localName);
        $this->assertEquals('SHOW_HIM', $elt->getAttribute('outcomeIdentifier'));
        $this->assertEquals('SHOW_MEH', $elt->getAttribute('identifier'));
        $this->assertEquals('show', $elt->getAttribute('showHide'));
        $this->assertEquals('', $elt->getAttribute('title'));
    }

    /**
     * @depends testUnmarshallNoTitle
     */
    public function testUnmarshallTitle()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<modalFeedbackRule outcomeIdentifier="SHOW_HIM" identifier="SHOW_MEH" showHide="show" href="./MF01.xml" title="Beautiful Feedback!"/>');
        $element = $dom->documentElement;
        $factory = new Compact21MarshallerFactory();
        $mf = $factory->createMarshaller($element)->unmarshall($element);

        $this->assertTrue($mf->hasTitle());
        $this->assertSame('Beautiful Feedback!', $mf->getTitle());
    }

    /**
     * @depends testMarshallNoTitle
     */
    public function testMarshallTitle()
    {
        $mf = new ModalFeedbackRule('SHOW_HIM', ShowHide::SHOW, 'SHOW_MEH', 'Beautiful Feedback!');
        $factory = new Compact21MarshallerFactory();
        $marshaller = $factory->createMarshaller($mf);
        $elt = $marshaller->marshall($mf);

        $this->assertEquals('Beautiful Feedback!', $elt->getAttribute('title'));
    }
}
